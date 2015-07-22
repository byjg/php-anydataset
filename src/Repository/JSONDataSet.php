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

use ByJG\AnyDataset\Exception\DatasetException;

class JSONDataSet
{
	/**
	 * @var object
	 */
	private $_jsonObject;

	/**
	 *
	 * @param string $json
	 */
	public function __construct($json)
	{
		$this->_jsonObject = json_decode($json, true);

		$lastError = json_last_error();
		$lastErrorDesc = "";
		switch ($lastError)
		{
			case JSON_ERROR_NONE:
				$lastErrorDesc = 'No errors';
				break;
			case JSON_ERROR_DEPTH:
				$lastErrorDesc = 'Maximum stack depth exceeded';
				break;
			case JSON_ERROR_STATE_MISMATCH:
				$lastErrorDesc = 'Underflow or the modes mismatch';
				break;
			case JSON_ERROR_CTRL_CHAR:
				$lastErrorDesc = 'Unexpected control character found';
				break;
			case JSON_ERROR_SYNTAX:
				$lastErrorDesc = 'Syntax error, malformed JSON';
				break;
			case JSON_ERROR_UTF8:
				$lastErrorDesc = 'Malformed UTF-8 characters, possibly incorrectly encoded';
				break;
			default:
				$lastErrorDesc = 'Unknown error';
				break;
		}

		if ($lastError != JSON_ERROR_NONE)
		{
			throw new DatasetException("Invalid JSON string: " . $lastErrorDesc);
		}
	}

	/**
	*@access public
	*@param string $sql
	*@param array $array
	*@return DBIterator
	*/
	public function getIterator($path = "", $throwErr = false)
	{
		$it = new JSONIterator($this->_jsonObject, $path, $throwErr);
		return $it;
	}

}
?>