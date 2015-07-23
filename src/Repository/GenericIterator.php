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

use ByJG\AnyDataset\Exception\NotImplementedException;
use Iterator;

/**
 * @package xmlnuke
 */
abstract class GenericIterator implements IIterator, Iterator
{
	public function hasNext()
	{
		throw new NotImplementedException("Implement this method");
	}

	public function moveNext()
	{
		throw new NotImplementedException("Implement this method");
	}

	public function Count()
	{
		throw new NotImplementedException("Implement this method");
	}

	function key()
	{
		throw new NotImplementedException("Implement this method");
	}

    public function toArray()
    {
        $retArray = [];

        while ($this->hasNext())
        {
            $singleRow = $this->moveNext();
            $retArray[] = $singleRow->toArray();
        }
    }

	/* ------------------------------------- */
	/* PHP 5 Specific functions for Iterator */
	/* ------------------------------------- */

	/**
	 * @return SingleRow
	 */
 	function current()
 	{
 		return $this->moveNext();
  	}

	function rewind ()
	{
		// There is no necessary in XMLNuke.
	}

 	function next ()
	{
		// There is no necessary in XMLNuke.
	}

	function valid()
	{
		return $this->hasNext();
	}

}