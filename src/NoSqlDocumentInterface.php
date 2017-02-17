<?php

namespace ByJG\AnyDataset;

interface NoSqlDocumentInterface
{

    public function getDocumentById($idDocument, $collection = null);

    public function save(NoSqlDocument $document);

    public function getDbConnection();

    //function setAttribute($name, $value);
    //function getAttribute($name);
}
