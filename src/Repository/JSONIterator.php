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

use InvalidArgumentException;
use ByJG\AnyDataset\Exception\IteratorException;

class JSONIterator extends GenericIterator
{
	/**
	 * @var object
	 */
	private $_jsonObject;

	/**
	 * Enter description here...
	 *
	 * @var int
	 */
	private $_current = 0;

	public function __construct($jsonObject, $path = "", $throwErr = false)
	{
		if (!is_array($jsonObject))
		{
			throw new InvalidArgumentException("Invalid JSON object");
		}

		if ($path != "")
		{
			if ($path[0] == "/")
				$path = substr ($path, 1);

			$pathAr = explode("/", $path);

			$newjsonObject = $jsonObject;

			foreach($pathAr as $key)
			{
				if (array_key_exists($key, $newjsonObject))
				{
					$newjsonObject = $newjsonObject[$key];
				}
				elseif ($throwErr)
				{
					throw new IteratorException("Invalid path '$path' in JSON Object");
				}
				else
				{
					$newjsonObject = array();
					break;
				}
			}
			$this->_jsonObject = $newjsonObject;
		}
		else
			$this->_jsonObject = $jsonObject;

		$this->_current = 0;
	}

	public function Count()
	{
		return (count($this->_jsonObject));
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		if ($this->_current < $this->Count())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
		}

		$sr = new SingleRow($this->_jsonObject[$this->_current++]);

		return 	$sr;
	}

 	function key()
 	{
 		return $this->_current;
 	}

}
?>
