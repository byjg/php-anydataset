<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Exception\NotFoundException;
use Generator;
use LogicException;
use Override;
use ReflectionGenerator;

/**
 * An iterator whose rows come from a Generator: the subclass only returns the generator of its rows in rows().
 *
 * It moves forward only, as every iterator of the family (a database cursor, a file handle): rewind() does
 * nothing, and a foreach after next() continues from the current row. To read the rows again, get a new
 * iterator from the dataset.
 *
 * A foreach runs on the generators themselves (see getIterator()), not on valid()/current()/next() of this
 * object: there is no PHP method call for each row.
 *
 * @psalm-suppress MissingTemplateParam
 */
abstract class GenericIterator implements IteratorInterface
{
    private ?Generator $generator = null;

    /**
     * The generator of the rows, from a static function: rows() does not yield itself.
     *
     * A generator of a method keeps the iterator ($this) while it is not finished, and the iterator keeps the
     * generator: the iterator would stay in memory, with its file or cursor open, until the garbage collector
     * finds the cycle. From a static function, the generator is destroyed with the iterator, and its `finally`
     * releases what it opened.
     *
     * @return Generator<int|string, RowInterface|null>
     */
    abstract protected function rows(): Generator;

    private function generator(): Generator
    {
        if ($this->generator === null) {
            $generator = $this->rows();
            if ((new ReflectionGenerator($generator))->getThis() === $this) {
                throw new LogicException(
                    static::class . "::rows() must return the generator of a static function, not yield itself"
                );
            }
            $this->generator = $generator;
        }
        return $this->generator;
    }

    /**
     * The rows from the current one: a foreach continues where valid()/next() or another foreach stopped
     */
    #[Override]
    public function getIterator(): Generator
    {
        return self::resume($this->generator());
    }

    /**
     * `yield from` continues the rows from where they are, without rewinding them; a finished generator cannot
     * be given to it, so it is checked first
     */
    private static function resume(Generator $rows): Generator
    {
        if ($rows->valid()) {
            yield from $rows;
        }
    }

    #[Override]
    public function valid(): bool
    {
        return $this->generator()->valid();
    }

    #[Override]
    public function current(): mixed
    {
        return $this->generator()->current();
    }

    #[Override]
    public function key(): mixed
    {
        return $this->generator()->key();
    }

    #[Override]
    public function next(): void
    {
        $this->generator()->next();
    }

    #[Override]
    public function rewind(): void
    {
        // Forward only
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function toArray(array $fields = []): array
    {
        $retArray = [];

        foreach ($this as $singleRow) {
            $retArray[] = $singleRow->toArray($fields);
        }

        return $retArray;
    }

    /**
     * Return the underlying entities for each row in the iterator.
     */
    #[Override]
    public function toEntities(): array
    {
        $retArray = [];

        foreach ($this as $singleRow) {
            $retArray[] = $singleRow->entity();
        }

        return $retArray;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function first(): mixed
    {
        if (!$this->valid()) {
            return null;
        }
        return $this->current()->entity();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function firstOrFail(): mixed
    {
        if (!$this->valid()) {
            throw new NotFoundException("No results found in iterator");
        }
        return $this->current()->entity();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function exists(): bool
    {
        return $this->valid();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function existsOrFail(): bool
    {
        if (!$this->exists()) {
            throw new NotFoundException("Iterator is empty");
        }
        return true;
    }
}
