AnyDataset is already prepared for team development because the setup files used by the application is particular 
for each team member or environment (Dev, Staging or Test). The base file are dist.file. For example: 
* the config/anydatasetconfig.php file is private for the use. The base file is config/anydatasetconfig-dist.php

But sometimes you want to rely on a same setup for different and you do not want 
have to create a new setup for each new installation. 

The procedure is:

#### Setup your server do pass a environment variable to PHP (Nginx)

```
location ~ \.php$ {
  ; Add the follow line 
  ; change 'staging' for your environment 'live', 'test' or 'dev'
  fastcgi_param APPLICATION_ENV staging;
}
```

#### Get the Environment variable

```php
$repository = new DBDataset($_ENV['APPLICATION_ENV']);
```

#### Setup your config/anydatasetconfig-dist.php


```php
return [
    'connections' => [
        'development' => [
            'url' => 'pdodriver://root@localhost/dbname',
            'type' => 'dsn'
        ],
        'staging' => [
            'url' => 'pdodriver://root@192.168.1.100/dbname',
            'type' => 'dsn'
        ],
        'test' => [
            'url' => 'pdodriver://root@192.168.1.200/dbname',
            'type' => 'dsn'
        ],
        'live' => [
            'url' => '-- DO NOT COMMIT --',
            'type' => 'dsn'
        ]
    ]
];
```

