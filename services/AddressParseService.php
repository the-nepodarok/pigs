<?php

namespace app\services;

use app\models\Clinic;
use app\models\FeedbackStatus;
use app\models\Vet;
use app\services\GeocoderService;
use Yii;
use yii\db\Exception;

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

    public function __construct()
    {
        $this->geocoderService = new GeocoderService();
    }

    /**
     * @param string $filename
     * @return void
     * @throws Exception
     * @throws \yii\httpclient\Exception
     */
    public function parse(string $filename): void
    {
        $lastClinic = null;

        $file = fopen(Yii::$app->basePath . '/web/' . $filename, "r");

        while (!feof($file)) {
            $line = fgets($file);

            $matches = [];
            $feedbackStatus = null;
            $currentStatus = $this->checkEntityFeedbackRating($line);

            if ($currentStatus) {
                $feedbackStatus = FeedbackStatus::findOne(['value' => $currentStatus]);
            }

            // Поиск клиники
            preg_match("/(?<address>[\w\W]+(?<![^.][\W])\d{1,3}\w?\/?\d?),?\s?(?<title>(?<=клиника)?[\W\w\s][^(]+)\s?(?<info>\([\w\s]+\))?/u", $line, $matches);

            if (empty($matches) || str_contains($matches['address'], '+7')) {

                // поиск врача
                $newString = trim(str_replace('•', '', $line));
                preg_match_all("/(?<!\s\w\s)(?<!\.\s)(?<!\()(?<name>[А-ЯA-Z][а-яa-z]+\b)\s?(?=\()?/u", $newString, $matches);

                if (empty($matches) || empty($matches['name'])) {
                    continue;
                }

                $this->handleVet($matches, $lastClinic, $feedbackStatus);

            } else {
                $lastClinic = $this->handleClinic($matches, $feedbackStatus);
            }

            echo $line;
        }

        fclose($file);
    }

    /**
     * @param string $line
     * @return string|null
     */
    protected function checkEntityFeedbackRating(string $line): string|null
    {
        $currentStatus = null;
        foreach ($this->statuses as $status => $value) {
            if ($currentStatus) {
                break;
            }
            $currentStatus = mb_strstr($line, $status) ? $value : null;
        }
        return $currentStatus;
    }

    /**
     * @param array $data
     * @param Clinic|null $lastClinic
     * @param FeedbackStatus|null $feedbackStatus
     * @return void
     * @throws Exception
     */
    protected function handleVet(array $data, Clinic $lastClinic = null, FeedbackStatus $feedbackStatus = null): void
    {
        $name = implode(' ', $data['name']);
        $existingVet = Vet::findOne(['name' => $name]);

        if (!$existingVet) {
            $this->createNewVet($name, $lastClinic, $feedbackStatus);
        } else if ($feedbackStatus && $existingVet->feedback_status_id !== $feedbackStatus->id) {
            $existingVet->feedback_status_id = $feedbackStatus->id;
            $existingVet->save();
        }
    }

    /**
     * @param array $data
     * @param FeedbackStatus|null $feedbackStatus
     * @return Clinic|null
     * @throws Exception
     * @throws \yii\httpclient\Exception
     */
    protected function handleClinic(array $data, FeedbackStatus $feedbackStatus = null): Clinic|null
    {
        $trimmedAddress = trim(str_replace(array_merge([$this->metro], array_keys($this->statuses)), '', $data['address']));
        $trimmedAddress = str_replace('строение', 'стр.', $trimmedAddress);
        $fullAddress = $this->buildFullAddress($trimmedAddress, $data);
        $clinic = Clinic::findOne(['address' => $fullAddress]);

        if (!$clinic) {
            $results = $this->getAddressCords(str_replace('м.', '', $trimmedAddress));

            if (empty($results) || !isset($results['features']['0'])) {
                Yii::error('Не найдена клиника по адресу - ' . $data['address']);
                return null;
            }

            $clinic = $this->createNewClinic($results['features']['0']['properties'], $fullAddress, $feedbackStatus);
        } else if ($feedbackStatus && $clinic->feedback_status_id !== $feedbackStatus->id) {
            $clinic->feedback_status_id = $feedbackStatus->id;
            $clinic->save();
        }

        return $clinic;
    }

    /**
     * @param string $address
     * @return array
     * @throws \yii\httpclient\Exception
     */
    protected function getAddressCords(string $address): array
    {
        return $this->geocoderService->searchByString($address);
    }

    /**
     * @param string $baseAddress
     * @param array $matches
     * @return string
     */
    protected function buildFullAddress(string $baseAddress, array $matches): string
    {
        return $baseAddress . ($matches['title'] ? (' - ' . $matches['title']) : '') . (isset($matches['info']) ? (' ' . $matches['info']) : '');
    }

    /**
     * @param array $geodata
     * @param string $address
     * @param FeedbackStatus|null $feedbackStatus
     * @return Clinic
     * @throws Exception
     */
    protected function createNewClinic(array $geodata, string $address, FeedbackStatus $feedbackStatus = null): Clinic
    {
        $newClinic = new Clinic();
        $newClinic->address = $address;
        $newClinic->longitude = strval($geodata['lon']);
        $newClinic->latitude = strval($geodata['lat']);

        if ($feedbackStatus) {
            $newClinic->feedback_status_id = $feedbackStatus->id;
        }

        $newClinic->save();
        return $newClinic;
    }

    /**
     * @param string $name
     * @param Clinic|null $lastClinic
     * @param FeedbackStatus|null $feedbackStatus
     * @return Vet
     * @throws Exception
     */
    protected function createNewVet(string $name, Clinic $lastClinic = null, FeedbackStatus $feedbackStatus = null): Vet
    {
        $newVet = new Vet();
        $newVet->name = $name;

        if ($feedbackStatus) {
            $newVet->feedback_status_id = $feedbackStatus->id;
        }

        if ($lastClinic) {
            $newVet->clinic_id = $lastClinic->id;
        }

        $newVet->save();
        return $newVet;
    }
}