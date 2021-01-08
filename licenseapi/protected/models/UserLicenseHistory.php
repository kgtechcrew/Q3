<?php

/**
 * This is the model class for table "license_his_user_log".
 *
 * The followings are the available columns in table 'license_his_user_log':
 * @property integer $id
 * @property integer $user_id
 * @property string $pat_login_time
 * @property string $pat_logout_time
 * @property string $login_status
 * @property string $logout_status
 * @property string $pat_sys_ip
 * @property string $pat_sys_browser
 * @property string $pat_sys_os
 * @property string $pat_dev_type
 */
class UserLicenseHistory extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'license_his_user_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array();
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
			
		);
	}

	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserLicenseHistory the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

/**
     * Store the user history information for unit testing.
     */
    public function insertLicenseUserHistory($data)
    {
        $model                  = new UserLicenseHistory();
        $model->user_id         = $data['user_id'];
        $model->pat_login_time  = date("Y-m-d H:i:s");
        $model->login_status    = 'S';
        $model->pat_sys_ip      = $data['pat_sys_ip'];
        $model->pat_sys_browser = $data['pat_sys_browser'];
        $model->pat_sys_os      = $data['pat_sys_os'];
        $model->pat_dev_type    = $data['pat_dev_type'];
        $model->save();
        return $model->id;
    }

    /**
     * Delete the stored device information for unit testing
     */
    public function deleteStoredDeviceInfo($user_id)
    {
        $model = UserLicenseHistory::model()->findByPk($user_id);
        if (!empty($model))
        {
            $model->delete();
        }
    }

}
