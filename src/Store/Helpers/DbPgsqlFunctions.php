<?php

namespace ByJG\AnyDataset\Store\Helpers;

use ByJG\AnyDataset\Helpers\SqlHelper;

class DbPgsqlFunctions extends DbBaseFunctions
{

    public function concat($str1, $str2 = null)
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
            $sql = $sql . " LIMIT x OFFSET y";
        }

        return preg_replace(
            '~(\s[Ll][Ii][Mm][Ii][Tt])\s.*?\s([Oo][Ff][Ff][Ss][Ee][Tt])\s.*~',
            '$1 ' . $qty .' $2 ' .$start,
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
            $column = 'current_timestamp';
        }

        $pattern = [
            'Y' => "YYYY",
            'y' => "YY",
            'M' => "Mon",
            'm' => "MM",
            'Q' => "Q",
            'q' => "Q",
            'D' => "DD",
            'd' => "DD",
            'h' => "HH",
            'H' => "HH24",
            'i' => "MI",
            's' => "SS",
            'a' => "AM",
            'A' => "AM",
        ];

        return sprintf(
            "TO_CHAR(%s,'%s')",
            $column,
            implode('', $this->prepareSqlDate($format, $pattern, ''))
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

    public function executeAndGetInsertedId($dbdataset, $sql, $param, $sequence = null)
    {
        $idInserted = parent::executeAndGetInsertedId($dbdataset, $sql, $param);
        $iterator = $dbdataset->getIterator(
            SqlHelper::createSafeSQL(
                "select currval(':sequence') id",
                array(':sequence' => $sequence)
            )
        );

        if ($iterator->hasNext()) {
            $singleRow = $iterator->moveNext();
            $idInserted = $singleRow->getField("id");
        }

        return $idInserted;
    }
}
