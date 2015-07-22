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

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Model\Object;

/**
 * @package xmlnuke
 */
abstract class BaseModel extends Object
{

	protected $_propertyPattern = array('/([^A-Za-z0-9])/', '');

	/**
	 * Construct a model and optionally can set (bind) your properties base and the attribute matching from SingleRow, IIterator
	 * @param Object $object
	 * @return void
	 */
	public function __construct($object=null)
	{
		if (is_object($object) || is_array($object))
		{
			$this->bind($object);
		}
	}


	/**
	 * PropertyPattern can change how the protected property will be change to your related getter or setter.
	 *
	 * For example:
	 * protected $some_thing
	 *
	 * without propertypattern have to be:
	 * public function getSome_thing()
	 *
	 * but with the default property pattern ([^A-Za-z]) can be (excluing all char are not alpha)
	 * public function getSomething()
	 *
	 * @param $pattern
	 * @param $replace
	 * @return void
	 */
	public function setPropertyPattern($pattern, $replace)
	{
		if ($pattern == null) {
            $this->_propertyPattern = null;
        } else {
            $this->_propertyPattern = array(($pattern[0] != "/" ? "/" : "") . $pattern . ($pattern[strlen($pattern) - 1] != "/" ? "/" : ""), $replace);
        }
    }
	public function getPropertyPattern()
	{
		return $this->_propertyPattern;
	}

}
