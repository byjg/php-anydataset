<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;
use PDO;

class PdoOci extends DbPdoDriver
{

    public function __construct(ConnectionManagement $connMngt)
    {
        $strcnn = $connMngt->getDriver() . ":dbname=" . DbOci8Driver::getTnsString($connMngt);

        $postOptions = [
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        parent::__construct($connMngt, $strcnn, [], $postOptions);
    }
}
