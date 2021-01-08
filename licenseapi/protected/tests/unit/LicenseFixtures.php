<?php

/**
 * License Fixtures Test information.
 *
 * @author srinivasan.k
 */
class LicenseFixtures
{

    /**
     *  User Login History Information
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
            '3' =>
            array('user_id'         => '',
                'pat_sys_ip'      => '192.168.1.24',
                'pat_sys_browser' => 'chrome',
                'pat_sys_os'      => 'Windows 8',
                'pat_dev_type'    => 'laptop',
            ),
        );
    }

}
