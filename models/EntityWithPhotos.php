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
    public function unlinkPhotos(array $photos): void
    {
        foreach ($photos as $photo) {
            $filename = \Yii::getAlias('@webroot') . '/img' . DIRECTORY_SEPARATOR . $photo . '.jpg';
            $photo = Photo::find()->where(['image' => $photo])->one();
            $photo->unlink('pig', $this);
            $photo->delete();
            unlink($filename);
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
}