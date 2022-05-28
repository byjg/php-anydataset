<?php

namespace ByJG\AnyDataset\Core\Formatter;

interface FormatterInterface 
{
    /**
     * Return the object in your original format, normally as object
     *
     * @return mixed
     */
    public function raw();

    /**
     * Return the object transformed to string.
     *
     * @return string
     */
    public function toText();

    /**
     * Save the contents to a file
     *
     * @param string $filename
     * @return void
     */
    public function saveToFile($filename);
}