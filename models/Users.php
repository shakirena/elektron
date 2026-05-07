<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property integer $id_user
 * @property string $telephone
 * @property string $fio
 * @property string $login
 * @property string $password
 * @property integer $id_role
 * @property double $salary
 *
 * @property Arrivalproduct[] $arrivalproducts
 * @property Debt[] $debts
 * @property Finance[] $finances
 * @property Roles $idRole
 */
class Users extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fio', 'login', 'id_role','id_store'], 'required'],
            [['id_role','id_store'], 'integer'],
            [['salary'], 'number'],
            [['telephone', 'login'], 'string', 'max' => 50],
            [[ 'password'],'string','min'=>5 ],
            [['fio'], 'string', 'max' => 255],
            [['id_role'], 'exist', 'skipOnError' => true, 'targetClass' => Roles::className(), 'targetAttribute' => ['id_role' => 'id_role']],
            [['id_store'], 'exist', 'skipOnError' => true, 'targetClass' => Store::className(), 'targetAttribute' => ['id_store' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_user' => 'Id User',
            'telephone' => 'Telefon',
            'fio' => 'SAA',
            'login' => 'Login',
            'password' => 'Şifrə',
            'id_role' => 'Vəzifə',
            'salary' => 'Salary',
            'id_store'=>'Filial'
        ];
    }

    public static function findByUsername($username)
    {
        return static::findOne([
            'login' => $username
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArrivalproducts()
    {
        return $this->hasMany(Arrival::className(), ['id_user' => 'id_user']);
    }
    public function getIdStore()
    {
        return $this->hasOne(Store::className(), ['id' => 'id_store']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDebts()
    {
        return $this->hasMany(Debt::className(), ['id_user' => 'id_user']);
    }
    public function setPassword($password) {
        $this->password=Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFinances()
    {
        return $this->hasMany(Finance::className(), ['id_user' => 'id_user']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdRole()
    {
        return $this->hasOne(Roles::className(), ['id_role' => 'id_role']);
    }

    /* Хелперы */
    /**
     * Сравнивает полученный пароль с паролем в поле password_hash, для текущего пользователя, в таблице user.
     * Вызываеться из модели LoginForm.
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }
    /* Аутентификация пользователей */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }



    public function getId()
    {
        return $this->id_user;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
    }

    public function getAuthKey()
    {

    }

    public function validateAuthKey($authKey)
    {
       // return $this->auth_key === $authKey;
    }
}
