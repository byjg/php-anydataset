<?php

namespace ByJG\AnyDataset\Store;

use ByJG\Util\Uri;

class PdoSqlite extends DbPdoDriver
{

    public function __construct(Uri $connUri)
    {
        parent::__construct($connUri, [], []);
    }
}
