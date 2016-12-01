<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;
use PDO;

class PdoMysql extends DbPdoDriver
{

    public function __construct(ConnectionManagement $connMngt)
    {
        $preOptions = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ];

        $postOptions = [
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        parent::__construct($connMngt, null, $preOptions, $postOptions);
    }
}
