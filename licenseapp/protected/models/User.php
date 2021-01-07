<?php

class User extends CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'udt_user_details';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {

        return array(
                /* array('udt_licenseid, udt_email, udt_password, udt_firstname, udt_lastname, udt_active, udt_entered_date', 'required'),
                  array('udt_licenseid, udt_email', 'length', 'max'=>50),
                  array('udt_password, udt_firstname, udt_lastname', 'length', 'max'=>100),
                  array('udt_active', 'length', 'max'=>1),

                  array('udt_id, udt_licenseid, udt_email, udt_password, udt_firstname, udt_lastname, udt_active, udt_entered_date', 'safe', 'on'=>'search'), */
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'udt_id'           => 'Udt',
            'udt_licenseid'    => 'Udt Licenseid',
            'udt_email'        => 'Udt Email',
            'udt_password'     => 'Udt Password',
            'udt_firstname'    => 'Udt Firstname',
            'udt_lastname'     => 'Udt Lastname',
            'udt_active'       => 'Udt Active',
            'udt_entered_date' => 'Udt Entered Date',
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function validatePassword($password, $login_pwd)
    {
        $validatedHash = md5($login_pwd);
        if ($password === $validatedHash)
        {
            return true;
        }
        return false;
    }

}
