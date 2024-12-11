<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Exception\DatabaseException;
use ByJG\AnyDataset\Core\Formatter\XmlFormatter;
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\File;
use ByJG\XmlUtil\XmlDocument;
use ByJG\XmlUtil\XmlNode;
use Closure;
use DOMElement;
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
     * @var RowInterface[]
     */
    private array $collection;

    /**
     * Current node anydataset works
     * @var int
     */
    private int $currentRow;

    private ?File $file;

    /**
     * @param string|array|null $filename
     * @throws FileException
     * @throws XmlUtilException
     */
    public function __construct(string|array|null $filename = null)
    {
        $this->file = null;
        $this->currentRow = -1;
        $this->collection = [];
        if (is_array($filename)) {
            $this->collection = array_map(fn($row) => Row::factory($row), $filename);
            return;
        }

        $this->defineSavePath($filename, function () {
            if (!is_null($this->file)) {
                $this->createFromFile();
            }
        });
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->file->getFilename();
    }

    /**
     *
     * @param string|null $filename
     * @param mixed $closure
     * @return void
     * @throws FileException
     */
    private function defineSavePath(?string $filename, Closure $closure): void
    {
        if (!is_null($filename)) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (empty($ext) && !str_starts_with($filename, "php://")) {
                $filename .= '.anydata.xml';
            }
            $this->file = new File($filename, allowNotFound: true);
        }

        $closure();
    }

    /**
     * Private method used to read and populate anydataset class from specified file
     *
     * @return void
     * @throws XmlUtilException|FileException
     */
    private function createFromFile(): void
    {
        if (file_exists($this->getFilename())) {
            $anyDataSet = new XmlDocument($this->file);
            $this->collection = array();

            $rows = $anyDataSet->selectNodes("row");
            foreach ($rows as $row) {
                $sr = Row::factory();
                $fields = XmlNode::instance($row)->selectNodes("field");
                /** @var DOMElement $field */
                foreach ($fields as $field) {
                    if (!$field->hasAttribute("name")) {
                        throw new InvalidArgumentException('Malformed anydataset file ' . basename($this->getFilename()));
                    }
                    $sr->set($field->getAttribute("name"), $field->nodeValue);
                }
                $this->collection[] = $sr;
            }
            $this->currentRow = count($this->collection) - 1;
        }
    }

    /**
     * Returns the AnyDataset XML representative structure.
     *
     * @return string XML String
     */
    public function xml(): string
    {
        return (new XmlFormatter($this->getIterator()))->toText();
    }

    /**
     * @param string|null $filename
     * @return void
     * @throws DatabaseException
     * @throws FileException
     */
    public function save(?string $filename = null): void
    {
        $this->defineSavePath($filename, function () use ($filename){
            if (is_null($this->file)) {
                throw new DatabaseException("No such file path to save anydataset");
            }

            (new XmlFormatter($this->getIterator()))->saveToFile($this->file->getFilename());
        });
    }

    /**
     * Append one row to AnyDataset.
     *
     * @param array|object $singleRow
     * @return void
     */
    public function appendRow(array|object $singleRow = []): void
    {
        if (!($singleRow instanceof RowInterface)) {
            $singleRow = Row::factory($singleRow);
        }

        $this->collection[] = $singleRow;
        $this->currentRow = count($this->collection) - 1;
    }

    /**
     * Enter description here...
     *
     * @param GenericIterator $iterator
     * @return void
     */
    public function import(GenericIterator $iterator): void
    {
        foreach ($iterator as $singleRow) {
            $this->appendRow($singleRow);
        }
    }

    /**
     * Insert one row before specified position.
     *
     * @param int $rowNumber
     * @param array|object $row
     */
    public function insertRowBefore(int $rowNumber, array|object $row): void
    {
        if ($rowNumber > count($this->collection)) {
            $this->appendRow($row);
        } else {
            $singleRow = $row;
            if (!($row instanceof RowInterface)) {
                $singleRow = Row::factory($row);
            }

            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            array_splice($this->collection, $rowNumber, 0, '');
            /**
             * @psalm-suppress InvalidPropertyAssignmentValue
             */
            $this->collection[$rowNumber] = $singleRow;
        }
    }

    /**
     *
     * @param int|RowInterface|null $row
     * @return void
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function removeRow(int|RowInterface $row = null): void
    {
        if (is_null($row)) {
            $row = $this->currentRow;
        }
        if ($row instanceof RowInterface) {
            $iPos = 0;
            $rowArr = $row->toArray();
            foreach ($this->collection as $sr) {
                if ($sr->toArray() == $rowArr) {
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
     */
    public function addField(string $name, mixed $value): void
    {
        if ($this->currentRow < 0) {
            $this->appendRow();
        }
        $this->collection[$this->currentRow]->set($name, $value);
    }

    /**
     * Get an Iterator filtered by an IteratorFilter
     * @param IteratorFilter|null $itf
     * @return GenericIterator|AnyIterator
     */
    public function getIterator(IteratorFilter $itf = null): GenericIterator|AnyIterator
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
     * @param IteratorFilter|null $itf
     * @return array
     */
    public function getArray(string $fieldName, IteratorFilter $itf = null): array
    {
        $iterator = $this->getIterator($itf);
        $result = array();
        foreach ($iterator as $singleRow) {
            $result[] = $singleRow->get($fieldName);
        }
        return $result;
    }

    /**
     *
     * @param string $field
     * @return void
     */
    public function sort(string $field): void
    {
        if (count($this->collection) == 0) {
            return;
        }

        $this->collection = $this->quickSortExec($this->collection, $field);
    }

    /**
     * @param RowInterface[] $seq
     * @param string $field
     * @return array
     */
    protected function quickSortExec(array $seq, string $field): array
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
