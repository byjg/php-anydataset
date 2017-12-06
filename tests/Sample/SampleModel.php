<?php

namespace Tests\AnyDataset\Sample;

use ByJG\Serializer\BaseModel;

class SampleModel extends BaseModel
{

    public $Id = "";
    protected $_Name = "";

    public function __construct($object = null)
    {
        parent::__construct($object);
    }

    public function getName()
    {
        return $this->_Name;
    }

    public function setName($Name)
    {
        $this->_Name = $Name;
    }
}
