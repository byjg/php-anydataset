<?php

namespace ByJG\AnyDataset\Database;

use ByJG\AnyDataset\Enum\Relation;
use ByJG\AnyDataset\Enum\SQLFieldType;
use ByJG\AnyDataset\Enum\SQLType;
use ByJG\AnyDataset\Repository\DBDataSet;
use ByJG\AnyDataset\Repository\SingleRow;
use DateTime;
use Exception;

class SQLHelper
{
	/**
	 * @var DBDataSet
	 */
	protected $_db;

	protected $_fieldDeliLeft = " ";
	protected $_fieldDeliRight = " ";

	/**
	 *
	 * @param DBDataSet $db
	 */
	public function __construct(DBDataSet $db)
	{
		$this->_db = $db;
	}

	/**
	 * Generate and Execute UPDATE and INSERTS
	 *
	 * @param DBDataSet $db
	 * @param string $table
	 * @param array $fields
	 * @param SQLType $type
	 * @param string $filter
	 * @return string
	 */
	public function generateSQL($table, $fields, &$param, $type = SQLType::SQL_INSERT, $filter = "", $decimalpoint = ".")
	{
		if ($fields instanceof SingleRow)
		{
			return $this->generateSQL($table, $fields->toArray(), $param, $type, $filter, $decimalpoint);
		}

		if ((is_null($param)) || (!is_array($param)))
		{
			$param = array();
		}

		if ($type == SQLType::SQL_UPDATE)
		{
			$sql = "";
			foreach ($fields as $fieldname=>$fieldvalue)
			{
				if ($sql != "")
				{
					$sql .= ", ";
				}
				$sql .= " " . $this->_fieldDeliLeft . $fieldname . $this->_fieldDeliRight . " = " . $this->getValue($fieldname, $fieldvalue, $param, $decimalpoint) . " ";
			}
			$sql = "update $table set $sql where $filter ";
		}
		elseif ($type == SQLType::SQL_INSERT)
		{
			$sql = "";
			$campos = "";
			$valores = "";
			foreach ($fields as $fieldname => $fieldvalue)
			{
				if ($campos != "")
				{
					$campos .= ", ";
					$valores .= ", ";
				}
				$campos .= $this->_fieldDeliLeft . $fieldname . $this->_fieldDeliRight;
				$valores .= $this->getValue($fieldname, $fieldvalue, $param, $decimalpoint);
			}
			$sql = "insert into $table ($campos) values ($valores)";
		}
		elseif ($type == SQLType::SQL_DELETE)
		{
			if ($filter == "")
			{
				throw new Exception("I can't generate delete statements without filter");
			}
			$sql = "delete from $table where $filter";
		}
		return $sql;
	}

	/**
	 * Generic Function
	 *
	 * @param unknown_type $valores
	 * @return unknown
	 */
	protected function getValue($name, $valores, &$param, $decimalpoint)
	{
		$paramName = "[[" . $name . "]]";
		if (!is_array($valores))
		{
			$valores = array(SQLFieldType::TEXT, $valores);
		}

		if ($valores[0]== SQLFieldType::BOOLEAN)
		{
			if ($valores[1]=="1")
			{
				$param[$name] = 'S';
			}
			else
			{
				$param[$name] = 'N';
			}
			return $paramName;
		}
		elseif (strlen($valores[1])==0) // Zero is Empty!?!?!?!?
		{
			return "null";
		}
		elseif ($valores[0]==SQLFieldType::TEXT)
		{
			$param[$name] = trim($valores[1]);
			return $paramName;
		}
		elseif ($valores[0]==SQLFieldType::DATE)
		{
            $date = ($valores[1] instanceof DateTime ? $valores[1]->format(DBBaseFunctions::YMDH): $valores[1]);
			$param[$name] = $date;
			if ( ($this->_db->getDbType() == 'oci8') || ( ($this->_db->getDbType() == 'dsn') && (strpos($this->_db->getDbConnectionString(), "oci8"))) )
			{
				return "TO_DATE($paramName, 'YYYY-MM-DD')";
			}
			else
			{
				return $paramName;
			}
		}
		elseif ($valores[0]==SQLFieldType::NUMBER)
		{
			$search = ($decimalpoint == ".") ? "," : ".";
			$valores[1] = trim(str_replace($search, $decimalpoint, $valores[1]));
			$param[$name] = $valores[1];
			return $paramName;
		}
		else
		{
			return $valores[1];
		}
	}

	/**
	 * Used to create a FILTER in a SQL string.
	 *
	 * @param string $fieldName
	 * @param Relation $relation
	 * @param array() $value
	 * @param &string $sql (Full SQL)
	 * @param &string $param
	 */
	public function getWhereClause($fieldName, $relation, $value, &$sql, &$param)
	{
		if (strlen($sql)>4)
		{
			$sql .= ' and ';
		}
		$sql = " $fieldName " . $relation . " " . $this->getValue($fieldName, $value, $param, $decimalpoint);
	}


	public function setFieldDelimeters($left, $right)
	{
		$this->_fieldDeliLeft = $left;
		$this->_fieldDeliRight = $right;
	}

	public static function createSafeSQL($sql, $list)
	{
		foreach($list as $key=>$value)
		{
			$value = str_replace(["'", ';'], ["", ''], $value);
			$sql = str_replace($key, $value, $sql);
		}
		return $sql;
	}
}
