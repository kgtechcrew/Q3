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

    
    /*
     * This Function will return all the logged in users currently being active in the application
     * Browser Details with IP is provided
     * No of devices logged in for a particular user is also displayed.
     */
    public function trackLoginUsers()
    {
        $sql          = 'SELECT 
                    concat(u.udt_firstname," ",u.udt_lastname) AS "User Name",
                    u.udt_email AS "User Email",
                    u.udt_licenseid AS LicenseId,
                    DATE_FORMAT(h.pat_login_time, "%m/%d/%Y H:i:s") AS "Login DateTime",
                    h.pat_sys_ip AS "IP",
                    h.pat_sys_browser AS "Browser",
                    h.pat_sys_os AS "Operating System",
                    h.pat_dev_type AS "Device Type",
                    COUNT(*) AS "Number Of Devices"
                        FROM license_his_user_log h
                            JOIN udt_user_details u ON h.user_id = u.udt_id
                               WHERE login_status = "S" AND logout_status IS NULL
                                    GROUP BY h.user_id HAVING COUNT(*) >= 1';
        $user_details = Yii::app()->db->createCommand($sql)->queryAll();
        return $user_details;
    }
}
