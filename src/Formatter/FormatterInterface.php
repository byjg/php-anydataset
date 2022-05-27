<?php

namespace ByJG\AnyDataset\Core\Formatter;

use \ByJG\AnyDataset\Core\AnyDataset;

interface FormatterInterface 
{
    public function raw();

    public function toText();

    public function saveToFile($filename);
}