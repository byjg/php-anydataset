<?php
/**
 * 
 * The connection string is Data Source Name (DSN) like:
 * 
 *   DRIVER://USERNAME[:PASSWORD]@SERVER[:PORT]/DATABASE[?PARAMETERS]
 *   DRIVER:///path[?PARAMETERS]
 *   DRIVER://C:/PATH[?PARAMETERS]
 *
 *  DRIVER is :
 *    - The name of PHP PDO Driver;
 *    - 'oci8' for a native Oracle Oci
 *    - 'sqlrelay' for a native SqlRelay connection.
 *
 *  PARAMETERS are a couple of key pair value set like 'KEY1=VALUE1&KEY2=VALUE2&...'
 *  The parameters are used to setup the PDO.
 */

return [
    'debug' => false,
    'connections' => [
        'test' => [
            'url' => 'mysql://root@localhost/test',
            'type' => 'dsn'
        ],
        'testpw' => [
            'url' => 'mysql://root:mypass@localhost:3306/test',
            'type' => 'dsn'
        ]
    ]
];
