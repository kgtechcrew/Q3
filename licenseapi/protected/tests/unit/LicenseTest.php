<?php

include_once 'LicenseFixtures.php';

/**
 * Description of LicenseTest
 *
 * @author srinivasan.k
 */
class LicenseTest extends CDbTestCase
{

    /**
     * @var EntityMaster
     */
    protected $user;
    protected $controller;
    protected $userfixtures;
    protected $userlicensehistory;
    protected $default_user_id = 2; // Default user id for testing

    public function setUp()
    {
        parent::setUp();
        $this->globalsetting      = new GlobalSettings();
        $this->user               = new User();
        $this->userfixtures       = new LicenseFixtures();
        $this->userlicensehistory = new UserLicenseHistory();
    }

    /**
     * test concurrent users exceeded
     */
    public function testConcurrentUsersExceeded()
    {
        $actual_output = $this->globalsetting->isConcurrentUsersExceeded();
        $expected      = $this->checkConcurrentUsersExceeded();

        if ($expected)
        {
            $this->assertEquals($expected, $actual_output, 'Conncurrent user exceeded');
        }
        else
        {
            $this->assertEquals($expected, $actual_output, 'Conncurrent user not exceeded');
        }
    }

    /**
     * check Concurrent Users Exceeded
     * TRUE  - user allowed count has exceed
     * FALSE - user allowed count has not exceed
     * @return boolean
     */
    public function checkConcurrentUsersExceeded()
    {
        $active_current_user_count = $this->getActiveUser();
        $user_allowed_count        = $this->getUsersAllowedCount();
        $expected                  = false;
        $user_waiting_count        = 1;

        if (($active_current_user_count + $user_waiting_count) > $user_allowed_count)
        {
            $expected = true;
        }
        return $expected;
    }

    /**
     * get user allowed count
     * @return type
     */
    public function getUsersAllowedCount()
    {
        $userallowedcount = $this->globalsetting->usersAllowedCount();
        return $userallowedcount;
    }

    /**
     * get active user count
     * @return type
     */
    public function getActiveUser()
    {
        $active_current_user = $this->globalsetting->concurrentUserCount();
        return $active_current_user;
    }

    public function testUsersAllowedDevicesExceeded()
    {
        $user_list     = $this->storeDeviceInfo();
        $actual_output = $this->globalsetting->isUsersAllowedDevicesExceeded($this->default_user_id);
        $expected      = $this->checkUsersAllowedDevicesExceeded();
        if ($expected)
        {
            $this->assertEquals($expected, $actual_output, 'User Allowed Device Count Exceeded');
        }
        else
        {
            $this->assertEquals($expected, $actual_output, 'User Allowed Device Count Exceeded');
        }
    }

    /**
     * 
     * @return boolean
     */
    public function checkUsersAllowedDevicesExceeded()
    {
        $users_active_dev_count = $this->globalsetting->checkUserDevicesCount($this->default_user_id);
        $devices_allowed_count  = $this->getDevicesAllowedCount();
        $user_waiting_count     = 1;
        if (($users_active_dev_count + $user_waiting_count) > $devices_allowed_count)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * get device allowed count
     * @return type
     */
    public function getDevicesAllowedCount()
    {
        $device_allowed_count = $this->globalsetting->devicesAllowedCount();
        return $device_allowed_count;
    }

    /**
     * Store the same user multiple device information
     * Return the created user id list
     * @return type
     */
    public function storeDeviceInfo()
    {
        $device_info          = $this->userfixtures->licenseUserHistory();
        $device_allowed_count = $this->getDevicesAllowedCount();
        $i                    = 1;
        $user_id_list         = array();

        while ($i <= $device_allowed_count)
        {
            $device_info[$i]['user_id'] = $this->default_user_id;
            $user_id_list[$i]           = $this->userlicensehistory->insertLicenseUserHistory($device_info[$i]);
            $i++;
        }
        return $user_id_list;
    }

    public function createNewUser()
    {
        $userallowedcount = $this->getUsersAllowedCount();
        $userallowedcount = $userallowedcount + 1;
        $i                = 1;

        while ($i <= $userallowedcount)
        {
            $testuser_information                             = $this->userfixtures->userInformation();
            $testuser_information['record1']['udt_firstname'] = $testuser_information['record1']['udt_firstname'] . $i;
            $testuser_information['record1']['udt_lastname']  = $testuser_information['record1']['udt_lastname'] . $i;
            $testuser_information['record1']['udt_email']     = $testuser_information['record1']['udt_email'] . $i . '@gmail.com';
            $this->user->insertUserInfo($testuser_information['record1']);
            $i++;
        }
    }

}
