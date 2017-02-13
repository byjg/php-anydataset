<?php

namespace ByJG\AnyDataset\Database;

use ByJG\Util\Uri;
use PDO;

class PdoLiteral extends DbPdoDriver
{

    public function __construct(Uri $connString, $preOptions = null, $postOptions = null)
    {
        $postOptions = [
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        parent::__construct($connString, $preOptions, $postOptions);
    }
}
