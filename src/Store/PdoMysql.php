<?php

namespace ByJG\AnyDataset\Store;

use ByJG\Util\Uri;
use PDO;

class PdoMysql extends DbPdoDriver
{

    /**
     * PdoMysql constructor.
     *
     * @param \ByJG\Util\Uri $connUri
     * @throws \ByJG\AnyDataset\Exception\NotAvailableException
     */
    public function __construct(Uri $connUri)
    {
        $preOptions = [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ];

        $postOptions = [
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::ATTR_EMULATE_PREPARES => true
        ];

        $sslCa = $connUri->getQueryPart("ca");
        $sslCert = $connUri->getQueryPart("cert");
        $sslKey = $connUri->getQueryPart("key");
        if (!empty($sslCa) && !empty($sslCert) && !empty($sslKey)) {
            $preOptions[PDO::MYSQL_ATTR_SSL_KEY] = urldecode($sslKey);
            $preOptions[PDO::MYSQL_ATTR_SSL_CERT] = urldecode($sslCert);
            $preOptions[PDO::MYSQL_ATTR_SSL_CA] = urldecode($sslCa);
        }

        $this->setSupportMultRowset(true);

        parent::__construct($connUri, $preOptions, $postOptions);
    }
}
