<?php

namespace AnyDataSet\Tests\Sample;

use ByJG\Serializer\BaseModel;

class ModelPublic extends BaseModel
{

    public $Id = "";
    public $Name = "";

    public function __construct($Id, $Name)
    {
        $this->Id = $Id;
        $this->Name = $Name;
    }
}
