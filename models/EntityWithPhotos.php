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
    public string $className;

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Получение названия класса
        $this->className = lcfirst(
            basename(
                get_class($this)
            ));
    }

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
     */
    public function linkPhoto(Photo $photo): void
    {
        $photo->link($this->className, $this);
    }

    /**
     * Открепление фотографий от модели и удаление из файловой системы
     * @return void
     */
    public function unlinkPhotos(): void
    {
        foreach ($this->photos as $photo) {
            $filename = \Yii::getAlias('@webroot') . '/img' . DIRECTORY_SEPARATOR . $photo->image;
            $photo->unlink($this->className, $this);
            $photo->delete();
            unlink($filename);
        }
    }
}