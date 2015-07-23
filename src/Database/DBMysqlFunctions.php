<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Repository\DBDataSet;

class DBMySQLFunctions extends DBBaseFunctions
{
    private $sysTimeStamp = 'NOW()';

	function Concat($s1, $s2 = null)
	{
		$sql = "concat(";
	 	for ($i = 0, $numArgs = func_num_args(); $i < $numArgs ; $i++)
	 	{
	 		$var = func_get_arg($i);
	 		$sql .= ($i==0 ? "" : ",") . $var;
	 	}
	 	$sql .= ")";

	 	return $sql;
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
		if (strpos($sql, ' LIMIT ') === false)
		{
			return $sql .= " LIMIT $start, $qty ";
		}
		else
		{
			return $sql;
		}
	}

	/**
	 * Given a SQL returns it with the proper TOP or equivalent method included
	 * @param string $sql
	 * @param int $qty
	 * @return string
	 */
	function Top($sql, $qty)
	{
		return $this->Limit($sql, 0, $qty);
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
		return true;
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
		if (!$col) $col = $this->sysTimeStamp;
		$s = 'DATE_FORMAT('.$col.",'";
		$concat = false;
		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			$ch = $fmt[$i];
			switch($ch) {
			case 'Y':
			case 'y':
				$s .= '%Y';
				break;
			case 'Q':
			case 'q':
				$s .= "'),Quarter($col)";

				if ($len > $i+1) $s .= ",DATE_FORMAT($col,'";
				else $s .= ",('";
				$concat = true;
				break;
			case 'M':
				$s .= '%b';
				break;

			case 'm':
				$s .= '%m';
				break;
			case 'D':
			case 'd':
				$s .= '%d';
				break;

			case 'H':
				$s .= '%H';
				break;

			case 'h':
				$s .= '%I';
				break;

			case 'i':
				$s .= '%i';
				break;

			case 's':
				$s .= '%s';
				break;

			case 'a':
			case 'A':
				$s .= '%p';
				break;

			default:

				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				$s .= $ch;
				break;
			}
		}
		$s.="')";
		if ($concat) $s = "CONCAT($s)";
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
		$it = $dbdataset->getIterator("select LAST_INSERT_ID() id");
		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			$id = $sr->getField("id");
		}

		return $id;
	}

}

?>
