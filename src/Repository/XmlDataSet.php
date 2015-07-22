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
use ByJG\Util\XmlUtil;
use DOMDocument;
use InvalidArgumentException;

class XmlDataSet
{
	/**
	 * String
	 *
	 * @var string
	 */
	private $_rowNode = null;
	/**
	 * Enter description here...
	 *
	 * @var string[]
	 */
	private $_colNodes = null;

	/**
	 * @var DOMDocument
	 */
	private $_domDocument;

	/**
	 *
	 * @var type
	 */
	protected $_registerNS;

    /**
     *
     * @param DOMDocument $xml
	 * @param string $rowNode
	 * @param string[] $colNode
     * @param array $registerNS
     * @throws DatasetException
     * @throws InvalidArgumentException
     */
	public function __construct($xml, $rowNode, $colNode, $registerNS = null)
	{
		if (!is_array($colNode))
		{
			throw new DatasetException("XmlDataSet constructor: Column nodes must be an array.");
		}

		if ($xml instanceof DOMDocument)
		{
			$this->_domDocument = $xml;
		}
		else
		{
			$this->_domDocument = XmlUtil::CreateXmlDocumentFromStr($xml);
		}

		if (is_null($registerNS))
		{
			$registerNS = array();
		}

		if (!is_array($registerNS))
		{
			throw new InvalidArgumentException('The parameter $registerNS must be an associative array');
		}

		$this->_registerNS = $registerNS;
		$this->_rowNode = $rowNode;
		$this->_colNodes = $colNode;
	}

	/**
	*@access public
	*@param string $sql
	*@param array $array
	*@return DBIterator
	*/
	public function getIterator()
	{
		$it = new XmlIterator(XmlUtil::selectNodes($this->_domDocument->documentElement, $this->_rowNode, $this->_registerNS), $this->_colNodes, $this->_registerNS);
		return $it;
	}

}
?>