<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "admin".
 *
 * @property int $id
 * @property string $name
 * @property string $password
 * @property string $token
 */
class Admin extends \yii\db\ActiveRecord implements IdentityInterface
{
    public function init(): void
    {
        parent::init();
        \Yii::$app->user->enableSession = false;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'admin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'password'], 'required', 'message' => '«{attribute}» обязательно к заполнению'],
            [['name', 'password'], 'string', 'message' => 'Поле «{attribute}» должно быть строкой'],
            ['name', 'exist', 'message' => 'Пользователя с таким именем не существует'],
            [['name', 'id', 'token', 'password'], 'safe'],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'name',
            'token',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Имя пользователя',
            'password' => 'Секретное слово',
            'token' => 'Секретный ключ'
        ];
    }

    public function validatePassword(string $password): bool
    {
        $validation = Yii::$app->security->validatePassword($password, $this->password);

        if (!$validation) {
            $this->addError('password', 'Неверный пароль');
        }

        return $validation;
    }

    /**
     * {@inheritdoc}
     * @return AdminQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AdminQuery(get_called_class());
    }

    public static function findIdentity($id)
    {
        // TODO: Implement findIdentity() method.
    }

    public static function findIdentityByAccessToken($token, $type = null): Admin|IdentityInterface|null
    {
        return static::findOne(['token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }
}
