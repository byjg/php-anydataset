<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\IteratorException;

class FixedTextFileIterator extends GenericIterator
{
	protected $_fields;

	protected $_handle;

	protected $_current = 0;

	protected $_curDefinition = 0;

	/**
	*@access public
	*@return IteratorInterface
	*/
	public function __construct($handle, $fields)
	{
		$this->_fields = $fields;
		$this->_handle = $handle;
		$this->_curDefinition = 0;
		$this->_current = 0;
	}

	/**
	*@access public
	*@return int
	*/
	public function count()
	{
		return -1;
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		if (!$this->_handle)
		{
			return false;
		}
		else
		{
			if (feof($this->_handle))
			{
				fclose($this->_handle);
				return false;
			}
			else
			{
				return true;
			}
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if ($this->hasNext())
		{
			$buffer = fgets($this->_handle, 4096);

			if ($buffer == "")
			{
				return new SingleRow();
			}

			$fields = array();
			$this->processBuffer($buffer, $this->_fields[$this->_curDefinition], $fields);

			if (is_null($fields))
			{
				throw new IteratorException("Text file definition is empty.");
			}

			$sr = new SingleRow();
			$sr->addField("_definition", $this->_curDefinition);

			foreach($fields as $key=>$value)
			{
				$sr->addField(strtolower($key), $value);
			}

			$this->_current++;
			return 	$sr;
		}
		else
		{
			if ($this->_handle)
			{
				fclose($this->_handle);
			}
			return null;
		}
	}

	protected function processBuffer($buffer, $definition, &$fields)
	{
		$cntDef = count($definition);
		for($i=0;$i< $cntDef; $i++)
		{
			$fieldDef = $definition[$i];

			$fields[$fieldDef->fieldName] = substr($buffer, $fieldDef->startPos - 1, $fieldDef->endPos - $fieldDef->startPos + 1);
			if (($fieldDef->requiredValue != "") && (!preg_match("/" . $fieldDef->requiredValue . "/", $fields[$fieldDef->fieldName])))
			{
				$fields = null;
				break;
			}
			elseif (is_array($fieldDef->subTypes))
			{
				$key = $fields[$fieldDef->fieldName];
				if (is_array($fieldDef->subTypes[$key]))
				{
					$this->processBuffer($buffer, $fieldDef->subTypes[$key], $fields);
				}
			}
		}

		if (is_null($fields))
		{
			if ($this->_curDefinition + 1 <= count($this->_fields))
			{
				$this->processBuffer($buffer, $this->_fields[++$this->_curDefinition], $fields);
			}
		}
	}

 	function key()
 	{
 		return $this->_current;
 	}

}

