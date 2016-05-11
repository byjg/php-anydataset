<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Exception\DatasetException;
use ByJG\AnyDataset\Exception\NotFoundException;
use Exception;
use InvalidArgumentException;

class FixedTextFileDataset
{

    protected $_source;

    /**
     * @var FixedTextDefinition[]
     */
    protected $_fieldDefinition;
    protected $_sourceType;

    /**
     * Text File Data Set
     *
     * @param string $source
     * @param FixedTextDefinition[] $fieldDefinition
     * @throws NotFoundException
     */
    public function __construct($source, $fieldDefinition)
    {
        if (!is_array($fieldDefinition)) {
            throw new InvalidArgumentException("You must define an array of field definition.");
        }

        $this->_source = $source;
        $this->_sourceType = "HTTP";

        if (!preg_match("~^https?://~", $source)) {
            if (!file_exists($this->_source)) {
                throw new NotFoundException("The specified file " . $this->_source . " does not exists");
            }

            $this->_sourceType = "FILE";
        }

        $this->_fieldDefinition = $fieldDefinition;
    }

    /**
     * @access public
     * @return DBIterator
     * @throws DatasetException
     * @throws Exception
     */
    public function getIterator()
    {
        if ($this->_sourceType == "HTTP") {
            return $this->getIteratorHttp();
        } else {
            return $this->getIteratorFile();
        }
    }

    protected function getIteratorHttp()
    {
        // Expression Regular:
        // [1]: http or ftp
        // [2]: Server name
        // [3]: Full Path
        $pat = "/(http|ftp|https):\/\/([\w+|\.]+)/i";
        $urlParts = preg_split($pat, $this->_source, -1, PREG_SPLIT_DELIM_CAPTURE);

        $handle = fsockopen($urlParts[2], 80, $errno, $errstr, 30);
        if (!$handle) {
            throw new DatasetException("TextFileDataset Socket error: $errstr ($errno)");
        } else {
            $out = "GET " . $urlParts[4] . " HTTP/1.1\r\n";
            $out .= "Host: " . $urlParts[2] . "\r\n";
            $out .= "Connection: Close\r\n\r\n";

            fwrite($handle, $out);

            try {
                $it = new FixedTextFileIterator($handle, $this->_fieldDefinition);
                return $it;
            } catch (Exception $ex) {
                fclose($handle);
                throw $ex;
            }
        }
    }

    protected function getIteratorFile()
    {
        $handle = fopen($this->_source, "r");
        if (!$handle) {
            throw new DatasetException("TextFileDataset File open error");
        } else {
            try {
                $it = new FixedTextFileIterator($handle, $this->_fieldDefinition);
                return $it;
            } catch (Exception $ex) {
                fclose($handle);
                throw $ex;
            }
        }
    }
}
