<?php

namespace app\services;

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
    protected FileReaderService $fileReaderService;

    public function __construct()
    {
        $this->geocoderService = new GeocoderService();
        $this->fileReaderService = new FileReaderService();
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

        foreach ($this->fileReaderService->readLine($filename) as $line) {
            $matches = [];
            $feedbackStatusId = null;
            $currentStatus = $this->checkEntityFeedbackRating($line);

            if ($currentStatus) {
                $feedbackStatusId = $allStatuses[$currentStatus];
            }

            try {
                if (!str_contains($line, '+7')) {
                    // Поиск клиники
                    $trimmedAddress = trim(str_replace(array_merge([$this->metro], array_keys($this->statuses)), '', $line));
                    $trimmedAddress = str_replace('строение', 'стр.', $trimmedAddress);
                    $matches = $this->matchClinic($trimmedAddress);
                }

                if (empty($matches)) {
                    // поиск врача
                    $newString = trim(str_replace('•', '', $line));
                    $matches = $this->matchVet($newString);

                    if (empty($matches) || empty($matches['name'])) {
                        continue;
                    }

                    $this->handleVet($matches, $lastClinic, $feedbackStatusId);

                } else {
                    $lastClinic = $this->handleClinic($matches, $feedbackStatusId);
                }
            } catch (\Error|\Exception $e) {
                Yii::error($e->getMessage());
                echo 'Error: ' . $e->getMessage();
                continue;
            }

            echo $line;
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
     * @return array{name?: string}
     */
    private function matchVet(string $string): array
    {
        $matches = [];
        preg_match_all("/(?<!\s\w\s)(?<!\.\s)(?<!\()(?<name>[А-ЯA-Z][а-яa-z]+\b)\s?(?=\()?/u", $string, $matches);
        return $matches;
    }

    /**
     * @param string $string
     * @return array{address?: string, title?: string, info?:string}
     */
    private function matchClinic(string $string): array
    {
        $matches = [];
        preg_match("/(?<address>[\w\W]+(?<![^.][\W])\d{1,3}\w?\/?\d?),?\s?(?<title>(?<=клиника)?[\W\w\s][^(]+)\s?(?<info>\([\w\s]+\))?/u", $string, $matches);
        return $matches;
    }

    /**
     * @param array{name: string} $data
     * @param Clinic|null $lastClinic
     * @param int|null $feedbackStatusId
     * @return void
     * @throws Exception
     */
    private function handleVet(array $data, Clinic $lastClinic = null, int $feedbackStatusId = null): void
    {
        $name = implode(' ', $data['name']);
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
    private function handleClinic(array $data, int $feedbackStatusId = null): Clinic|null
    {
        $fullAddress = $this->buildFullAddress($data);
        $clinic = Clinic::findOne(['address' => $fullAddress]);

        if (!$clinic) {
            $results = $this->getAddressCoords(str_replace('м.', '', $data['address']));

            if (empty($results) || !isset($results['features']['0'])) {
                Yii::error('Не найдена клиника по адресу - ' . $data['address']);
                return null;
            }

            $clinic = $this->createNewClinic($results['features']['0']['properties'], $fullAddress, $feedbackStatusId);
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
    private function buildFullAddress(array $matches): string
    {
        return $matches['address'] . ($matches['title'] ? (' - ' . $matches['title']) : '') . (isset($matches['info']) ? (' ' . $matches['info']) : '');
    }

    /**
     * @param array{lon: double, lat: double} $geodata
     * @param string $address
     * @param int|null $feedbackStatusId
     * @return Clinic
     * @throws Exception
     */
    private function createNewClinic(array $geodata, string $address, int $feedbackStatusId = null): Clinic
    {
        $newClinic = new Clinic();
        $newClinic->address = $address;
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
    private function createNewVet(string $name, Clinic $lastClinic = null, int $feedbackStatusId = null): Vet
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