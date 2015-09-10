<?php
/**
 * Access a delimited string content from a HTTP server and iterate it.
 *
 * The string have the format:
 * COLUMN1;COLUMN2;COLUMN3|COLUMN1;COLUMN2;COLUMN3|COLUMN1;COLUMN2;COLUMN3
 *
 * You can customize the field and row separators.
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
     * @param string $sql
     * @param array $array
     * @return DBIterator
     */
    public function getIterator()
    {
        $errno = null;
        $errstr = null;
        $handle = fsockopen($this->_server, $this->_port, $errno, $errstr, 30);
        if (!$handle) {
            throw new DatasetException("Socket error: $errstr ($errno)");
        } else {
            $out = "GET " . $this->_path . " HTTP/1.1\r\n";
            $out .= "Host: " . $this->_server . "\r\n";
            $out .= "Connection: Close\r\n\r\n";

            fwrite($handle, $out);

            $it = new SocketIterator($handle, $this->_fields, $this->_rowsep, $this->_colsep);
            return $it;
        }
    }
}
