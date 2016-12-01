<?php

namespace ByJG\AnyDataset\Database\Expressions;

use ByJG\AnyDataset\Repository\DBDataset;

class DbMysqlFunctions extends DbBaseFunctions
{

    public function concat($str1, $str2 = null)
    {
        return "concat(" . implode(func_get_args(), ', ') . ")";
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
     * @param string|null $column
     * @return string
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
     */
    public function sqlDate($format, $column = null)
    {
        if (is_null($column)) {
            $column = 'now()';
        }

        $pattern = [
            'Y' => "%Y",
            'y' => "%y",
            'M' => "%b",
            'm' => "%m",
            'Q' => "",
            'q' => "",
            'D' => "%d",
            'd' => "%d",
            'h' => "%I",
            'H' => "%H",
            'i' => "%i",
            's' => "%s",
            'a' => "%p",
            'A' => "%p",
        ];

        $preparedSql = $this->prepareSqlDate($format, $pattern, '');

        return sprintf(
            "DATE_FORMAT(%s,'%s')",
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
        $id = parent::executeAndGetInsertedId($dbdataset, $sql, $param);
        $it = $dbdataset->getIterator("select LAST_INSERT_ID() id");
        if ($it->hasNext()) {
            $sr = $it->moveNext();
            $id = $sr->getField("id");
        }

        return $id;
    }
}
