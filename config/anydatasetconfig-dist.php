<?php

return [
    'debug' => false,
    'connections' => [
        'test' => [
            'name' => 'mysql://root@localhost/test',
            'type' => 'dsn'
        ],
        'testpw' => [
            'name' => 'mysql://root:mypass@localhost:3306/test',
            'type' => 'dsn'
        ]
    ]
];
