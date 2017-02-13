<?php
/**
 * User: jg
 * Date: 13/02/17
 * Time: 15:48
 */

namespace ByJG\AnyDataset;

use ByJG\AnyDataset\Store\PdoLiteral;
use ByJG\Util\Uri;

class Factory
{
    /**
     * @param $connectionString
     * @param $schemesAlternative
     * @return \ByJG\AnyDataset\DbDriverInterface
     * @throws \ByJG\AnyDataset\Exception\NotFoundException
     * @throws \ByJG\AnyDataset\Exception\NotImplementedException
     */
    public static function getDbRelationalInstance($connectionString, $schemesAlternative = null)
    {
        $connectionUri = new Uri($connectionString);

        $scheme = $connectionUri->getScheme();

        $prefix = '\\ByJG\\AnyDataset\\Store\\';
        $validSchemes = array_merge(
            [
                "sqlrelay" => $prefix . "DbSqlRelayDriver",
                "oci8" => $prefix . "DbOci8Driver",
                "dblib" => $prefix . "PdoDblib",
                "mysql" => $prefix . "PdoMysql",
                "pgsql" => $prefix . "PdoPgsql",
                "oci" => $prefix . "PdoOci",
                "odbc" => $prefix . "PdoOdbc"
            ],
            (array)$schemesAlternative
        );

        $class = isset($validSchemes[$scheme]) ? $validSchemes[$scheme] : PdoLiteral::class;

        return new $class($connectionUri);
    }

    /**
     * Get a IDbFunctions class to execute Database specific operations.
     *
     * @param \ByJG\Util\Uri $connectionUri
     * @return \ByJG\AnyDataset\DbFunctionsInterface
     */
    public static function getDbFunctions(Uri $connectionUri)
    {
        $dbFunc = "\\ByJG\\AnyDataset\\Store\\Helpers\\Db"
            . ucfirst($connectionUri->getScheme())
            . "Functions";
        return new $dbFunc();
    }
}
