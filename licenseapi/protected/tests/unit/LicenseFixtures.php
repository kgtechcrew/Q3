<?php

/**
 * License Fixtures test information.
 */
class LicenseFixtures
{

    public function userInformation()
    {
        return array('record1' =>
            array('udt_firstname' => 'FIRST NAME',
                'udt_lastname'  => 'LAST NAME',
                'udt_email'     => 'test_user',
                'udt_password'  => 'Kgisl@123')
        );
    }

    /**
     * Default looged user information
     * @return type
     */
    public function licenseUserHistory()
    {
        return array('1' =>
            array('user_id'         => '',
                'pat_sys_ip'      => '192.168.1.24',
                'pat_sys_browser' => 'firefox',
                'pat_sys_os'      => 'Windows 10',
                'pat_dev_type'    => 'mobile',
            ),
            '2' =>
            array('user_id'         => '',
                'pat_sys_ip'      => '192.168.1.24',
                'pat_sys_browser' => 'chrome',
                'pat_sys_os'      => 'Windows 8',
                'pat_dev_type'    => 'desktop',
            ),
        );
    }

}
