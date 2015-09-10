<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;
use ByJG\AnyDataset\Database\DBOci8Driver;
use ByJG\AnyDataset\Database\DBPDODriver;
use PDO;

class PdoOci extends DBPDODriver
{

    public function __construct(ConnectionManagement $connMngt)
    {
        $strcnn = $connMngt->getDriver() . ":dbname=" . DBOci8Driver::getTnsString($connMngt);

        $postOptions = [
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        parent::__construct($connMngt, $strcnn, [], $postOptions);
    }
}
