<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes
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

/**
 * @package xmlnuke
 */
namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\IteratorException;
use ForceUTF8\Encoding;
use PDO;
use PDOStatement;

class DBIterator extends GenericIterator
{
	const RECORD_BUFFER = 50;
	private $_rowBuffer;

	private $_currentRow = 0;

	/**
	*@var PDOStatement
	*/
	private $_rs;

	/**
	*@access public
	*@param PDOStatement $recordset
	*@return void
	*/
	public function __construct($recordset)
	{
		$this->_rs = $recordset;
		$this->_rowBuffer = array();
	}

	/**
	*@access public
	*@return int
	*/
	public function Count()
	{
		return $this->_rs->rowCount();
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
        if (count($this->_rowBuffer) >= DBIterator::RECORD_BUFFER)
        {
            return true;
        }
        else if (is_null($this->_rs))
		{
			return (count($this->_rowBuffer) > 0);
		}
		else if ($row = $this->_rs->fetch(PDO::FETCH_ASSOC))
		{
			foreach ($row as $key=>$value)
			{
      			if (is_null($value))
      			{
      				$row[$key]  = "";
      			}
      			elseif (is_object($value))
      			{
      				$row[$key] = "[OBJECT]";
      			}
      			else
				{
					$row[$key] = Encoding::toUTF8($value);
				}
			}
			$sr = new SingleRow($row);

			// Enfileira o registo
			array_push($this->_rowBuffer, $sr);
			// Traz novos até encher o Buffer
			if (count($this->_rowBuffer) < DBIterator::RECORD_BUFFER)
			{
				$this->hasNext();
			}

			return true;
		}
		else
		{
			$this->_rs->closeCursor();
            $this->_rs = null;

            //if (this._db != null)
            //{
            //    this._db.Close();
            //    this._db = null;
            //}
			return (count($this->_rowBuffer) > 0);
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
		}
		else
		{
			$sr = array_shift($this->_rowBuffer);
			$this->_currentRow++;
			return $sr;
		}
	}

 	function key()
 	{
 		return $this->_currentRow;
 	}
}
?>
