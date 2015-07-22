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
 * Access a delimited string content from a HTTP server and iterate it.
 *
 * The string have the format:
 * COLUMN1;COLUMN2;COLUMN3|COLUMN1;COLUMN2;COLUMN3|COLUMN1;COLUMN2;COLUMN3
 *
 * You can customize the field and row separators.
 *
 * @package xmlnuke
 */
namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\DatasetException;

class SocketDataSet
{
	private $_server = null;
	private $_colsep = null;
	private $_rowsep = null;
	private $_fields = null; //Array
	private $_port = null;
	private $_path = null;

    /**
     *
     * @param type $server
     * @param type $path
     * @param type $rowsep
     * @param type $colsep
     * @param type $fieldnames
     * @param type $port
     */
	public function __construct($server, $path, $rowsep, $colsep, $fieldnames, $port = 80)
	{
		$this->_server = $server;
		$this->_rowsep = $rowsep;
		$this->_colsep = $colsep;
		$this->_fields = $fieldnames;
		$this->_port = $port;
		$this->_path = $path;
	}

	/**
	*@access public
	*@param string $sql
	*@param array $array
	*@return DBIterator
	*/
	public function getIterator()
	{
		$errno = null;
		$errstr = null;
		$handle = fsockopen($this->_server, $this->_port, $errno, $errstr, 30);
		if (!$handle)
		{
			throw new DatasetException("Socket error: $errstr ($errno)");
		}
		else
		{
			$out = "GET " . $this->_path . " HTTP/1.1\r\n";
			$out .= "Host: " . $this->_server . "\r\n";
			$out .= "Connection: Close\r\n\r\n";

			fwrite($handle, $out);

			$it = new SocketIterator($handle, $this->_fields, $this->_rowsep, $this->_colsep);
			return $it;

			fclose($fp);
		}
	}

}
?>