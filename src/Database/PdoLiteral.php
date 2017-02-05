<?php

namespace ByJG\AnyDataset\Database;

use PDO;

class PdoLiteral extends DbPdoDriver
{

    public function __construct($connString)
    {
        $postOptions = [
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        parent::__construct(null, $connString, [], $postOptions);
    }
}
