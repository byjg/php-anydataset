<?php

namespace ByJG\AnyDataset;

interface KeyValueInterface
{

    public function getIterator($options = []);

    public function get($key, $options = []);

    public function put($key, $value, $contentType = null, $options = []);

    public function remove($key, $options = []);

    public function getDbConnection();

    //function setAttribute($name, $value);
    //function getAttribute($name);
}
