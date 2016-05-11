<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;

class PdoOdbc extends DBPDODriver
{

    public function __construct(ConnectionManagement $connMngt)
    {
        $strcnn = $connMngt->getDriver() . ":" . $connMngt->getServer();

        parent::__construct($connMngt, $strcnn, [], []);
    }
}
