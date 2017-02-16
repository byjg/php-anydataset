<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\DatabaseException;
use ByJG\AnyDataset\IteratorInterface;
use ByJG\Util\XmlUtil;
use ForceUTF8\Encoding;
use InvalidArgumentException;

/**
 * AnyDataset is a simple way to store data using only XML file.
 * Your structure is hierarquical and each "row" contains "fields" but these structure can vary for each row.
 * Anydataset files have extension ".anydata.xml" and have many classes to put and get data into anydataset xml file.
 * Anydataset class just read and write files. To search elements you need use AnyIterator
 * and IteratorFilter. Each row have a class SingleRow.
 *
 * XML Structure
 * <code>
 * <anydataset>
 *    <row>
 *        <field name="fieldname1">value of fieldname 1</field>
 *        <field name="fieldname2">value of fieldname 2</field>
 *        <field name="fieldname3">value of fieldname 3</field>
 *    </row>
 *    <row>
 *        <field name="fieldname1">value of fieldname 1</field>
 *        <field name="fieldname4">value of fieldname 4</field>
 *    </row>
 * </anydataset>
 * </code>
 *
 * How to use:
 * <code>
 * $any = new AnyDataset();
 * </code>
 *
 * @see SingleRow
 * @see AnyIterator
 * @see IteratorFilter
 *
 */
class AnyDataset
{

    /**
     * Internal structure represent the current SingleRow
     * @var SingleRow[]
     */
    private $collection;

    /**
     * Current node anydataset works
     * @var int
     */
    private $currentRow;

    /**
     * Path to anydataset file
     * @var string
     */
    private $path;

    /**
     *
     * @param string $file
     * @throws InvalidArgumentException
     */
    public function __construct($file = null)
    {
        $this->collection = array();
        $this->currentRow = -1;

        $this->path = null;
        if (!is_null($file)) {
            if (!is_string($file)) {
                throw new \InvalidArgumentException('I expected a string as a file name');
            }

            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (empty($ext)) {
                $file .= '.anydata.xml';
            }
            $this->path = $file;

            $this->createFrom($this->path);
        }
    }

    /**
     * Private method used to read and populate anydataset class from specified file
     * @param string $filepath Path and Filename to be read
     * @return null
     */
    private function createFrom($filepath)
    {
        if (file_exists($filepath)) {
            $anyDataSet = XmlUtil::createXmlDocumentFromFile($filepath);
            $this->collection = array();

            $rows = $anyDataSet->getElementsByTagName("row");
            foreach ($rows as $row) {
                $sr = new SingleRow();
                $fields = $row->getElementsByTagName("field");
                foreach ($fields as $field) {
                    $attr = $field->attributes->getNamedItem("name");
                    if (is_null($attr)) {
                        throw new \InvalidArgumentException('Malformed anydataset file ' . basename($filepath));
                    }

                    $sr->addField($attr->nodeValue, $field->nodeValue);
                }
                $sr->acceptChanges();
                $this->collection[] = $sr;
            }
            $this->currentRow = sizeof($this->collection) - 1;
        }
    }

    /**
     * Returns the AnyDataset XML representative structure.
     * @return string XML String
     */
    public function xml()
    {
        return $this->getDomObject()->saveXML();
    }

    /**
     * Returns the AnyDataset XmlDocument representive object
     * @return \DOMDocument XmlDocument object
     */
    public function getDomObject()
    {
        $anyDataSet = XmlUtil::createXmlDocumentFromStr("<anydataset/>");
        $nodeRoot = $anyDataSet->getElementsByTagName("anydataset")->item(0);
        foreach ($this->collection as $sr) {
            $row = $sr->getDomObject();
            $nodeRow = $row->getElementsByTagName("row")->item(0);
            $newRow = XmlUtil::createChild($nodeRoot, "row");
            XmlUtil::addNodeFromNode($newRow, $nodeRow);
        }

        return $anyDataSet;
    }

    /**
     *
     * @param string $file
     * @throws DatabaseException
     */
    public function save($file = null)
    {
        if (!is_null($file)) {
            if (!is_string($file)) {
                throw new InvalidArgumentException('Invalid file name');
            }

            $this->path = $file;
        }

        if (is_null($this->path)) {
            throw new DatabaseException("No such file path to save anydataset");
        }

        XmlUtil::saveXmlDocument($this->getDomObject(), $this->path);
    }

    /**
     * Append one row to AnyDataset.
     *
     * @param SingleRow $singleRow
     * @return void
     */
    public function appendRow($singleRow = null)
    {
        if (!is_null($singleRow)) {
            if ($singleRow instanceof SingleRow) {
                $this->collection[] = $singleRow;
                $singleRow->acceptChanges();
            } elseif (is_array($singleRow)) {
                $this->collection[] = new SingleRow($singleRow);
            } else {
                throw new InvalidArgumentException("You must pass an array or a SingleRow object");
            }
        } else {
            $singleRow = new SingleRow();
            $this->collection[] = $singleRow;
            $singleRow->acceptChanges();
        }
        $this->currentRow = count($this->collection) - 1;
    }

    /**
     * Enter description here...
     *
     * @param IteratorInterface $ititerator
     */
    public function import($ititerator)
    {
        foreach ($ititerator as $singleRow) {
            $this->appendRow($singleRow);
        }
    }

    /**
     * Insert one row before specified position.
     * @param int $rowNumber
     * @param mixed $row
     */
    public function insertRowBefore($rowNumber, $row = null)
    {
        if ($rowNumber > count($this->collection)) {
            $this->appendRow($row);
        } else {
            $singleRow = $row;
            if (!($row instanceof SingleRow)) {
                $singleRow = new SingleRow($row);
            }
            array_splice($this->collection, $rowNumber, 0, '');
            $this->collection[$rowNumber] = $singleRow;
        }
    }

    /**
     *
     * @param mixed $row
     * @return null
     */
    public function removeRow($row = null)
    {
        if (is_null($row)) {
            $row = $this->currentRow;
        }
        if ($row instanceof SingleRow) {
            $iPos = 0;
            foreach ($this->collection as $sr) {
                if ($sr->toArray() == $row->toArray()) {
                    $this->removeRow($iPos);
                    break;
                }
                $iPos++;
            }
            return;
        }

        if ($row == 0) {
            $this->collection = array_slice($this->collection, 1);
        } else {
            $this->collection = array_slice($this->collection, 0, $row) + array_slice($this->collection, $row);
        }
    }

    /**
     * Add a single string field to an existing row
     * @param string $name - Field name
     * @param string $value - Field value
     * @return void
     */
    public function addField($name, $value)
    {
        if ($this->currentRow < 0) {
            $this->appendRow();
        }
        $this->collection[$this->currentRow]->addField($name, $value);
    }

    /**
     * Get an Iterator filtered by an IteratorFilter
     * @param IteratorFilter $itf
     * @return GenericIterator
     */
    public function getIterator(IteratorFilter $itf = null)
    {
        if (is_null($itf)) {
            return new AnyIterator($this->collection);
        }

        return new AnyIterator($itf->match($this->collection));
    }

    /**
     * @desc
     * @param IteratorFilter $itf
     * @param string $fieldName
     * @return array
     */
    public function getArray($itf, $fieldName)
    {
        $iterator = $this->getIterator($itf);
        $result = array();
        while ($iterator->hasNext()) {
            $singleRow = $iterator->moveNext();
            $result [] = $singleRow->getField($fieldName);
        }
        return $result;
    }

    /**
     *
     * @param string $field
     * @return void
     */
    public function sort($field)
    {
        if (count($this->collection) == 0) {
            return;
        }

        $this->collection = $this->quickSortExec($this->collection, $field);

        return;
    }

    protected function quickSortExec($seq, $field)
    {
        if (!count($seq)) {
            return $seq;
        }

        $key = $seq[0];
        $left = $right = array();

        $cntSeq = count($seq);
        for ($i = 1; $i < $cntSeq; $i ++) {
            if ($seq[$i]->getField($field) <= $key->getField($field)) {
                $left[] = $seq[$i];
            } else {
                $right[] = $seq[$i];
            }
        }

        return array_merge(
            $this->quickSortExec($left, $field),
            [ $key ],
            $this->quickSortExec($right, $field)
        );
    }

    /**
     * @param $document
     * @return array|string
     */
    public static function fixUTF8($document)
    {
        return Encoding::fixUTF8(Encoding::removeBOM($document), Encoding::ICONV_TRANSLIT);
    }
}
