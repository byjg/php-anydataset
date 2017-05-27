<?php

namespace ByJG\AnyDataset;

use ByJG\AnyDataset\Dataset\GenericIterator;
use ByJG\Util\Uri;

interface DbDriverInterface
{

    /**
     * @param string $sql
     * @param array|null $params
     * @return GenericIterator
     */
    public function getIterator($sql, $params = null);

    public function getScalar($sql, $array = null);

    public function getAllFields($tablename);

    public function execute($sql, $array = null);

    public function executeAndGetId($sql, $array = null);

    /**
     * @return DbFunctionsInterface
     */
    public function getDbHelper();

    public function beginTransaction();

    public function commitTransaction();

    public function rollbackTransaction();

    /**
     * @return \PDO
     */
    public function getDbConnection();

    /**
     * @return Uri
     */
    public function getUri();

    public function setAttribute($name, $value);

    public function getAttribute($name);

    public function isSupportMultRowset();

    public function setSupportMultRowset($multipleRowSet);
}
