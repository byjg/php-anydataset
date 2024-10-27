<?php

namespace Tests\Sample;

use ByJG\Serializer\BaseModel;

class ModelPublic extends BaseModel
{

    public $Id = "";
    public $Name = "";

    /**
     * ModelPublic constructor.
     *
     * @param string|int $Id
     * @param string $Name
     */
    public function __construct($Id, $Name)
    {
        $this->Id = $Id;
        $this->Name = $Name;
    }
}
