<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Exception\NotAvailableException;
use ByJG\AnyDataset\Repository\DBDataset;

class DbDblibFunctions extends DbBaseFunctions
{

    public function concat($s1, $s2 = null)
    {
        return implode(func_get_args(), ' + ');
    }

    /**
     * Given a SQL returns it with the proper LIMIT or equivalent method included
     * @param string $sql
     * @param int $start
     * @param int $qty
     * @return string
     * @throws NotAvailableException
     */
    public function limit($sql, $start, $qty = null)
    {
        throw new NotAvailableException("DBLib does not support LIMIT feature.");
    }

    /**
     * Given a SQL returns it with the proper TOP or equivalent method included
     * @param string $sql
     * @param int $qty
     * @return string
     */
    public function top($sql, $qty)
    {
        if (stripos($sql, ' TOP ') === false) {
            return  preg_replace("/^\\s*(select) /i", "\\1 top $qty ", $sql);
        }

        return preg_replace(
            '~(\s[Tt][Oo][Pp])\s.*?\d+\s~',
            '$1 ' . $qty . ' ',
            $sql
        );
    }

    /**
     * Return if the database provider have a top or similar function
     * @return bool
     */
    public function hasTop()
    {
        return true;
    }

    /**
     * Return if the database provider have a limit function
     * @return bool
     */
    public function hasLimit()
    {
        return false;
    }

    /**
     * Format date column in sql string given an input format that understands Y M D

*
*@param string $format
     * @param bool|string $column
     * @return string
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
     */
    public function sqlDate($format, $column = null)
    {
        if (is_null($column)) {
            $column = "getdate()";
        }

        $pattern = [
            'Y' => "YYYY",
            'y' => "YY",
            'M' => "MM",
            'm' => "M",
            'Q' => "",
            'q' => "",
            'D' => "dd",
            'd' => "dd",
            'h' => "H",
            'H' => "HH",
            'i' => "mm",
            's' => "ss",
            'a' => "",
            'A' => "",
        ];

        $preparedSql = $this->prepareSqlDate($format, $pattern, '');

        return sprintf(
            "FORMAT(%s, '%s')",
            $column,
            implode('', $preparedSql)
        );
    }

    /**
     * Format a string date to a string database readable format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
    public function toDate($date, $dateFormat)
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
    public function fromDate($date, $dateFormat)
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
    public function executeAndGetInsertedId($dbdataset, $sql, $param)
    {
        $insertedId = parent::executeAndGetInsertedId($dbdataset, $sql, $param);
        $iterator = $dbdataset->getIterator("select @@identity id");
        if ($iterator->hasNext()) {
            $singleRow = $iterator->moveNext();
            $insertedId = $singleRow->getField("id");
        }

        return $insertedId;
    }
}
