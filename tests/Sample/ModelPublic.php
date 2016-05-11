<?php

namespace AnyDataSet\Tests\Sample;

use ByJG\Serialize\BaseModel;

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
