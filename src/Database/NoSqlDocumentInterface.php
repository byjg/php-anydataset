<?php

namespace ByJG\AnyDataset\Database;

interface NoSqlDocumentInterface
{

    public function getIterator($filter = null, $fields = null);

    public function getCollection();

    public function setCollection($collection);

    public function insert($document);

    public function update($document, $filter = null, $options = null);

    public function getDbConnection();

    //function setAttribute($name, $value);
    //function getAttribute($name);
}
