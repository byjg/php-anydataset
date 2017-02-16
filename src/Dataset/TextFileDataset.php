<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\DatasetException;
use ByJG\AnyDataset\Exception\NotFoundException;
use Exception;
use InvalidArgumentException;

class TextFileDataset
{

    const CSVFILE = '/[|,;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
    const CSVFILE_SEMICOLON = '/[;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
    const CSVFILE_COMMA = '/[,](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';

    protected $source;
    protected $fields;
    protected $fieldexpression;
    protected $sourceType;

    /**
     * Text File Data Set
     *
     * @param string $source
     * @param array $fields
     * @param string $fieldexpression
     * @throws NotFoundException
     */
    public function __construct($source, $fields, $fieldexpression = null)
    {
        if (is_null($fieldexpression)) {
            $fieldexpression = TextFileDataset::CSVFILE;
        }

        if (!is_array($fields)) {
            throw new InvalidArgumentException("You must define an array of fields.");
        }
        if (!preg_match('~(http|https|ftp)://~', $source)) {
            $this->source = $source;

            if (!file_exists($this->source)) {
                throw new NotFoundException("The specified file " . $this->source . " does not exists");
            }

            $this->sourceType = "FILE";
        } else {
            $this->source = $source;
            $this->sourceType = "HTTP";
        }


        $this->fields = $fields;

        if ($fieldexpression == 'CSVFILE') {
            $this->fieldexpression = TextFileDataset::CSVFILE;
        } else {
            $this->fieldexpression = $fieldexpression;
        }
    }

    /**
     * @access public
     * @return GenericIterator
     * @throws DatasetException
     * @throws Exception
     */
    public function getIterator()
    {
        $old = ini_set('auto_detect_line_endings', true);
        $handle = @fopen($this->source, "r");
        ini_set('auto_detect_line_endings', $old);
        if (!$handle) {
            throw new DatasetException("TextFileDataset failed to open resource");
        }

        try {
            $iterator = new TextFileIterator($handle, $this->fields, $this->fieldexpression);
            return $iterator;
        } catch (Exception $ex) {
            fclose($handle);
            throw $ex;
        }
    }
}
