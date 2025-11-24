<?php

namespace ByJG\AnyDataset\Core\Formatter;

interface FormatterInterface 
{
    /**
     * Return the object in your original format, normally as object
     *
     * @return mixed
     */
    public function raw(): mixed;

    /**
     * Return the object transformed to string.
     *
     * @return string|false
     */
    public function toText(): string|false;

    /**
     * Save the contents to a file
     *
     * @param string $filename
     * @return void
     */
    public function saveToFile(string $filename): void;
}