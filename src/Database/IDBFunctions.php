<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\DBDataSet;

interface IDBFunctions
{

	/**
	 * Given two or more string the system will return the string containing de proper SQL commands to concatenate these string;
	 * use:
	 * 		for ($i = 0, $numArgs = func_num_args(); $i < $numArgs ; $i++)
	 * to get all parameters received.
	 * @param string $s1
	 * @param string $s2
	 * @return string
	 */
	function Concat($s1, $s2 = null);

	/**
	 * Given a SQL returns it with the proper LIMIT or equivalent method included
	 * @param string $sql
	 * @param int $start
	 * @param int $qty
	 * @return string
	 */
	function Limit($sql, $start, $qty);

	/**
	 * Given a SQL returns it with the proper TOP or equivalent method included
	 * @param string $sql
	 * @param int $qty
	 * @return string
	 */
	function Top($sql, $qty);

	/**
	 * Return if the database provider have a top or similar function
	 * @return unknown_type
	 */
	function hasTop();

	/**
	 * Return if the database provider have a limit function
	 * @return bool
	 */
	function hasLimit();

    /**
	 * Format date column in sql string given an input format that understands Y M D
	 * @param string $fmt
     * @param string $col
     * @return string
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
	 */
	function SQLDate($fmt, $col=false);

    /**
     * Format a string date to a string database readable format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
	function toDate($date, $dateFormat);

    /**
     * Format a string database readable format to a string date in a free format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
	function fromDate($date, $dateFormat);

	/**
	 *
	 * @param DBDataSet $dbdataset
	 * @param string $sql
	 * @param array $param
	 * @return int
	 */
	function executeAndGetInsertedId($dbdataset, $sql, $param);
}

