<?php

namespace ByJG\AnyDataset\Database;

use ByJG\Util\Uri;

class PdoOdbc extends DbPdoDriver
{

    public function __construct(Uri $connUri)
    {
        parent::__construct($connUri, [], []);
    }
}
