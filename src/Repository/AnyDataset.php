<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
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

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\DatabaseException;
use ByJG\Util\XmlUtil;
use DOMDocument;
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
 * @package xmlnuke
 *
 */
class AnyDataset
{
	/**
	 *@access private
	 *@var SingleRow[]
	 *@desc Internal structure represent the current SingleRow
	 */
	private $_collection;
	/**
	 *@access private
	 *@var int
	 *@desc Current node anydataset works
	 */
	private $_currentRow;
	/**
	 *@var string
	 *@desc Path to anydataset file
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
		if ($file != null)
		{
			if (!is_string($file))
			{
				$this->_path = $file;
			}
			else
			{
				throw new \InvalidArgumentException('I expected a string as a file name');
			}
			$this->CreateFrom( $this->_path );
		}
	}


	/**
	 *@access private
	 *@return void
	 *@param string $filepath - Path and Filename to be read
	 *@desc Private method used to read and populate anydataset class from specified file
	 */
	private function CreateFrom($filepath)
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
					if ($attr != null)
						$sr->addField($attr->nodeValue, $field->nodeValue);
					else
						throw new \InvalidArgumentException('Malformed anydataset file ' . basename($filepath));
				}
				$sr->acceptChanges();
				$this->_collection[] = $sr;
			}
			$this->_currentRow = sizeof($this->_collection) - 1;
		}
	}

	/**
	 *@access public
	 *@return string - XML String
	 *@desc Returns the AnyDataset XML representative structure.
	 */
	public function XML()
	{
		return $this->getDomObject()->saveXML();
	}

	/**
	 *@access public
	 *@return DOMDocument - XmlDocument object
	 *@desc Returns the AnyDataset XmlDocument representive object
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
	public function Save($file = null)
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
	 * @access public
	 * @param SingleRow $sr
	 * @return void
	 * @desc Append one row to AnyDataset.
	 */
	public function appendRow($sr = null)
	{
		if ($sr != null)
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
	 * @param IIterator $it
	 */
	public function import($it)
	{
		while ($it->hasNext())
		{
			$sr = $it->moveNext();
			$this->appendRow($sr);
		}
	}

	/**
	 *@access public
	 *@param int $rowNumber
	 *@param SingleRow row
	 *@desc Insert one row before specified position.
	 */
	public function insertRowBefore($rowNumber, $row = null)
	{
		if ($row >= sizeof($this->_collection))
		{
			$this->appendRow ();
		}
		else
		{
			if ($row == null)
			{
				$row = new SingleRow();
			}
			array_splice($this->_collection, $rowNumber, 0, $row);
		}
	}

	/**
	 *@access public
	 *@param int $row - Row number (sequential)
	 *@return
	 *@desc Remove specified row position.
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
	 *@access public
	 *@param string $name - Field name
	 *@param string $value - Field value
	 *@return void
	 *@desc Add a single string field to an existing row
	 */
	public function addField($name, $value)
	{
		if ($this->_currentRow < 0)
		{
			$this->appendRow();
		}
		$this->_collection[$this->_currentRow]->AddField( $name, $value );
	}

	/**
	 *@access public
	 *@param IteratorFilter $itf
	 *@return IIterator
	 *@desc Get an Iterator filtered by an IteratorFilter
	 */
	public function getIterator($itf = null)
	{
		if ($itf == null)
		{
			//return new AnyIterator(XmlUtil::selectNodes($this->_nodeRoot, ""));
			return new AnyIterator ( $this->_collection );
		}
		else
		{
			return new AnyIterator ( $itf->match($this->_collection) );
		}
	}

	/**
	 *@access public
	 *@param IteratorFilter $itf
	 *@param string $fieldName
	 *@return array
	 *@desc
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
	public function Sort($field)
	{
		if (count($this->_collection) == 0)
		{
			return;
		}

		$this->_collection = $this->quicksort_exec ( $this->_collection, $field );

		return;
	}

	protected function quicksort_exec($seq, $field )
	{
		if (! count ( $seq ))
			return $seq;

		$k = $seq [0];
		$x = $y = array ();

		for($i = 1; $i < count ( $seq ); $i ++)
		{
			if ($seq[$i]->getField($field) <= $k->getField($field))
			{
				$x [] = $seq [$i];
			} else {
				$y [] = $seq [$i];
			}
		}

		return array_merge ( $this->quicksort_exec ( $x, $field ), array ($k ), $this->quicksort_exec ( $y, $field ) );
	}

    public static function fixUTF8($document)
    {
        Encoding::fixUTF8(Encoding::removeBOM($document), Encoding::ICONV_TRANSLIT);
    }

}
