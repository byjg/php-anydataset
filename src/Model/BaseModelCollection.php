<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
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
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Exception\IteratorException;
use ByJG\AnyDataset\Repository\GenericIterator;
use ByJG\AnyDataset\Repository\IIterator;
use ByJG\AnyDataset\Repository\SingleRow;
use InvalidArgumentException;

/**
 * @package xmlnuke
 */
abstract class BaseModelCollection extends GenericIterator
{

	protected $_items = array();
	private $_curRow = 0; //int
	protected $_type = null;

	// <editor-fold desc="Implementation of IIterator interface based on GenericIterator">

	public function hasNext()
	{
		return ($this->_curRow < $this->Count());
	}

	public function moveNext()
	{
		if ($this->hasNext())
		{
			$singleRow = new SingleRow($this->_items[$this->_curRow++]);
			return $singleRow;
		}
		else
		{
			throw new IteratorException("Cannot move to next item");
		}
	}

	public function Count()
	{
		return count($this->_items);
	}

	function key()
	{
		return $this->_curRow;
	}

	// </editor-fold>

	public function __construct($it = null, $type = 'BaseModel')
	{
		if ($it != null)
		{
			if (!($it instanceof IIterator))
			{
				throw new InvalidArgumentException("You have to pass an IIterator class");
			}

			// Check if the object is an instance of BaseModel
			$tObj = new $type();
			if (!($tObj instanceof BaseModel))
			{
				throw new InvalidArgumentException("You have to pass a BaseModel descendant as type");
			}

			// Save the type to be used in the future
			$this->_type = $type;

			foreach ($it as $singleRow)
			{
				$item = new $type($singleRow);
				$this->addItem($item);
			}
		}
	}

	// <editor-fold desc="Implementation of BaseModelCollection methods">
	public function getItems()
	{
		return $this->_items;
	}

	public function addItem($item)
	{
		$this->_items[] = $item;
	}

	public function getItem($index)
	{
		if (array_key_exists($index, $this->_items))
		{
			return $this->_items[$index];
		}
		else
		{
			throw new IteratorException('The index is out of range.');
		}
	}

	// </editor-fold>
}

