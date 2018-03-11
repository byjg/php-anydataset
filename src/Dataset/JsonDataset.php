<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\DatasetException;

class JsonDataset
{

    /**
     * @var object
     */
    private $jsonObject;

    /**
     * JsonDataset constructor.
     * @param $json
     * @throws DatasetException
     */
    public function __construct($json)
    {
        $this->jsonObject = json_decode($json, true);

        $lastError = json_last_error();
        switch ($lastError) {
            case JSON_ERROR_NONE:
                $lastErrorDesc = 'No errors';
                break;
            case JSON_ERROR_DEPTH:
                $lastErrorDesc = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $lastErrorDesc = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $lastErrorDesc = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $lastErrorDesc = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $lastErrorDesc = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $lastErrorDesc = 'Unknown error';
                break;
        }

        if ($lastError != JSON_ERROR_NONE) {
            throw new DatasetException("Invalid JSON string: " . $lastErrorDesc);
        }
    }

    /**
     * @access public
     * @param string $path
     * @param bool $throwErr
     * @return GenericIterator
     * @throws \ByJG\AnyDataset\Exception\IteratorException
     */
    public function getIterator($path = "", $throwErr = false)
    {
        $iterator = new JsonIterator($this->jsonObject, $path, $throwErr);
        return $iterator;
    }
}
