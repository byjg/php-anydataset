<?php

namespace ByJG\AnyDataset\Store;

use ByJG\Util\Uri;

class PdoSqlite extends DbPdoDriver
{

    /**
     * PdoSqlite constructor.
     *
     * @param \ByJG\Util\Uri $connUri
     * @throws \ByJG\AnyDataset\Exception\NotAvailableException
     */
    public function __construct(Uri $connUri)
    {
        parent::__construct($connUri, [], []);
    }
}
