<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;

class PdoMysql extends DBPDODriver
{

    public function __construct(ConnectionManagement $connMngt)
    {
        parent::__construct($connMngt, null, [], []);
    }
}
