<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\DBDataSet;
use Xmlnuke\Core\Enum\DATEFORMAT;

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
	 * Format a string to database readable format.
	 * @param string $date
     * @param DATEFORMAT $dateFormat
     * @return string
     * @example $db->getDbFunctions()->toDate('26/01/1974', DATEFORMAT::DMY);
	 */
	function toDate($date, $dateFormat, $hour = false);

    /**
	 * Format a string from database to a user readable format.
	 * @param string $date
     * @param DATEFORMAT $dateFormat
     * @return string
     * @example $db->getDbFunctions()->toDate('26/01/1974', DATEFORMAT::DMY);
	 */
	function fromDate($date, $dateFormat, $hour = false);

	/**
	 *
	 * @param DBDataSet $dbdataset
	 * @param string $sql
	 * @param array $param
	 * @return int
	 */
	function executeAndGetInsertedId($dbdataset, $sql, $param);
}

?>