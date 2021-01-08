<?php

return CMap::mergeArray(
                require(dirname(__FILE__) . '/main.php'), array(
            'components' => array(
                'fixture' => array(
                    'class' => 'system.test.CDbFixtureManager',
                ),
                'db'      => array(
                    'connectionString' => 'mysql:host=' . DB_IP . ';dbname=' . DB_NAME,
                    'username'         => DB_USERNAME,
                    'password'         => DB_PASSWORD,
                    'tablePrefix'      => 'tbl_',
                    'emulatePrepare'   => true,
                ),
            ),
                )
);
