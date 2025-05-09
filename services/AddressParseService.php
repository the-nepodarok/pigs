<?php

namespace app\services;

use app\helpers\FileReaderHelper;
use app\models\Clinic;
use app\models\FeedbackStatus;
use app\models\Vet;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class AddressParseService
{
    protected array $statuses = [
        '🟢' => 'good',
        '🟡' => 'insufficient',
        '⚪' => 'none',
        '🟠' => 'mostly_bad',
        '🔴' => 'bad',
    ];

    protected string $metro = '🚇';

    protected GeocoderService $geocoderService;
    protected FileReaderHelper $fileReaderService;

    public function __construct()
    {
        $this->geocoderService = new GeocoderService();
        $this->fileReaderService = new FileReaderHelper();
    }

    /**
     * @param string $filename
     * @return void
     */
    public function parse(string $filename): void
    {
        $lastClinic = null;
        $feedbackStatuses = FeedbackStatus::find()->all();
        $allStatuses = ArrayHelper::map($feedbackStatuses, 'value', 'id');

        foreach ($this->fileReaderService->getNextLine($filename) as $line) {
            echo $line;
            $matches = [];
            $feedbackStatusId = null;
            $currentStatus = $this->checkEntityFeedbackRating($line);

            if ($currentStatus) {
                $feedbackStatusId = $allStatuses[$currentStatus];
            }

            try {
                if (!str_contains($line, '+7')) {
                    // Поиск клиники
                    $trimmedAddress = trim(str_replace(array_merge([$this->metro, 'г.'], array_keys($this->statuses)), '', $line));
                    $trimmedAddress = str_replace('строение', 'стр.', $trimmedAddress);
                    $trimmedAddress = str_replace('корпус', 'к.', $trimmedAddress);
                    $matches = $this->matchClinic($trimmedAddress);

                    if (!empty($matches)) {
                        $lastClinic = $this->handleClinic($matches, $feedbackStatusId);
                        continue;
                    }
                }

                if (empty($matches)) {
                    // поиск врача
                    $newString = trim(str_replace('•', '', $line));
                    $name = $this->matchVet($newString);

                    if (!$name) {
                        continue;
                    }

                    $this->handleVet($name, $lastClinic, $feedbackStatusId);
                }
            } catch (\Error|\Exception $e) {
                Yii::error($e->getMessage());
                echo 'Error: ' . $e->getMessage();
                continue;
            }
        }
    }

    /**
     * @param string $line
     * @return string|null
     */
    private function checkEntityFeedbackRating(string $line): string|null
    {
        $currentStatus = null;
        foreach ($this->statuses as $status => $value) {
            $currentStatus = mb_strstr($line, $status) ? $value : null;
            if ($currentStatus) {
                break;
            }
        }
        return $currentStatus;
    }

    /**
     * @param string $string
     * @return string|null
     */
    private function matchVet(string $string): string|null
    {
        $matches = [];
        preg_match_all("/(?<!\s\w\s)(?<!\.\s)(?<!\()(?<name>[А-ЯA-Z][а-яa-z]+\b)\s?(?=\()?/u", $string, $matches);
        return isset($matches['name']) ? implode(' ', $matches['name']) : null;
    }

    /**
     * @param string $string
     * @return array{address?: string, title?: string, info?:string}
     */
    private function matchClinic(string $string): array
    {
        $clinicData = [];
        $matches = [];

        preg_match("/(?<address>[г.]?[🚇\-\w\s,.]+(?<![^.][\W])\d{1,3}\w?[\/-]?\d?\w?),?\s?(?<title>(?<=клиника|«)?[\W\w\s][^(]+)\s?(?<info>\([\w.,\s]+\))?/u", $string, $matches);

        if (isset($matches['address'])) {
            $clinicData['address'] = $matches['address'];
        }

        if (isset($matches['title'])) {
            $clinicData['title'] = $matches['title'];
        }

        if (isset($matches['info'])) {
            $clinicData['info'] = $matches['info'];
        }

        return $clinicData;
    }

    /**
     * @param string $name
     * @param Clinic|null $lastClinic
     * @param int|null $feedbackStatusId
     * @return void
     * @throws Exception
     */
    private function handleVet(string $name, ?Clinic $lastClinic = null, ?int $feedbackStatusId = null): void
    {
        $existingVet = Vet::findOne(['name' => $name]);

        if (!$existingVet) {
            $this->createNewVet($name, $lastClinic, $feedbackStatusId);
        } else if ($feedbackStatusId && $existingVet->feedback_status_id !== $feedbackStatusId) {
            $existingVet->feedback_status_id = $feedbackStatusId;
            $existingVet->save();
        }
    }

    /**
     * @param array{address: string, title?: string, info?: string} $data
     * @param int|null $feedbackStatusId
     * @return Clinic|null
     * @throws Exception
     * @throws \yii\httpclient\Exception
     */
    private function handleClinic(array $data, ?int $feedbackStatusId = null): Clinic|null
    {
        $title = $this->buildFullTitle($data);
        $title = preg_replace('/\s+/', ' ', $title);
        $query = Clinic::find()->where(['address' => $data['address']]);

        if ($title) {
            $query->andWhere(['title' => $title]);
        }

        $clinic = $query->one();

        if (!$clinic) {
            $searchAddress = preg_replace('/,\s{0,5}м.\s?\w+\s?\w+?,/u', '', $data['address']);
            $searchAddress = preg_replace('/к. \d+/u', '', $searchAddress);
            $searchAddress = preg_replace('/стр. \d+/u', '', $searchAddress);
            $searchAddress = str_replace(['д.', 'б-р', 'корп.', 'пер.'], '', $searchAddress);
            $searchAddress = preg_replace('/\s+/u', ' ', $searchAddress);
            $results = $this->getAddressCoords($searchAddress);

            if (empty($results) || !isset($results['features']['0'])) {
                Yii::error('Не найдена клиника по адресу - ' . $data['address']);
                return null;
            }

            $clinic = $this->createNewClinic($results['features']['0']['properties'], $data['address'], $title, $feedbackStatusId);
        } else if ($feedbackStatusId && $clinic->feedback_status_id !== $feedbackStatusId) {
            $clinic->feedback_status_id = $feedbackStatusId;
            $clinic->save();
        }

        return $clinic;
    }

    /**
     * @param string $address
     * @return array{features: array{properties?: array{lat: double, lon: double}}, query: array{text: string}}
     * @throws \yii\httpclient\Exception
     */
    private function getAddressCoords(string $address): array
    {
        return $this->geocoderService->searchByString($address);
    }

    /**
     * @param array{address: string, title?: string, info?: string} $matches
     * @return string
     */
    private function buildFullTitle(array $matches): string
    {
        return (isset($matches['title']) ? ($matches['title']) : '') . (isset($matches['info']) ? (' ' . $matches['info']) : '');
    }

    /**
     * @param array{lon: double, lat: double} $geodata
     * @param string $address
     * @param string|null $title
     * @param int|null $feedbackStatusId
     * @return Clinic
     * @throws Exception
     */
    private function createNewClinic(array $geodata, string $address, ?string $title = '', ?int $feedbackStatusId = null): Clinic
    {
        $newClinic = new Clinic();
        $newClinic->address = $address;
        $newClinic->title = $title;
        $newClinic->longitude = strval($geodata['lon']);
        $newClinic->latitude = strval($geodata['lat']);

        if ($feedbackStatusId) {
            $newClinic->feedback_status_id = $feedbackStatusId;
        }

        $newClinic->save();
        return $newClinic;
    }

    /**
     * @param string $name
     * @param Clinic|null $lastClinic
     * @param int|null $feedbackStatusId
     * @return Vet
     * @throws Exception
     */
    private function createNewVet(string $name, Clinic $lastClinic = null, ?int $feedbackStatusId = null): Vet
    {
        $newVet = new Vet();
        $newVet->name = $name;

        if ($feedbackStatusId) {
            $newVet->feedback_status_id = $feedbackStatusId;
        }

        if ($lastClinic) {
            $newVet->clinic_id = $lastClinic->id;
        }

        $newVet->save();
        return $newVet;
    }
}