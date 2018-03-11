<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Exception\DatasetException;
use ByJG\AnyDataset\Exception\NotFoundException;
use Exception;
use InvalidArgumentException;

class FixedTextFileDataset
{

    protected $source;

    /**
     * @var FixedTextDefinition[]
     */
    protected $fieldDefinition;
    protected $sourceType;

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

        $this->source = $source;
        $this->sourceType = "HTTP";

        if (!preg_match("~^https?://~", $source)) {
            if (!file_exists($this->source)) {
                throw new NotFoundException("The specified file " . $this->source . " does not exists");
            }

            $this->sourceType = "FILE";
        }

        $this->fieldDefinition = $fieldDefinition;
    }

    /**
     * @access public
     * @return GenericIterator
     * @throws DatasetException
     * @throws Exception
     */
    public function getIterator()
    {
        if ($this->sourceType == "HTTP") {
            return $this->getIteratorHttp();
        }
        return $this->getIteratorFile();
    }

    /**
     * @return \ByJG\AnyDataset\Dataset\FixedTextFileIterator
     * @throws \ByJG\AnyDataset\Exception\DatasetException
     * @return GenericIterator
     */
    protected function getIteratorHttp()
    {
        // Expression Regular:
        // [1]: http or ftp
        // [2]: Server name
        // [3]: Full Path
        $pat = "/(http|ftp|https):\/\/([\w+|\.]+)/i";
        $urlParts = preg_split($pat, $this->source, -1, PREG_SPLIT_DELIM_CAPTURE);

        $handle = fsockopen($urlParts[2], 80, $errno, $errstr, 30);
        if (!$handle) {
            throw new DatasetException("TextFileDataset Socket error: $errstr ($errno)");
        }

        $out = "GET " . $urlParts[4] . " HTTP/1.1\r\n";
        $out .= "Host: " . $urlParts[2] . "\r\n";
        $out .= "Connection: Close\r\n\r\n";

        try {
            fwrite($handle, $out);
        } catch (\Exception $ex) {
            fclose($handle);
            throw new DatasetException($ex->getMessage());
        }

        return new FixedTextFileIterator($handle, $this->fieldDefinition);
    }

    /**
     * @return \ByJG\AnyDataset\Dataset\FixedTextFileIterator
     * @throws \ByJG\AnyDataset\Exception\DatasetException
     */
    protected function getIteratorFile()
    {
        $handle = fopen($this->source, "r");
        if (!$handle) {
            throw new DatasetException("TextFileDataset File open error");
        }

        return new FixedTextFileIterator($handle, $this->fieldDefinition);
    }
}
