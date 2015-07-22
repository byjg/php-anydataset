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

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Repository\GenericIterator;
use ByJG\AnyDataset\Repository\SingleRow;

/**
 * Iterator class is a structure used to navigate forward in a AnyDataset structure.
 * @package xmlnuke
 */
class AnyIterator extends GenericIterator
{

	/**
	*@desc Row Elements
	*@access private
	*@var array
	*/
	private $_list;

	/**
	*@access private
	*@var int
	*@desc Current row number
	*/
	private	$_curRow;//int

	/**
	*@access public
	*@param \DOMNodeList $list \DOMNodeList
	*@return void
	*@desc Iterator constructor
	*/
	public function __construct($list)
	{
		$this->_curRow = 0;
		$this->_list = $list;
	}

	/**
	*@access public
	*@return int
	*@desc How many elements have
	*/
	public function Count()
	{
		return sizeof($this->_list);
	}

	/**
	*@access public
	*@return bool - True if exist more rows, otherwise false
	*@desc Ask the Iterator is exists more rows. Use before moveNext method.
	*/
	public function hasNext()
	{
		return ($this->_curRow < $this->Count());
	}

	/**
	*@access public
	*@return SingleRow
	*@desc Return the next row.
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			return null;
		}
		else
		{
			return $this->_list[$this->_curRow++];
		}
	}

 	function key()
 	{
 		return $this->_curRow;
 	}

}
?>