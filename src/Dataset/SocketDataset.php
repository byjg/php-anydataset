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

    private $server = null;
    private $colsep = null;
    private $rowsep = null;
    /**
     * @var array
     */
    private $fields = [];
    private $port = null;
    private $path = null;

    /**
     * SocketDataSet constructor.
     * @param string $server
     * @param string $path
     * @param string $rowsep
     * @param string $colsep
     * @param array $fieldnames
     * @param int $port
     */
    public function __construct($server, $path, $rowsep, $colsep, $fieldnames, $port = 80)
    {
        $this->server = $server;
        $this->rowsep = $rowsep;
        $this->colsep = $colsep;
        $this->fields = $fieldnames;
        $this->port = $port;
        $this->path = $path;
    }

    /**
     * @return GenericIterator
     * @throws DatasetException
     */
    public function getIterator()
    {
        $errno = null;
        $errstr = null;
        $handle = fsockopen($this->server, $this->port, $errno, $errstr, 30);
        if (!$handle) {
            throw new DatasetException("Socket error: $errstr ($errno)");
        }

        $out = "GET " . $this->path . " HTTP/1.1\r\n";
        $out .= "Host: " . $this->server . "\r\n";
        $out .= "Connection: Close\r\n\r\n";

        fwrite($handle, $out);

        $iterator = new SocketIterator($handle, $this->fields, $this->rowsep, $this->colsep);
        return $iterator;
    }
}
