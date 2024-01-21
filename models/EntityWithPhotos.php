<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property string|null $main_photo;
 * @property array|null $files;
 */
class EntityWithPhotos extends ActiveRecord
{
    public ?string $main_photo = null;
    public ?array $files = null;

    public function rules(): array
    {
        return [
            [['files'], 'image',
                'maxFiles' => 0,
                'skipOnEmpty' => true,
                'extensions' => 'jpg, jpeg',
                'wrongExtension' => 'Неверный формат файла. Принимаются только картинки с расширением JPG',
                'wrongMimeType' => 'Неверный формат файла. Принимаются только картинки с расширением JPG',
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
     * @param array $files
     * @return void
     * @throws \Exception
     */
    public function linkPhotos(array $files): void
    {
        // Получение названия класса
        $className = lcfirst(
            basename(
                get_class($this)
            ));

        foreach ($files as $file) {
            $photo = new Photo();

            try {
                $photo->upload($file);
            } catch (\Exception $exception) {
                error_log($exception->getMessage());
            }

            $photo->link($className, $this);
        }
    }

    /**
     * Открепление фотографий от модели и удаление из файловой системы
     * @return void
     */
    public function unlinkPhotos(): void
    {
        foreach ($this->photos as $photo) {
            $filename = \Yii::getAlias('@webroot') . '/img' . DIRECTORY_SEPARATOR . $photo->image . '.jpg';
            $photo->unlink('pig', $this);
            $photo->delete();
            unlink($filename);
        }
    }
}