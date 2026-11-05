<?php

namespace ByJG\AnyDataset\Core;

use Generator;
use Override;

/**
 * Iterator class is a structure used to navigate forward in a AnyDataset structure.
 */
class AnyIterator extends GenericIterator
{

    /**
     * Row Elements
     * @var RowInterface[]
     */
    private array $list;

    /**
     * Iterator constructor
     *
     * @param RowInterface[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    #[Override]
    protected function rows(): Generator
    {
        return self::walk(array_values($this->list));
    }

    /**
     * @param RowInterface[] $list
     */
    private static function walk(array $list): Generator
    {
        yield from $list;
    }
}
