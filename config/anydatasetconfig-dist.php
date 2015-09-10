<?php
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
