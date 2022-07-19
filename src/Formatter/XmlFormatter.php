<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\Util\XmlUtil;
use DOMDocument;
use DOMNode;

class XmlFormatter extends BaseFormatter
{
    /**
     * Return a DOMNode representing AnyDataset
     *
     * @param array $collection
     * @return DOMNode
     */
    protected function anydatasetXml($collection)
    {
        $anyDataSet = XmlUtil::createXmlDocumentFromStr("<anydataset></anydataset>");
        $nodeRoot = $anyDataSet->getElementsByTagName("anydataset")->item(0);
        foreach ($collection as $sr) {
            $row = $this->rowXml($sr);
            $nodeRow = $row->getElementsByTagName("row")->item(0);
            $newRow = XmlUtil::createChild($nodeRoot, "row");
            XmlUtil::addNodeFromNode($newRow, $nodeRow);
        }

        return $anyDataSet;
    }

    /**
     * @param array $row
     * @return DOMDocument
     */
    protected function rowXml($row)
    {
        $node = XmlUtil::createXmlDocumentFromStr("<row></row>");
        $root = $node->getElementsByTagName("row")->item(0);
        foreach ($row as $key => $value) {
            if (!is_array($value)) {
                $field = XmlUtil::createChild($root, "field", $value);
                XmlUtil::addAttribute($field, "name", $key);
            } else {
                foreach ($value as $valueItem) {
                    $field = XmlUtil::createChild($root, "field", $valueItem);
                    XmlUtil::addAttribute($field, "name", $key);
                }
            }
        }
        return $node;
    }


    /**
     * @inheritDoc
     */
    public function raw()
    {
        if ($this->object instanceof GenericIterator) {
            return $this->anydatasetXml($this->object->toArray());
        }
        return $this->rowXml($this->object->toArray());
    }

    /**
     * @inheritDoc
     */
    public function toText()
    {
        return $this->raw()->saveXML();
    }
}