<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Exception\NotAvailableException;
use ByJG\AnyDataset\Repository\DBDataSet;


class DBDblibFunctions extends DBBaseFunctions
{
	function concat($s1, $s2 = null)
	{
	 	for ($i = 0, $numArgs = func_num_args(); $i < $numArgs ; $i++)
	 	{
	 		$var = func_get_arg($i);
	 		$sql .= ($i==0 ? "" : "+") . $var;
	 	}

	 	return $sql;
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
		throw new NotAvailableException("DBLib does not support LIMIT feature.");
	}

	/**
	 * Given a SQL returns it with the proper TOP or equivalent method included
	 * @param string $sql
	 * @param int $qty
	 * @return string
	 */
	function top($sql, $qty)
	{
		return preg_replace("/^\s*(select) /i", "\\1 top $qty ", $sql);
	}

	/**
	 * Return if the database provider have a top or similar function
	 * @return unknown_type
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
		return false;
	}
	/**
	 * Format date column in sql string given an input format that understands Y M D
	 * @param string $fmt
     * @param string $col
     * @return string
     * @example $db->getDbFunctions()->SQLDate("d/m/Y H:i", "dtcriacao")
	 */
	function sqlDate($fmt, $col=false)
	{
		if (!$col) $col = "getdate()";
		$s = '';

		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			if ($s) $s .= '+';
			$ch = $fmt[$i];
			switch($ch) {
			case 'Y':
			case 'y':
				$s .= "datename(yyyy,$col)";
				break;
			case 'M':
				$s .= "convert(char(3),$col,0)";
				break;
			case 'm':
				$s .= "replace(str(month($col),2),' ','0')";
				break;
			case 'Q':
			case 'q':
				$s .= "datename(quarter,$col)";
				break;
			case 'D':
			case 'd':
				$s .= "replace(str(day($col),2),' ','0')";
				break;
			case 'h':
				$s .= "substring(convert(char(14),$col,0),13,2)";
				break;

			case 'H':
				$s .= "replace(str(datepart(hh,$col),2),' ','0')";
				break;

			case 'i':
				$s .= "replace(str(datepart(mi,$col),2),' ','0')";
				break;
			case 's':
				$s .= "replace(str(datepart(ss,$col),2),' ','0')";
				break;
			case 'a':
			case 'A':
				$s .= "substring(convert(char(19),$col,0),18,2)";
				break;

			default:
				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				$s .= $this->qstr($ch);
				break;
			}
		}
		return $s;
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
	 * @param DBDataSet $dbdataset
	 * @param string $sql
	 * @param array $param
	 * @return int
	 */
	function executeAndGetInsertedId($dbdataset, $sql, $param)
	{
		$id = parent::executeAndGetInsertedId($dbdataset, $sql, $param);
		$it = $dbdataset->getIterator("select @@identity id");
		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			$id = $sr->getField("id");
		}

		return $id;
	}
}

