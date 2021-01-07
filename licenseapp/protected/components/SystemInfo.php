<?php
/**
* $Id: SystemInfo.php 43 2015-12-08 14:32:20Z chrdel $
*          
* EXCLUSIVE LICENSE
* THE INFORMATION AND COMPUTER SOURCE CODE CONTAINED WITHIN THIS PROGRAM SCRIPT IS
* THE EXCLUSIVE PROPERTY OF HEALTHFIRST FINANCIAL, LLC. USE MUST BE AUTHORIZED UNDER WRITTEN
* LICENSE OBTAINED FROM HEALTHFIRST FINANCIAL, LLC. USE AT YOUR OWN RISK. NO WARANTY EITHER
* EXPRESSED OR IMPLIED.
*
* UNAUTHORIZED USE, ALTERATION, COPYING, OR REDISTRIBUTION IS STRICTLY PROHIBITED.
*
* @copyright Copyright (c) 2015 HealthFirst Financial, LLC.
*
* @author KG Financial Software Pvt Ltd (www.kgfsl.com), Chris DeLess
*
*/

/**
 * Description of  SystemInfo
 *
 * @author prabhu.p
 */
class SystemInfo
{

        public static function getSystemInfo()
        {
                $colspan = 6;
                $phpSniff = new phpSniff();
                echo "<table><tr><th colspan = '$colspan'>" . Yii::app()->name . "</th></tr>";
                echo "<tr><th colspan = '$colspan'> PHP Extenstions Installed </th></tr>";
                echo '<tr>';
                $extenstions = get_loaded_extensions();
                for ($index = 0; $index < count($extenstions); $index++)
                {
                        echo '<td width = "20%">' . $extenstions[$index] . "</td>";
                        if ($index % $colspan == 0)
                        {
                                echo '</tr><tr>';
                        }
                }
                echo '</tr>';

                echo "<tr><th colspan = '$colspan'> Browser Information </th></tr>";
                $extenstions = $phpSniff->_browser_info;
                $index = 0;
                echo '<tr>';
                foreach ($extenstions as $key => $value)
                {
                        echo '<td width = "20%">' . $key . " : " . $value . "</td>";
                        $index++;
                        if ($index % $colspan == 0)
                        {
                                echo '</tr><tr>';
                        }
                }
                echo '</tr>';
                echo '</table>';
        }

        public static function getErroredSystemInfo($colspan = 2, $isRecord = false, $isLast = false)
        {
                $phpSniff = new phpSniff();
                $errorMessage = '';
                $colspan1 = $colspan / 2;
                if ($colspan == 2)
                {
                        $errorMessage = SystemInfo::getErrorMsgCSS();
                        $errorMessage .= "<table style='BORDER-COLLAPSE: collapse;>";
                }
                $errorMessage .= "<tr><th class = 'header' colspan = '$colspan'> Browser Information </th></tr>";
                $errorMessage .= "<tr>";
                $errorMessage .= "<th class = 'column1' colspan = '$colspan1'>System Detail</th>";
                $errorMessage .= "<td class = 'column2' colspan = '$colspan1'>" . $phpSniff->_browser_info['platform'] . " - " . $phpSniff->_browser_info['os'] . "</td>";
                $errorMessage .= "</tr><tr>";
                $errorMessage .= "<th class = 'column1' colspan = '$colspan1'>Browser Detail</th>";
                $errorMessage .= "<td class = 'column2' colspan = '$colspan1'>" . $phpSniff->_browser_info['ua'] . "</td>";
                $errorMessage .= "</tr><tr>";
                $errorMessage .= "<th class = 'column1' colspan = '$colspan1'>IP Detail</th>";
                $errorMessage .= "<td class = 'column2' colspan = '$colspan1'>" . $phpSniff->_browser_info['ip'] . "</td>";
                $errorMessage .= "</tr><tr>";
                $errorMessage .= "<th class = 'column1' colspan = '$colspan1'>JavaScript Detail </th>";
                $errorMessage .= "<td class = 'column2' colspan = '$colspan1'>" . $phpSniff->_browser_info['javascript'] . "</td>";
                $errorMessage .= "</tr>";
                if ($isLast)
                {
                        $errorMessage .= "</table>";
                }
                return print_r($errorMessage, $isRecord);
        }

        public static function getErroredUserInfo($colspan = 2, $isRecord = false, $isLast = false)
        {
                $errorMessage = '';
                $colspan1 = $colspan / 2;
                if ($colspan == 2)
                {
                        $errorMessage = SystemInfo::getErrorMsgCSS();
                        $errorMessage .= "<table style='BORDER-COLLAPSE: collapse;>";
                }
                $errorMessage .= "<tr><th class = 'header' colspan = '$colspan'> User Information </th></tr>";
                $errorMessage .= "<tr>";
                $errorMessage .= "<th class = 'column1' colspan = '$colspan1'>User ID</th>";
                $errorMessage .= "<td class = 'column2' colspan = '$colspan1'>" . Yii::app()->user->name . "</td>";
                $errorMessage .= "</tr><tr>";
                $errorMessage .= "<th class = 'column1' colspan = '$colspan1'>User Name</th>";
                $errorMessage .= "<td class = 'column2' colspan = '$colspan1'>" .  " (" . Yii::app()->user->user_id . ")</td>";
                $errorMessage .= "</tr><tr>";
                $errorMessage .= "<th class = 'column1' colspan = '$colspan1'>Application Mode</th>";
                $errorMessage .= "<td class = 'column2' colspan = '$colspan1'>" . ENV_MODE . " APPLICATION</td>";
                $errorMessage .= "</tr>";
                if ($isLast)
                {
                        $errorMessage .= "</table>";
                }
                return print_r($errorMessage, $isRecord);
        }

        public static function getErrorMsgCSS()
        {
                return '<style>
                TABLE {}
                
                .column1 {
                    border-color: #2E7D32; 
                    border-style: solid;
                    border-width: 1pt;
                    color: #000000;
                    font-family: "Times New Roman",serif;
                    font-size: 11pt;
                    font-style: normal;
                    font-weight: 700;
                    padding-left: 10px;
                    padding-right: 10px;
                    padding-top: 1px;
                    text-align: left;
                    text-decoration: none;
                    vertical-align: top;
                    white-space: normal;
                }

                .column2 {
                    border-color: #2E7D32 #2E7D32 #2E7D32 -moz-use-text-color;
                    border-style: solid solid solid none;
                    border-width: 1pt 1pt 1pt medium;
                    color: #000000;
                    font-family: "Times New Roman",serif;
                    font-size: 11pt;
                    font-style: normal;
                    font-weight: 400;
                    padding-left: 5px;
                    padding-right: 10px;
                    padding-top: 1px;
                    text-align: left;
                    text-decoration: none;
                    vertical-align: top;
                    white-space: normal;
                }

                .header {
                    background: none repeat scroll 0 0 #2E7D32;
                    border-color: #2E7D32 -moz-use-text-color #2E7D32 #2E7D32;
                    border-style: solid none solid solid;
                    border-width: 1pt medium 1pt 1pt;
                    color: #FFFFFF;
                    font-family: "Times New Roman",serif;
                    font-size: 11pt;
                    font-style: normal;
                    padding-left: 1px;
                    padding-right: 1px;
                    padding-top: 1px;
                    text-align: center;
                    text-decoration: none;
                    vertical-align: top;
                    white-space: normal;
                }
                </style>';
        }

}

?>
