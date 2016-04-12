<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\ConnectionManagement;

class PdoPgsql extends DBPDODriver
{

    public function __construct(ConnectionManagement $connMngt)
    {
        parent::__construct($connMngt, null, [], []);
    }
}
