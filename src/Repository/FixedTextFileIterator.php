<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes
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
	*@return IIterator
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
	public function Count()
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
				return new SingleRow();

			$fields = array();
			$this->processBuffer($buffer, $this->_fields[$this->_curDefinition], $fields);

			if ($fields == null)
				throw new IteratorException("Text file definition is empty.");

			$sr = new SingleRow();
			$sr->AddField("_definition", $this->_curDefinition);

			foreach($fields as $key=>$value)
			{
				$sr->AddField(strtolower($key), $value);
				//Debug::PrintValue(strtolower($this->_fields[$i]), $cols[$i]);
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
		for($i=0;$i< count($definition); $i++)
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
					$this->processBuffer($buffer, $fieldDef->subTypes[$key], $fields);
			}
		}

		if ($fields == null)
		{
			if ($this->_curDefinition+1 <= count($this->_fields))
				$this->processBuffer($buffer, $this->_fields[++$this->_curDefinition], $fields);
		}
	}

 	function key()
 	{
 		return $this->_current;
 	}

}
?>
