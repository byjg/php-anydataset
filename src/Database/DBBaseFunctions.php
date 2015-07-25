<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\DBDataSet;
use DateTime;

abstract class DBBaseFunctions implements IDBFunctions
{

    const DMY = "d-m-Y";
    const MDY = "m-d-Y";
    const YMD = "Y-m-d";
    const DMYH = "d-m-Y H:i:s";
    const MDYH = "m-d-Y H:i:s";
    const YMDH = "Y-m-d H:i:s";

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
     * Format a string date to a string database readable format.
     *
     * @param string $date
     * @param string $dateFormat
     * @return string
     */
	function toDate($date, $dateFormat)
	{
        $dateTime = DateTime::createFromFormat($dateFormat, $date);
        return $dateTime->format(self::YMDH);
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
        $dateTime = DateTime::createFromFormat(self::YMDH, $date);
        return $dateTime->format($dateFormat);
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




