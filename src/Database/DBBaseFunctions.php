<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\DBDataSet;
use Xmlnuke\Core\Enum\DATEFORMAT;
use Xmlnuke\Util\DateUtil;

/**
 * @package xmlnuke
 */
abstract class DBBaseFunctions implements IDBFunctions
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
	function Concat($s1, $s2 = null)
	{
		return "";
	}

	/**
	 * Given a SQL returns it with the proper LIMIT or equivalent method included
	 * @param string $sql
	 * @param int $start
	 * @param int $qty
	 * @return string
	 */
	function Limit($sql, $start, $qty)
	{
		return $sql;
	}

	/**
	 * Given a SQL returns it with the proper TOP or equivalent method included
	 * @param string $sql
	 * @param int $qty
	 * @return string
	 */
	function Top($sql, $qty)
	{
		return $sql;
	}

	/**
	 * Return if the database provider have a top or similar function
	 * @return unknown_type
	 */
	function hasTop()
	{
		return false;
	}

	/**
	 * Return if the database provider have a limit function
	 * @return bool
	 */
	function hasLimit()
	{
		return false;
	}

    /**
	 * Format date column in sql string given an input format that understands Y M D
	 * @param string $fmt
     * @param string $col
     * @return string
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
	 */
	function SQLDate($fmt, $col=false)
	{
		return "";
	}

    /**
	 * Format a string to database readable format.
	 * @param string $date
     * @param DATEFORMAT $dateFormat
     * @return string
     * @example $db->getDbFunctions()->toDate('26/01/1974', DATEFORMAT::DMY);
	 */
	function toDate($date, $dateFormat, $hour = false)
	{
		return DateUtil::ConvertDate($date, $dateFormat, DATEFORMAT::YMD, "-", $hour);
	}

    /**
	 * Format a string from database to a user readable format.
	 * @param string $date
     * @param DATEFORMAT $dateFormat
     * @return string
     * @example $db->getDbFunctions()->toDate('26/01/1974', DATEFORMAT::DMY);
	 */
	function fromDate($date, $dateFormat, $hour = false)
	{
		return DateUtil::ConvertDate($date, DATEFORMAT::YMD, $dateFormat, "/", $hour);
	}

	/**
	 *
	 * @param DBDataSet $dbdataset
	 * @param string $sql
	 * @param array $param
	 * @return int
	 */
	function executeAndGetInsertedId($dbdataset, $sql, $param, $sequence = null)
	{
		$dbdataset->execSQL($sql, $param);
		return -1;
	}
}




?>