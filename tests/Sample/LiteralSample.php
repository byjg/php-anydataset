<?php

namespace Tests\AnyDataset\Sample;

use ByJG\Serializer\BaseModel;

/**
 * @Xmlnuke:NodeName ModelGetter
 */
class LiteralSample extends BaseModel
{

    protected $value = "";

    /**
     * LiteralSample constructor.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return "cast('" . $this->value . "' as integer)";
    }
}
