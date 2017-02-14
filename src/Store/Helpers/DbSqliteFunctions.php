<?php

namespace ByJG\AnyDataset\Store\Helpers;

use ByJG\AnyDataset\DbDriverInterface;
use ByJG\AnyDataset\Exception\NotImplementedException;

class DbSqliteFunctions extends DbBaseFunctions
{

    /**
     * @param string $s1
     * @param null $s2
     * @return string
     */
    public function concat($s1, $s2 = null)
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
    public function limit($sql, $start, $qty = null)
    {
        if (is_null($qty)) {
            $qty = 50;
        }

        if (stripos($sql, ' LIMIT ') === false) {
            $sql = $sql . " LIMIT x, y";
        }

        return preg_replace(
            '~(\s[Ll][Ii][Mm][Ii][Tt])\s.*?,\s*.*~',
            '$1 ' . $start .', ' .$qty,
            $sql
        );
    }

    /**
     * Given a SQL returns it with the proper TOP or equivalent method included
     * @param string $sql
     * @param int $qty
     * @return string
     */
    public function top($sql, $qty)
    {
        return $this->limit($sql, 0, $qty);
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
        return true;
    }

    /**
     * Format date column in sql string given an input format that understands Y M D
     *
     * @param string $format
     * @param string|null$column
     * @return string
     * @throws NotImplementedException
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
     */
    public function sqlDate($format, $column = null)
    {
        if (is_null($column)) {
            $column = "'now'";
        }

        $pattern = [
            'Y' => "%Y",
            'y' => "%Y",
            'M' => "%m",
            'm' => "%m",
            'Q' => "",
            'q' => "",
            'D' => "%d",
            'd' => "%d",
            'h' => "%H",
            'H' => "%H",
            'i' => "%M",
            's' => "%S",
            'a' => "",
            'A' => "",
        ];

        $preparedSql = $this->prepareSqlDate($format, $pattern, '');

        return sprintf(
            "strftime('%s', %s)",
            implode('', $preparedSql),
            $column
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
     * @param DbDriverInterface $dbdataset
     * @param string $sql
     * @param array $param
     * @return int
     */
    public function executeAndGetInsertedId(DbDriverInterface $dbdataset, $sql, $param)
    {
        $returnedId = parent::executeAndGetInsertedId($dbdataset, $sql, $param);
        $iterator = $dbdataset->getIterator("SELECT last_insert_rowid() id");
        if ($iterator->hasNext()) {
            $singleRow = $iterator->moveNext();
            $returnedId = $singleRow->getField("id");
        }

        return $returnedId;
    }
}
