<?php

namespace ByJG\AnyDataset;

interface DbFunctionsInterface
{

    /**
     * Given two or more string the system will return the string containing de proper SQL commands to concatenate these string;
     * use:
     *      for ($i = 0, $numArgs = func_num_args(); $i < $numArgs ; $i++)
     * to get all parameters received.
     *
     * @param string $str1
     * @param string|null $str2
     * @return string
     */
    public function concat($str1, $str2 = null);

    /**
     * Given a SQL returns it with the proper LIMIT or equivalent method included
     * @param string $sql
     * @param int $start
     * @param int $qty
     * @return string
     */
    public function limit($sql, $start, $qty);

    /**
     * Given a SQL returns it with the proper TOP or equivalent method included
     * @param string $sql
     * @param int $qty
     * @return string
     */
    public function top($sql, $qty);

    /**
     * Return if the database provider have a top or similar function
     * @return bool
     */
    public function hasTop();

    /**
     * Return if the database provider have a limit function
     * @return bool
     */
    public function hasLimit();

    /**
     * Format date column in sql string given an input format that understands Y M D
     *
     * @param string $format
     * @param null|string $column
     * @return string
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
     */
    public function sqlDate($format, $column = null);

    /**
     * Format a string date to a string database readable format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
    public function toDate($date, $dateFormat);

    /**
     * Format a string database readable format to a string date in a free format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
    public function fromDate($date, $dateFormat);

    /**
     *
     * @param DbDriverInterface $dbdataset
     * @param string $sql
     * @param array $param
     * @return int
     */
    public function executeAndGetInsertedId(DbDriverInterface $dbdataset, $sql, $param);
}
