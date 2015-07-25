<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\DatabaseException;
use ByJG\Util\XmlUtil;
use ForceUTF8\Encoding;
use InvalidArgumentException;

/**
 * AnyDataset is a simple way to store data using only XML file.
 * Your structure is hierarquical and each "row" contains "fields" but these structure can vary for each row.
 * Anydataset files have extension ".anydata.xml" and have many classes to put and get data into anydataset xml file.
 * Anydataset class just read and write files. To search elements you need use AnyIterator and IteratorFilter. Each row have a class SingleRow.
 *
 * XML Structure
 * <code>
 * <anydataset>
 *		<row>
 *			<field name="fieldname1">value of fieldname 1</field>
 *			<field name="fieldname2">value of fieldname 2</field>
 *			<field name="fieldname3">value of fieldname 3</field>
 *		</row>
 *		<row>
 *			<field name="fieldname1">value of fieldname 1</field>
 *			<field name="fieldname4">value of fieldname 4</field>
 *		</row>
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
	private $_collection;

	/**
	 * Current node anydataset works
	 * @var int
	 */
	private $_currentRow;

	/**
	 * Path to anydataset file
	 * @var string
	 */
	private $_path;

    /**
     *
     * @param type $file
     * @throws InvalidArgumentException
     */
	public function __construct($file = null)
	{
		$this->_collection = array();
		$this->_currentRow = -1;

		$this->_path = null;
		if (!is_null($file))
		{
			if (!is_string($file))
			{
				$this->_path = $file;
			}
			else
			{
				throw new \InvalidArgumentException('I expected a string as a file name');
			}
			$this->createFrom( $this->_path );
		}
	}


	/**
	 * Private method used to read and populate anydataset class from specified file
	 * @param string $filepath Path and Filename to be read
	 * @return null
	 */
	private function createFrom($filepath)
	{
		if (file_exists($filepath))
		{
			$anyDataSet = XmlUtil::CreateXmlDocumentFromFile ( $filepath );
			$this->_collection = array();

			$rows = $anyDataSet->getElementsByTagName ( "row" );
			foreach ($rows as $row)
			{
				$sr = new SingleRow();
				$fields =  $row->getElementsByTagName("field");
				foreach ($fields as $field)
				{
					$attr = $field->attributes->getNamedItem("name");
					if (!is_null($attr))
					{
						$sr->addField($attr->nodeValue, $field->nodeValue);
					}
					else
					{
						throw new \InvalidArgumentException('Malformed anydataset file ' . basename($filepath));
					}
				}
				$sr->acceptChanges();
				$this->_collection[] = $sr;
			}
			$this->_currentRow = sizeof($this->_collection) - 1;
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
		$anyDataSet = XmlUtil::CreateXmlDocumentFromStr ( "<anydataset/>" );
		$nodeRoot = $anyDataSet->getElementsByTagName ( "anydataset" )->item ( 0 );
		foreach ($this->_collection as $sr)
		{
			$row = $sr->getDomObject();
			$nodeRow = $row->getElementsByTagName ( "row" )->item ( 0 );
			$newRow = XmlUtil::CreateChild($nodeRoot, "row");
			XmlUtil::AddNodeFromNode($newRow, $nodeRow);
		}

		return $anyDataSet;
	}

    /**
     *
     * @param type $file
     * @throws DatabaseException
     */
	public function save($file = null)
	{
		if (!is_null( $file ))
		{
			if (is_string( $file ))
            {
				$this->_path = $file;
			}
            else
            {
                throw new InvalidArgumentException('Invalid file name');
            }
		}

		if (is_null ( $this->_path )) {
			throw new DatabaseException ( "No such file path to save anydataset" );
		}

		XmlUtil::SaveXmlDocument ( $this->getDomObject(), $this->_path );
	}

	/**
	 * Append one row to AnyDataset.
	 * @param SingleRow $sr
	 * @return void
	 */
	public function appendRow($sr = null)
	{
		if (!is_null($sr))
		{
			if ($sr instanceof SingleRow )
			{
				$this->_collection[] = $sr;
			}
			elseif (is_array($sr))
			{
				$this->_collection[] = new SingleRow($sr);
			}
			else
			{
				throw new InvalidArgumentException("You must pass an array or a SingleRow object");
			}
		}
		else
		{
			$sr = new SingleRow();
			$this->_collection[] = $sr;
		}
		$sr->acceptChanges();
		$this->_currentRow = sizeof($this->_collection) - 1;
	}

	/**
	 * Enter description here...
	 *
	 * @param IteratorInterface $it
	 */
	public function import(IteratorInterface $it)
	{
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$this->appendRow($sr);
		}
	}

	/**
	 * Insert one row before specified position.
	 * @param int $rowNumber
	 * @param SingleRow row
	 */
	public function insertRowBefore($rowNumber, SingleRow $row = null)
	{
		if ($row >= sizeof($this->_collection))
		{
			$this->appendRow ();
		}
		else
		{
			if (is_null($row))
			{
				$row = new SingleRow();
			}
			array_splice($this->_collection, $rowNumber, 0, $row);
		}
	}

	/**
	 *
	 * @param mixed $row
	 * @return null
	 */
	public function removeRow($row = null)
	{
		if (is_null($row))
		{
			$row = $this->_currentRow;
		}
		if ($row instanceof SingleRow)
		{
			$i = 0;
			foreach($this->_collection as $sr)
			{
				if ($sr->toArray() == $row->toArray())
				{
					$this->removeRow($i);
					break;
				}
				$i++;
			}
			return;
		}

		if ($row == 0)
		{
			$this->_collection = array_slice($this->_collection, 1);
		}
		else
		{
			$this->_collection = array_slice($this->_collection, 0, $row) + array_slice($this->_collection, $row);
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
		if ($this->_currentRow < 0)
		{
			$this->appendRow();
		}
		$this->_collection[$this->_currentRow]->addField( $name, $value );
	}

	/**
	 * Get an Iterator filtered by an IteratorFilter
	 * @param IteratorFilter $itf
	 * @return IteratorInterface
	 */
	public function getIterator(IteratorFilter $itf = null)
	{
		if (is_null($itf))
		{
			return new AnyIterator ( $this->_collection );
		}
		else
		{
			return new AnyIterator ( $itf->match($this->_collection) );
		}
	}

	/**
	 * @desc
	 * @param IteratorFilter $itf
	 * @param string $fieldName
	 * @return array
	 */
	public function getArray($itf, $fieldName)
	{
		$it = $this->getIterator ( $itf );
		$result = array ();
		while ( $it->hasNext () )
		{
			$sr = $it->moveNext ();
			$result [] = $sr->getField ( $fieldName );
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
		if (count($this->_collection) == 0)
		{
			return;
		}

		$this->_collection = $this->quickSortExec ( $this->_collection, $field );

		return;
	}

	protected function quickSortExec($seq, $field )
	{
		if (! count ( $seq ))
			return $seq;

		$k = $seq [0];
		$x = $y = array ();

		$cntSeq = count ( $seq );
		for($i = 1; $i < $cntSeq; $i ++)
		{
			if ($seq[$i]->getField($field) <= $k->getField($field))
			{
				$x [] = $seq [$i];
			} else {
				$y [] = $seq [$i];
			}
		}

		return array_merge ( $this->quickSortExec ( $x, $field ), array ($k ), $this->quickSortExec ( $y, $field ) );
	}

    public static function fixUTF8($document)
    {
        Encoding::fixUTF8(Encoding::removeBOM($document), Encoding::ICONV_TRANSLIT);
    }

}
