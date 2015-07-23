<?php

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