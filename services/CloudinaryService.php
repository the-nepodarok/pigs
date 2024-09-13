<?php

namespace app\services;

use Cloudinary\Api\Exception\ApiError;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Exception;
use Yii;

class CloudinaryService
{
    public Configuration $cloudConfig;
    public UploadApi $cloudUploader;

    public function __construct()
    {
        $this->cloudConfig = new Configuration(env('CLOUDINARY_URL'));
        $this->cloudUploader = new UploadApi();
    }

    /**
     * @param string $filepath
     * @return string|void|null
     * @throws Exception
     */
    public function store(string $filepath)
    {
        try {
            return $this->upload($filepath);
        } catch (ApiError $error) {
            Yii::error($error->getMessage());
        }
    }

    /**
     * @param string $filepath
     * @return string|null
     * @throws ApiError
     */
    public function upload(string $filepath): string|null
    {
        $response = $this->cloudUploader->upload($filepath, []);

        $headers = $response->headers;

        if ($headers['Status'][0] === '200 OK') {
            $data = $response->getArrayCopy();
            return $data['public_id'];
        }
    }

    /**
     * @param string $publicId
     * @return void
     */
    public function delete(string $publicId): void
    {
        $this->cloudUploader->destroy($publicId);
    }
}