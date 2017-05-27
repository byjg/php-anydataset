<?php

namespace ByJG\AnyDataset\Store;

use ByJG\Util\Uri;
use PDO;

class PdoMysql extends DbPdoDriver
{

    public function __construct(Uri $connUri)
    {
        $preOptions = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ];

        $postOptions = [
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        $this->setSupportMultRowset(true);

        parent::__construct($connUri, $preOptions, $postOptions);
    }
}
