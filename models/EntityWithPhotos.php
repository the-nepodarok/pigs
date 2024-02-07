<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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


    public function afterFind()
    {
        parent::afterFind();

        // Устанавливает отображение даты по заданному часовому поясу
        $time = strtotime($this->datetime.' UTC');
        $this->datetime = date("Y-m-d H:i:s", $time);
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
    public function unlinkPhoto (string $photo): void
    {
        $filename = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $photo . '.jpg';
        $photo = Photo::find()->where(['image' => $photo])->one();
        $photo->unlink($this->className, $this);
        $photo->delete();
        unlink($filename);
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
}