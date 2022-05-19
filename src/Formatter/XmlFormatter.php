<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\Util\XmlUtil;

class XmlFormatter extends BaseFormatter
{
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
     * Returns the AnyDataset XmlDocument representive object
     *
     * @return \DOMDocument XmlDocument object
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    public function raw()
    {
        if ($this->object instanceof AnyDataset) {
            return $this->anydatasetXml($this->object->getIterator()->toArray());
        }
        return $this->rowXml($this->object->toArray());
    }


    public function toText()
    {
        return $this->raw()->saveXML();
    }
}