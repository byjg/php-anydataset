<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\DatasetException;
use ByJG\Util\XmlUtil;
use DOMDocument;
use InvalidArgumentException;

class XmlDataset
{

    /**
     * String
     *
     * @var string
     */
    private $rowNode = null;

    /**
     * Enter description here...
     *
     * @var string[]
     */
    private $colNodes = null;

    /**
     * @var DOMDocument
     */
    private $domDocument;

    /**
     *
     * @var string
     */
    protected $registerNS;

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
        if (!is_array($colNode)) {
            throw new DatasetException("XmlDataset constructor: Column nodes must be an array.");
        }

        if ($xml instanceof DOMDocument) {
            $this->domDocument = $xml;
        } else {
            $this->domDocument = XmlUtil::createXmlDocumentFromStr($xml);
        }

        if (is_null($registerNS)) {
            $registerNS = array();
        }

        if (!is_array($registerNS)) {
            throw new InvalidArgumentException('The parameter $registerNS must be an associative array');
        }

        $this->registerNS = $registerNS;
        $this->rowNode = $rowNode;
        $this->colNodes = $colNode;
    }

    /**
     * @access public
     * @return GenericIterator
     */
    public function getIterator()
    {
        $iterator = new XmlIterator(
            XmlUtil::selectNodes(
                $this->domDocument->documentElement,
                $this->rowNode,
                $this->registerNS
            ),
            $this->colNodes,
            $this->registerNS
        );
        return $iterator;
    }
}
