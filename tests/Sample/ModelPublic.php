<?php

namespace Tests\Sample;

use ByJG\AnyDataset\Model\BaseModel;

class ModelPublic extends BaseModel
{

    public $Id = "";
    public $Name = "";

    function __construct($Id, $Name)
    {
        $this->Id = $Id;
        $this->Name = $Name;
    }
}
