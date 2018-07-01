<?php

namespace ByJG\AnyDataset\Store;

use ByJG\Util\Uri;
use PDO;

class PdoMysql extends DbPdoDriver
{

    protected $mysqlAttr = [
        "ca" => PDO::MYSQL_ATTR_SSL_CA,
        "capath" => PDO::MYSQL_ATTR_SSL_CAPATH,
        "cert" => PDO::MYSQL_ATTR_SSL_CERT,
        "key" => PDO::MYSQL_ATTR_SSL_KEY,
        "cipher" => PDO::MYSQL_ATTR_SSL_CIPHER,
        "verifyssl" => 1014 // PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT (>=7.1)
    ];

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

        if (!empty($connUri->getQuery())) {
            foreach ($this->mysqlAttr as $key => $property) {
                $value = $connUri->getQueryPart($key);
                if (!empty($value)) {
                    $prepValue = urldecode($value);
                    if ($prepValue === 'false') {
                        $prepValue = false;
                    } else if ($prepValue === 'true') {
                        $prepValue = true;
                    }
                    $preOptions[$property] = $prepValue;
                }
            }
        }

        $this->setSupportMultRowset(true);

        parent::__construct($connUri, $preOptions, $postOptions);
    }
}
