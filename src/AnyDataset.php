<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Exception\DatabaseException;
use ByJG\AnyDataset\Core\Formatter\XmlFormatter;
use ByJG\Util\XmlUtil;
use InvalidArgumentException;

/**
 * AnyDataset is a simple way to store data using only XML file.
 * Your structure is hierarquical and each "row" contains "fields" but these structure can vary for each row.
 * Anydataset files have extension ".anydata.xml" and have many classes to put and get data into anydataset xml file.
 * Anydataset class just read and write files. To search elements you need use AnyIterator
 * and IteratorFilter. Each row have a class Row.

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

 * How to use:
 * <code>
 * $any = new AnyDataset();
 * </code>

 *
*@see Row
 * @see AnyIterator
 * @see IteratorFilter

 */
class AnyDataset
{

    /**
     * Internal structure represent the current Row
     *
     * @var Row[]
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
    private $filename;

    /**
     * @param string $filename
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    public function __construct($filename = null)
    {
        $this->collection = array();
        $this->currentRow = -1;

        $this->filename = null;
        $this->defineSavePath($filename, function () {
            if (!is_null($this->filename)) {
                $this->createFrom($this->filename);
            }
        });
    }

    public function getFilename()
    {
        return $this->filename;
    }

    private function defineSavePath($file, $closure)
    {
        if (!is_null($file)) {
            if (!is_string($file)) {
                throw new InvalidArgumentException('I expected a string as a file name');
            }

            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (empty($ext) && substr($file, 0, 6) !== "php://") {
                $file .= '.anydata.xml';
            }
            $this->filename = $file;
        }

        $closure();
    }

    /**
     * Private method used to read and populate anydataset class from specified file
     *
     * @param string $filepath Path and Filename to be read
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    private function createFrom($filepath)
    {
        if (file_exists($filepath)) {
            $anyDataSet = XmlUtil::createXmlDocumentFromFile($filepath);
            $this->collection = array();

            $rows = $anyDataSet->getElementsByTagName("row");
            foreach ($rows as $row) {
                $sr = new Row();
                $fields = $row->getElementsByTagName("field");
                foreach ($fields as $field) {
                    $attr = $field->attributes->getNamedItem("name");
                    if (is_null($attr)) {
                        throw new InvalidArgumentException('Malformed anydataset file ' . basename($filepath));
                    }

                    $sr->addField($attr->nodeValue, $field->nodeValue);
                }
                $sr->acceptChanges();
                $this->collection[] = $sr;
            }
            $this->currentRow = count($this->collection) - 1;
        }
    }

    /**
     * Returns the AnyDataset XML representative structure.
     *
     * @return string XML String
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    public function xml()
    {
        return (new XmlFormatter($this->getIterator()))->toText();
    }

    /**
     * @param string $filename
     * @throws DatabaseException
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    public function save($filename = null)
    {
        $this->defineSavePath($filename, function () {
            if (is_null($this->filename)) {
                throw new DatabaseException("No such file path to save anydataset");
            }

            (new XmlFormatter($this->getIterator()))->saveToFile($this->filename);
        });
    }

    /**
     * Append one row to AnyDataset.
     *
     * @param array|Row|object $singleRow
     * @return void
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function appendRow($singleRow = null)
    {
        if (!is_null($singleRow)) {
            if ($singleRow instanceof Row) {
                $this->collection[] = $singleRow;
                $singleRow->acceptChanges();
            } elseif (is_array($singleRow)) {
                $this->collection[] = new Row($singleRow);
            } else {
                throw new InvalidArgumentException("You must pass an array or a Row object");
            }
        } else {
            $singleRow = new Row();
            $this->collection[] = $singleRow;
            $singleRow->acceptChanges();
        }
        $this->currentRow = count($this->collection) - 1;
    }

    /**
     * Enter description here...
     *
     * @param GenericIterator $iterator
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function import($iterator)
    {
        foreach ($iterator as $singleRow) {
            $this->appendRow($singleRow);
        }
    }

    /**
     * Insert one row before specified position.
     *
     * @param int $rowNumber
     * @param mixed $row
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function insertRowBefore($rowNumber, $row = null)
    {
        if ($rowNumber > count($this->collection)) {
            $this->appendRow($row);
        } else {
            $singleRow = $row;
            if (!($row instanceof Row)) {
                $singleRow = new Row($row);
            }
            array_splice($this->collection, $rowNumber, 0, '');
            $this->collection[$rowNumber] = $singleRow;
        }
    }

    /**
     *
     * @param mixed $row
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function removeRow($row = null)
    {
        if (is_null($row)) {
            $row = $this->currentRow;
        }
        if ($row instanceof Row) {
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
     *
     * @param string $name - Field name
     * @param string $value - Field value
     * @return void
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
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
     * Undocumented function
     *
     * @param string $fieldName
     * @param IteratorFilter $itf
     * @return array
     */
    public function getArray($fieldName, $itf = null)
    {
        $iterator = $this->getIterator($itf);
        $result = array();
        while ($iterator->hasNext()) {
            $singleRow = $iterator->moveNext();
            $result[] = $singleRow->get($fieldName);
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

    /**
     * @param Row[] $seq
     * @param $field
     * @return array
     */
    protected function quickSortExec($seq, $field)
    {
        if (!count($seq)) {
            return $seq;
        }

        $key = $seq[0];
        $left = $right = array();

        $cntSeq = count($seq);
        for ($i = 1; $i < $cntSeq; $i ++) {
            if ($seq[$i]->get($field) <= $key->get($field)) {
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
}
