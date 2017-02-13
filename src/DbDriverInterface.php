<?php

namespace ByJG\AnyDataset;

interface DbDriverInterface
{

    /**
     * @param string $sql
     * @param null $array
     * @return IteratorInterface
     */
    public function getIterator($sql, $array = null);

    public function getScalar($sql, $array = null);

    public function getAllFields($tablename);

    public function executeSql($sql, $array = null);

    public function beginTransaction();

    public function commitTransaction();

    public function rollbackTransaction();

    public function getDbConnection();

    public function setAttribute($name, $value);

    public function getAttribute($name);
}
