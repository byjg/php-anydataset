<?php

namespace ByJG\AnyDataset\Store;

use ByJG\Util\Uri;
use PDO;

class PdoOci extends DbPdoDriver
{

    public function __construct(Uri $connUri)
    {
        $this->connectionUri = $connUri;
        $strconn = $connUri->getScheme(). ":dbname=" . DbOci8Driver::getTnsString($connUri);

        // Create Connection
        $this->instance = new PDO(
            $strconn,
            $this->connectionUri->getUsername(),
            $this->connectionUri->getPassword()
        );

        $this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        $this->instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    }
}
