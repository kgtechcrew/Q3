<?php

/**
 * Description of UserDevice
 *
 * @author srinivasan.k
 */
class UserDevice extends CFormModel
{

    /**
     * Get the user browser
     * @return string
     */
    public static function get_user_browser()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $u_agent = $_SERVER['HTTP_USER_AGENT'];
            $ub      = '';

            if (preg_match('/MSIE/i', $u_agent) || preg_match('/Trident/i', $u_agent))
            {
                $ub = "ie";
            }
            elseif (preg_match('/Firefox/i', $u_agent))
            {
                $ub = "firefox";
            }
            elseif (preg_match('/Safari/i', $u_agent) && (preg_match('/Chrome/i', $u_agent) == 0 && preg_match('/Safari/i', $u_agent) == 1))
            {
                $ub = "safari";
            }
            elseif (preg_match('/Chrome/i', $u_agent) && (preg_match('/Chrome/i', $u_agent) == 1 && preg_match('/Safari/i', $u_agent) == 1))
            {
                $ub = "chrome";
            }
            elseif (preg_match('/Flock/i', $u_agent))
            {
                $ub = "flock";
            }
            elseif (preg_match('/Opera/i', $u_agent))
            {
                $ub = "opera";
            }

            return $ub;
        }
    }

    /**
     * Get the user system environment
     * @return string
     */
    public static function systemEnv()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $tablet_browser = 0;
            $mobile_browser = 0;

            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                $tablet_browser++;
            }

            if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                $mobile_browser++;
            }

            if ((isset($_SERVER['HTTP_ACCEPT']) && (strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml') > 0)) || ( (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']))))
            {
                $mobile_browser++;
            }
            $mobile_ua     = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
            $mobile_agents = array(
                'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird', 'blac',
                'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric', 'hipt', 'inno',
                'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c', 'lg-d', 'lg-g', 'lge-',
                'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi', 'mot-', 'moto', 'mwbp', 'nec-',
                'newt', 'noki', 'palm', 'pana', 'pant', 'phil', 'play', 'port', 'prox',
                'qwap', 'sage', 'sams', 'sany', 'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar',
                'sie-', 'siem', 'smal', 'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-',
                'tosh', 'tsm-', 'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp',
                'wapr', 'webc', 'winw', 'winw', 'xda ', 'xda-');

            if (in_array($mobile_ua, $mobile_agents))
            {
                $mobile_browser++;
            }

            if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini') > 0)
            {
                $mobile_browser++;
                //Check for tablets on opera mini alternative headers
                $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA']) ? $_SERVER['HTTP_X_OPERAMINI_PHONE_UA'] : (isset($_SERVER['HTTP_DEVICE_STOCK_UA']) ? $_SERVER['HTTP_DEVICE_STOCK_UA'] : ''));
                if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua))
                {
                    $tablet_browser++;
                }
            }

            if ($tablet_browser > 0)
            {
                $env = 'tablet';
            }
            else if ($mobile_browser > 0)
            {
                $env = 'mobile';
            }
            else
            {
                $env = 'desktop';
            }

            return $env;
        }
    }

    /**
     * Get the user OS.
     * @return string
     */
    public static function getOS()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $user_agent  = $_SERVER['HTTP_USER_AGENT'];
            $os_platform = "Unknown OS Platform";

            $os_array = array(
                '/windows nt 10/i'      => 'Windows 10',
                '/windows nt 6.3/i'     => 'Windows 8.1',
                '/windows nt 6.2/i'     => 'Windows 8',
                '/windows nt 6.1/i'     => 'Windows 7',
                '/windows nt 6.0/i'     => 'Windows Vista',
                '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
                '/windows nt 5.1/i'     => 'Windows XP',
                '/windows xp/i'         => 'Windows XP',
                '/windows nt 5.0/i'     => 'Windows 2000',
                '/windows me/i'         => 'Windows ME',
                '/win98/i'              => 'Windows 98',
                '/win95/i'              => 'Windows 95',
                '/win16/i'              => 'Windows 3.11',
                '/macintosh|mac os x/i' => 'Mac OS X',
                '/mac_powerpc/i'        => 'Mac OS 9',
                '/linux/i'              => 'Linux',
                '/ubuntu/i'             => 'Ubuntu',
                '/iphone/i'             => 'iPhone',
                '/ipod/i'               => 'iPod',
                '/ipad/i'               => 'iPad',
                '/android/i'            => 'Android',
                '/blackberry/i'         => 'BlackBerry',
                '/webos/i'              => 'Mobile'
            );

            foreach ($os_array as $regex => $value)
            {

                if (preg_match($regex, $user_agent))
                {
                    $os_platform = $value;
                }
            }

            return $os_platform;
        }
    }

}
