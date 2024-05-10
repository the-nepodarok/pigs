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

        unlink($filename);
    }

    /**
     * Открепляет все фотографии модели
     * @return void
     */

    public function unlinkAllPhotos(): void
    {
        foreach ($this->photos as $photo) {
            $this->unlinkPhoto($photo);
        }
    }

    /**
     * Сравнивает массив пришедших фотографий с теми, что уже были загружены, и возвращает различие
     * @param array $oldPhotos
     * @return array
     */
    public function comparePhotos (array $oldPhotos): array
    {
        // Находим имеющиеся фотографии
        $currentPhoto = $this->photos;
        $currentPhoto = ArrayHelper::getColumn($currentPhoto, 'image');

        // Сравниваем пришедшие имена фотографий с теми, что уже имеются
        return array_diff($currentPhoto, $oldPhotos);
    }

    /**
     * Меняет порядок загружаемых файлов
     * @param $mainPhotoIndex
     * @return void
     */
    public function changePhotoOrder($mainPhotoIndex): void
    {
        $photos = $this->files;

        // changes order of marked as main file to be the first to add
        $main = ArrayHelper::remove($photos, $mainPhotoIndex);
        array_unshift($photos, $main);
        $this->files = $photos;
    }

    /**
     * Загружает новые фотографии
     * @return void
     */
    public function handleNewPhotos(): void
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

    /**
     * Перепревязывает старые фотографии к модели
     * @param array $oldPhotos
     * @return void
     */
    public function relinkOldPhotos(array $oldPhotos): void
    {
        foreach ($oldPhotos as $photo) {
            $newPhoto = new Photo();
            $newPhoto->image = $photo;
            $this->linkPhoto($newPhoto);
        }
    }

    /**
     * Изменяет порядок загруженных и\или сохранённый
     * фотографий для того, чтобы сделать отмеченную пользователем первой - т.е. главной
     * @param $mainPhotoName - название фотографии, если главной должна быть старая фотография
     * @param $mainPhotoIndex - индекс фотографии, если главной выбрана одна из новых
     * @return void
     */
    public function rearrangePhotos($mainPhotoName, $mainPhotoIndex): void
    {
        // получаем список текущих фотографий
        $currentPhotos = $this->photos;

        foreach ($currentPhotos as $currentPhoto) {
            // отвязываем их
            try {
                $this->unlink('photos', $currentPhoto, true);
            } catch (\Exception $e) {
                error_log('Attempt to unlink file that does not exist');
            }
        }

        // если нужно выбрать из новых фотографий
        if (isset($mainPhotoIndex)) {

            // если была отмечена первая, менять порядок не нужно
            if (intval($mainPhotoIndex) !== 0) {

                // меняем порядок файлов, чтобы главная фотография была первой
                $this->changePhotoOrder($mainPhotoIndex);
            }

            // загружаем новые фотографии
            $this->handleNewPhotos();
        }

        // получаем массив из имён фотографий, что уже были в системе
        $currentPhotos = ArrayHelper::getColumn($currentPhotos, 'image');

        // если главной фотографией должна быть одна из старых
        if ($mainPhotoName) {

            // находим её индекс в массиве
            $index = array_search($mainPhotoName, $currentPhotos);

            // переносим на первое место
            $mainPhoto = ArrayHelper::remove($currentPhotos, $index);
            array_unshift($currentPhotos, $mainPhoto);
        }

        // возвращаем в бд старые фотографии
        $this->relinkOldPhotos($currentPhotos);

        // если помимо главной из старых были переданые новые фото,
        // загрузить их
        if (isset($mainPhoto) && $this->files) {
            $this->handleNewPhotos();
        }
    }
}