<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Exception\NotImplementedException;
use ByJG\AnyDataset\Repository\DBDataset;

class DBSqliteFunctions extends DBBaseFunctions
{

    function concat($s1, $s2 = null)
    {
        return implode(func_get_args(), ' || ');
    }

    /**
     * Given a SQL returns it with the proper LIMIT or equivalent method included
     * @param string $sql
     * @param int $start
     * @param int $qty
     * @return string
     */
    function limit($sql, $start, $qty)
    {
        if (strpos($sql, ' LIMIT ') === false) {
            return $sql . " LIMIT $start, $qty ";
        }

        return $sql;
    }

    /**
     * Given a SQL returns it with the proper TOP or equivalent method included
     * @param string $sql
     * @param int $qty
     * @return string
     */
    function top($sql, $qty)
    {
        return $this->limit($sql, 0, $qty);
    }

    /**
     * Return if the database provider have a top or similar function
     * @return bool
     */
    function hasTop()
    {
        return true;
    }

    /**
     * Return if the database provider have a limit function
     * @return bool
     */
    function hasLimit()
    {
        return true;
    }

    /**
     * Format date column in sql string given an input format that understands Y M D
     * @param string $fmt
     * @param string|bool $col
     * @return string
     * @throws NotImplementedException
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
     */
    function sqlDate($fmt, $col = false)
    {
        throw new NotImplementedException('Not implemented');
    }

    /**
     * Format a string date to a string database readable format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
    function toDate($date, $dateFormat)
    {
        return parent::toDate($date, $dateFormat);
    }

    /**
     * Format a string database readable format to a string date in a free format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
    function fromDate($date, $dateFormat)
    {
        return parent::fromDate($date, $dateFormat);
    }

    /**
     *
     * @param DBDataset $dbdataset
     * @param string $sql
     * @param array $param
     * @return int
     */
    function executeAndGetInsertedId($dbdataset, $sql, $param)
    {
        $id = parent::executeAndGetInsertedId($dbdataset, $sql, $param);
        $it = $dbdataset->getIterator("SELECT last_insert_rowid() id");
        if ($it->hasNext()) {
            $sr = $it->moveNext();
            $id = $sr->getField("id");
        }

        return $id;
    }
}
