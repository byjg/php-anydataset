<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\XmlUtil\XmlDocument;
use ByJG\XmlUtil\XmlNode;

class XmlFormatter extends BaseFormatter
{
    /**
     * Return a DOMNode representing AnyDataset
     *
     * @param array $collection
     * @return XmlNode
     */
    protected function anydatasetXml(array $collection): XmlNode
    {
        $anyDataSet = new XmlDocument("<anydataset></anydataset>");
        foreach ($collection as $sr) {
            $this->rowXml($sr, $anyDataSet);
        }

        return $anyDataSet;
    }

    /**
     * @param array $row
     * @return XmlNode
     */
    protected function rowXml(array $row, XmlDocument $parentDocument = null): XmlNode
    {
        if (!empty($parentDocument)) {
            $node = $parentDocument->appendChild('row');
        } else {
            $node = new XmlDocument("<row></row>");
        }
        foreach ($row as $key => $value) {
            foreach ((array)$value as $valueItem) {
                $field = $node->appendChild("field", $valueItem);
                $field->addAttribute("name", $key);
            }
        }
        return $node;
    }


    /**
     * @inheritDoc
     */
    public function raw(): mixed
    {
        if ($this->object instanceof GenericIterator) {
            return $this->anydatasetXml($this->object->toArray())->DOMDocument();
        }
        return $this->rowXml($this->object->toArray())->DOMNode();
    }

    /**
     * @inheritDoc
     */
    public function toText(): string|false
    {
        return $this->raw()->saveXML();
    }
}