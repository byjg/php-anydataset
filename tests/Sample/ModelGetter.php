<?php

namespace AnyDataSet\Tests\Sample;

use ByJG\Serializer\BaseModel;

/**
 * @Xmlnuke:NodeName ModelGetter
 */
class ModelGetter extends BaseModel
{

    protected $_Id = "";
    protected $_Name = "";

    function __construct($Id, $Name)
    {
        $this->_Id = $Id;
        $this->_Name = $Name;
    }

    public function getId()
    {
        return $this->_Id;
    }

    public function getName()
    {
        return $this->_Name;
    }

    public function setId($Id)
    {
        $this->_Id = $Id;
    }

    public function setName($Name)
    {
        $this->_Name = $Name;
    }
}
