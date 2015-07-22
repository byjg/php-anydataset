<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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
 * Abstract XPath or SQL query commands to filter a AnyDataset based iterator. 
 * @package xmlnuke
 */
namespace ByJG\AnyDataset\Repository;

use Xmlnuke\Core\Enum\Relation;

class IteratorFilter
{
	const XPATH = 1;
	const SQL = 2;

	/**
	 * @var array
	 */
	private $_filters;

	/**
	*@desc IteratorFilter Constructor
	*/
	public function __construct()
	{
		$this->_filters = array();
	}

	/**
	*@param
	*@return string - XPath String
	*@desc Get the XPATH string
	*/
	public function getXPath()
	{
		$xpathFilter = $this->getFilter(IteratorFilter::XPATH, $param);
		//Debug::PrintValue($xpathFilter);

		if ($xpathFilter == "")
		{
			return "/anydataset/row";
		}
		else
		{
			return "/anydataset/row[".$xpathFilter."]";
		}
	}

	/**
	 * Get the SQL string
	 *
	 * @param string $tableName
	 * @param array &$params
	 * @param string $returnFields
	 * @param string $paramSubstName If ended with "_" the program subst by argname;
	 * @return string
	 */
	public function getSql($tableName, &$params, $returnFields = "*")
	{
		$params = array();

		$sql = "select :returnFields from :tableName ";
		$sqlFilter = $this->getFilter(IteratorFilter::SQL, $params);
		if ($sqlFilter != "")
		{
			$sql .= " where :sqlFilter ";
		}

		$sql = \ByJG\AnyDataset\Database\SQLHelper::createSafeSQL($sql, array(
			":returnFields" => $returnFields,
			":tableName" => $tableName,
			":sqlFilter" => $sqlFilter
		));

		return $sql;
	}

	/**
	 *
	 * @param $array
	 * @return unknown_type
	 */
	public function match($array)
	{
		$returnArray = array();

		foreach ($array as $sr)
		{
			if ($this->evalString($sr))
			{
				$returnArray[] = $sr;
			}
		}

		return $returnArray;
	}

	/**
	 * Return a filter in SQL or XPATH
	 *
	 * @param $type use XPATH or SQL
	 * @param $param
	 * @return unknown_type
	 */
	public function getFilter($type, &$param)
	{
		$filter = "";
		$param = array();

		$previousValue = null;
		foreach ($this->_filters as $value)
		{
			if ($value[0] == "(")
			{
				if ($previousValue != null)
				{
					$filter .= " or ( ";
				}
				else
				{
					$filter .= " ( ";
				}
			}
			elseif ($value[0] == ")")
			{
				$filter .= ")";
			}
			else
			{
				if ( ($previousValue != null) && ($previousValue[0] != "(") )
				{
					$filter .= $value[0];
				}
				if ($type == 1)
				{
					$filter .= $this->getStrXpathRelation($value[1], $value[2], $value[3]);
				}
				elseif ($type == 2)
				{
					$filter .= $this->getStrSqlRelation($value[1], $value[2], $value[3], $param);
				}
			}
			$previousValue = $value;
		}

		return $filter;
	}

	/**
	*@param string $name - Field name
	*@param Relation $relation - Relation enum
	*@param string $value - Field string value
	*@return string - Xpath String
	*@desc Private method to get a Xpath string to a single string comparison
	*/
	private function getStrXpathRelation($name, $relation, $value)
	{
		$str = is_numeric($value)?"":"'";
		$field = "field[@name='".$name."'] ";
		$value = " $str$value$str ";

		$result = "";
		switch ($relation)
		{
			case Relation::Equal:
			{
				$result = $field . "=" . $value;
				break;
			}
			case Relation::GreaterThan:
			{
				$result = $field . ">" . $value;
				break;
			}
			case Relation::LessThan:
			{
				$result = $field . "<" . $value;
				break;
			}
			case Relation::GreaterOrEqualThan:
			{
				$result = $field . ">=" . $value;
				break;
			}
			case Relation::LessOrEqualThan:
			{
				$result = $field . "<=" . $value;
				break;
			}
			case Relation::NotEqual:
			{
				$result = $field . "!=" . $value;
				break;
			}
			case Relation::StartsWith:
			{
				$result = " starts-with($field, $value) ";
				break;
			}
			case Relation::Contains:
			{
				$result = " contains($field, $value) ";
				break;
			}
		}
		return $result;
	}

	/**
	 *
	 * @param $name
	 * @param $relation
	 * @param $value
	 * @param $param
	 * @return unknown_type
	 */
	private function getStrSqlRelation($name, $relation, $value, &$param)
	{
		//$str = is_numeric($value)?"":"'";
		$value = trim($value);
		$paramName = $name;
		$i = 0;
		while (array_key_exists($paramName, $param))
		{
			$paramName = $name . ($i++);
		}

		$param[$paramName] = $value;

		$result = "";
		$field = " $name ";
		$valueparam = " [[" . $paramName . "]] ";
		switch ($relation)
		{
			case Relation::Equal:
			{
				$result = $field . "=" . $valueparam;
				break;
			}
			case Relation::GreaterThan:
			{
				$result = $field . ">" . $valueparam;
				break;
			}
			case Relation::LessThan:
			{
				$result = $field . "<" . $valueparam;
				break;
			}
			case Relation::GreaterOrEqualThan:
			{
				$result = $field . ">=" . $valueparam;
				break;
			}
			case Relation::LessOrEqualThan:
			{
				$result = $field . "<=" . $valueparam;
				break;
			}
			case Relation::NotEqual:
			{
				$result = $field . "!=" . $valueparam;
				break;
			}
			case Relation::StartsWith:
			{
				$param[$paramName] = $value . "%";
				$result = $field . " like " . $valueparam;
				break;
			}
			case Relation::Contains:
			{
				$param[$paramName] = "%" . $value . "%";
				$result = $field . " like " . $valueparam;
				break;
			}
		}

		return $result;
	}


	/**
	 *
	 * @param $array
	 * @return unknown_type
	 */
	private function evalString($array)
	{
		$result = array();
		$finalResult = false;
		$pos = 0;

		$result[0] = true;

		foreach ($this->_filters as $filter)
		{
			if ( ($filter[0] == ")") || ($filter[0] == " or "))
			{
				$finalResult |= $result[$pos];
				$result[++$pos] = true;
			}

			$name = $filter[1];
			$relation = $filter[2];
			$value = $filter[3];

			$field = $array->getField($name);

			if (!is_array($field)) $field = array($field);

			foreach ($field as $valueparam)
			{
				switch ($relation)
				{
					case Relation::Equal:
					{
						$result[$pos] &= ($valueparam == $value);
						break;
					}
					case Relation::GreaterThan:
					{
						$result[$pos] &= ($valueparam > $value);
						break;
					}
					case Relation::LessThan:
					{
						$result[$pos] &= ($valueparam < $value);
						break;
					}
					case Relation::GreaterOrEqualThan:
					{
						$result[$pos] &= ($valueparam >= $value);
						break;
					}
					case Relation::LessOrEqualThan:
					{
						$result[$pos] &= ($valueparam <= $value);
						break;
					}
					case Relation::NotEqual:
					{
						$result[$pos] &= ($valueparam != $value);
						break;
					}
					case Relation::StartsWith:
					{
						$result[$pos] &= (strpos($valueparam, $value) === 0);
						break;
					}
					case Relation::Contains:
					{
						$result[$pos] &= (strpos($valueparam, $value) !== false);
						break;
					}
				}
			}
		}

		$finalResult |= $result[$pos];

		return $finalResult;
	}

	/**
	*@param string $name - Field name
	*@param Relation $relation - Relation enum
	*@param string $value - Field string value
	*@return void
	*@desc Add a single string comparison to filter.
	*/
	public function addRelation($name, $relation, $value)
	{
		$this->_filters[] = array(" and ", $name, $relation, $value);
	}

	/**
	*@param string $name - Field name
	*@param Relation $relation - Relation enum
	*@param string $value - Field string value
	*@return void
	*@desc Add a single string comparison to filter. This comparison use the OR operator.
	*/
	public function addRelationOr($name, $relation, $value)
	{
		$this->_filters[] = array(" or ", $name, $relation, $value);
	}

	/**
	 * Add a "("
	 *
	 */
	public function startGroup()
	{
		$this->_filters[] = array("(", "", "", "");
	}

	/**
	 * Add a ")"
	 *
	 */
	public function endGroup()
	{
		$this->_filters[] = array(")", "", "", "");
	}
}
?>
