<?php

namespace ByJG\AnyDataset;

class NoSqlDocument
{
    protected $idDocument;

    protected $collection;

    protected $document;

    protected $subDocument = [];


    /**
     * NoSqlDocument constructor.
     *
     * @param $idDocument
     * @param $collection
     * @param array $document
     */
    public function __construct($idDocument = null, $collection = null, $document = [])
    {
        $this->idDocument = $idDocument;
        $this->collection = $collection;

        $this->setDocument($document);
    }

    /**
     * @return null
     */
    public function getIdDocument()
    {
        return $this->idDocument;
    }

    /**
     * @param null $idDocument
     * @return $this
     */
    public function setIdDocument($idDocument)
    {
        $this->idDocument = $idDocument;
        return $this;
    }

    /**
     * @return null
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param null $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }


    /**
     * @return array
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param array $document
     * @return $this
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    public function addSubDocument(NoSqlDocument $document)
    {
        $this->subDocument[] = $document;
    }
}
