<?php

include_once 'LicenseFixtures.php';

/**
 * Description of LicenseTest
 *
 * @author srinivasan.k
 */
class LicenseTest extends CDbTestCase
{

    /** @var EntityMaster * */
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
     * Test Concurrent Users Exceeded
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
     * Check Concurrent Users Exceeded
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
     * Get User Allowed Count
     * @return type
     */
    public function getUsersAllowedCount()
    {
        $userallowedcount = $this->globalsetting->usersAllowedCount();
        return $userallowedcount;
    }

    /**
     * Get Active User Count
     * @return type
     */
    public function getActiveUser()
    {
        $active_current_user = $this->globalsetting->concurrentUserCount();
        return $active_current_user;
    }

    /**
     * Test Users Allowed Devices Exceeded
     */
    public function testUsersAllowedDevicesExceeded()
    {
        $list          = $this->storeDeviceInfo();
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
        $this->deleteStoredTestDevice($list);
    }

    /**
     * Check Users Allowed Devices Exceeded
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
     * Get Device Allowed Count
     * @return type
     */
    public function getDevicesAllowedCount()
    {
        $device_allowed_count = $this->globalsetting->devicesAllowedCount();
        return $device_allowed_count;
    }

    /**
     * Store The Same User Multiple Device Information
     * Return The Created User Id List
     * @return type
     */
    public function storeDeviceInfo()
    {
        $device_info          = $this->userfixtures->licenseUserHistory();
        $device_allowed_count = $this->getDevicesAllowedCount();
        $i                    = 1;
        $list                 = array();

        while ($i <= $device_allowed_count)
        {
            $device_info[$i]['user_id'] = $this->default_user_id;
            $list[$i]                   = $this->userlicensehistory->insertLicenseUserHistory($device_info[$i]);
            $i++;
        }
        return $list;
    }

    /**
     * Delete The Same User Multiple Device Information
     * @param type $user_list
     */
    public function deleteStoredTestDevice($list)
    {
        foreach ($list as $history_id)
        {
            $this->userlicensehistory->deleteStoredDeviceInfo($history_id);
        }
    }

}
