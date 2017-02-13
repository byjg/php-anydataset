<?php

namespace ByJG\AnyDataset\Store;

use ByJG\Util\Uri;
use PDO;

class PdoOci extends DbPdoDriver
{

    public function __construct(Uri $connUri)
    {
        $strcnn = $connUri->getDriver() . ":dbname=" . DbOci8Driver::getTnsString($connUri);

        $postOptions = [
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        parent::__construct(null, [], $postOptions);
    }
}
