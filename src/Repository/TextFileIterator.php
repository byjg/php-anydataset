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

class TextFileIterator extends GenericIterator
{
	protected $_fields;

	protected $_fieldexpression;

	protected $_handle;

	protected $_current = 0;

	protected $_currentBuffer = "";

	/**
	*@access public
	*@return IIterator
	*/
	public function __construct($handle, $fields, $fieldexpression)
	{
		$this->_fields = $fields;
		$this->_fieldexpression = $fieldexpression;
		$this->_handle = $handle;

		$this->readNextLine();
	}

	protected function readNextLine()
	{
		if ($this->hasNext())
		{
			$buffer = fgets($this->_handle, 4096);
			$this->_currentBuffer = false;

			if (($buffer !== false) && (trim($buffer) != ""))
			{
				$this->_current++;
				$this->_currentBuffer = $buffer;
			}
			else
				$this->readNextLine();
		}
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
		if ($this->_currentBuffer !== false)
		{
			return true;
		}
		elseif (!$this->_handle)
		{
			return false;
		}
		else
		{
			if (feof($this->_handle))
			{
				fclose($this->_handle);
				$this->_handle = null;
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
			$cols = preg_split($this->_fieldexpression, $this->_currentBuffer, -1, PREG_SPLIT_DELIM_CAPTURE);

			$sr = new SingleRow();

			for($i=0;($i<sizeof($this->_fields)) && ($i<sizeof($cols)); $i++)
			{
				$column = $cols[$i];

				if (($i>=sizeof($this->_fields)-1) || ($i>=sizeof($cols)-1))
					$column = preg_replace("/(\r?\n?)$/", "", $column);

				$sr->AddField(strtolower($this->_fields[$i]), $column);
				//Debug::PrintValue(strtolower($this->_fields[$i]), $cols[$i]);
			}

			$this->readNextLine();
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

 	function key()
 	{
 		return $this->_current;
 	}

}
?>
