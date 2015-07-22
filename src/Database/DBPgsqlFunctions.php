<?php
/*
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 * 
 *  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
 *  for more information.
 *  
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 *=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= 
 */

/**
 * @package xmlnuke
 */
namespace ByJG\AnyDataset\Database;

use Xmlnuke\Core\Enum\DATEFORMAT;

class DBPGSqlFunctions extends DBBaseFunctions
{

	function Concat($s1, $s2 = null)
	{
	 	for ($i = 0, $numArgs = func_num_args(); $i < $numArgs ; $i++)
	 	{
	 		$var = func_get_arg($i);
	 		$sql .= ($i==0 ? "" : " || ") . $var;
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
	function Limit($sql, $start, $qty)
	{
		if (strpos($sql, ' LIMIT ') === false)
		{
			return $sql .= " LIMIT $qty OFFSET $start ";
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
		$s = 'TO_CHAR('.$col.",'";

		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			$ch = $fmt[$i];
			switch($ch) {
			case 'Y':
			case 'y':
				$s .= 'YYYY';
				break;
			case 'Q':
			case 'q':
				$s .= 'Q';
				break;

			case 'M':
				$s .= 'Mon';
				break;

			case 'm':
				$s .= 'MM';
				break;
			case 'D':
			case 'd':
				$s .= 'DD';
				break;

			case 'H':
				$s.= 'HH24';
				break;

			case 'h':
				$s .= 'HH';
				break;

			case 'i':
				$s .= 'MI';
				break;

			case 's':
				$s .= 'SS';
				break;

			case 'a':
			case 'A':
				$s .= 'AM';
				break;

			default:
			// handle escape characters...
				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				if (strpos('-/.:;, ',$ch) !== false) $s .= $ch;
				else $s .= '"'.$ch.'"';

			}
		}
		return $s. "')";
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
		return parent::toDate($date, $dateFormat, $hour);
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
		return parent::fromDate($date, $dateFormat, $hour);
	}
	
	function executeAndGetInsertedId($dbdataset, $sql, $param, $sequence = null)
	{
		$id = parent::executeAndGetInsertedId($dbdataset, $sql, $param);
		$it = $dbdataset->getIterator(SQLHelper::createSafeSQL("select currval(':sequence') id", array(':sequence' => $sequence)));
		if ($it->hasNext())
		{
			$sr = $it->moveNext();
			$id = $sr->getField("id");
		}

		return $id;
	}	
}

?>
