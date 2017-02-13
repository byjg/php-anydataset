<?php
/**
 * Access a delimited string content from a HTTP server and iterate it.
 *
 * The string have the format:
 * COLUMN1;COLUMN2;COLUMN3|COLUMN1;COLUMN2;COLUMN3|COLUMN1;COLUMN2;COLUMN3
 *
 * You can customize the field and row separators.
 */

namespace ByJG\AnyDataset\Dataset;

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
     * SocketDataSet constructor.
     * @param string $server
     * @param string $path
     * @param string $rowsep
     * @param string $colsep
     * @param string $fieldnames
     * @param int $port
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
     * @return SocketIterator
     * @throws DatasetException
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
