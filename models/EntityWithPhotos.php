<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * @property string|null $main_photo;
 * @property array|null $files;
 */
class EntityWithPhotos extends ActiveRecord
{
    public ?string $main_photo = null;
    public ?array $files = null;
    public string $className;

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Получение названия класса
        $reflection = new \ReflectionClass($this);
        $this->className = lcfirst($reflection->getShortName());
    }

    public function load($data, $formName = null): bool
    {
        $files = UploadedFile::getInstancesByName('files');

        if (!empty($files) && $files[0]->size) {
            $this->files = $files;
        }

        return parent::load($data, $formName);
    }

    public function beforeSave($insert): bool
    {
        if ($insert)
            $this->datetime = $this->datetime ?: date('Y-m-d H:i:s');
        else
            $this->datetime = $this->datetime;
        return parent::beforeSave($insert);
    }

    public function rules(): array
    {
        return [
            [['files'], 'image',
                'maxFiles' => 0,
                'maxSize' => 4e+6,
                'skipOnEmpty' => true,
                'extensions' => ['jpg', 'jpeg'],
                'wrongExtension' => 'Неверный формат файла. Принимаются только картинки с расширением JPG',
                'wrongMimeType' => 'Неверный формат файла. Принимаются только картинки с расширением JPG',
                'tooBig' => 'Файл слишком большой. Максимально допустимый размер: 4MB'
            ],
        ];
    }

    public function fields(): array
    {
        $fields = parent::fields();

        // Добавление поля с главной фотографией
        $fields[] = 'main_photo';

        $this->main_photo = $this->photos[0]['image'] ?? null;

        return $fields;
    }

    /**
     * Загрузка фотографий в файловую систему и прикрепление к модели
     * @param $photo Photo
     * @return void
     */
    public function linkPhoto(Photo $photo): void
    {
        $photo->link($this->className, $this);
    }

    /**
     * Открепление фотографии от модели и удаление из файловой системы
     * @param $photo Photo
     */
    public function unlinkPhoto (Photo $photo): void
    {
        $filename = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $photo->image;

        try {
            $photo->unlink($this->className, $this, true);
        } catch (\Exception $e) {
            error_log('Attempt to unlink file that does not exist');
        }

//        try {
//            $photo->delete();
//        } catch (\Throwable $e) {
//            error_log('Failed to delete record');
//        }

        unlink($filename);
    }

    public function unlinkAllPhotos(): void
    {
        foreach ($this->photos as $photo) {
            $this->unlinkPhoto($photo);
        }
    }

    /**
     * Сравнивает массив пришедших фотографий с теми, что уже были загружены, и возвращает различие
     * @param array $old_photos
     * @return array
     */
    public function comparePhotos (array $old_photos): array
    {
        // Находим имеющиеся фотографии
        $current_photos = $this->photos;
        $current_photos = ArrayHelper::getColumn($current_photos, 'image');

        // Сравниваем пришедшие имена фотографий с теми, что уже имеются
        return array_diff($current_photos, $old_photos);
    }


    public function changePhotoOrder($main_photo_index): void
    {
        $photos = $this->files;

        // changes order of marked as main file to be the first to add
        $main = ArrayHelper::remove($photos, $main_photo_index);
        array_unshift($photos, $main);
        $this->files = $photos;
    }

    public function handlePhotos(): void
    {
        if ($this->files) {
            foreach ($this->files as $file) {
                $photo = new Photo();

                try {
                    $photo->upload($file);
                    $this->linkPhoto($photo);
                } catch (\Exception $exception) {
                    $this->addError('files', $exception->getMessage());
                }

                $this->linkPhoto($photo);
            }
        }
    }
}