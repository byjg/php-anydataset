<?php

namespace ByJG\AnyDataset\Core;

use IteratorAggregate;

/**
 * The rows of a dataset, read forward only.
 *
 * A foreach reads the rows through getIterator(); valid(), current(), key() and next() step the same rows by
 * hand. Both move the same position: a foreach after next() continues from the current row.
 *
 * @extends IteratorAggregate<int|string, mixed>
 */
interface IteratorInterface extends IteratorAggregate
{
    /**
     * Whether there is a current row
     *
     * @return bool
     */
    public function valid(): bool;

    /**
     * The current row, or null when there is none
     *
     * @return mixed
     */
    public function current(): mixed;

    /**
     * The key of the current row
     *
     * @return mixed
     */
    public function key(): mixed;

    /**
     * Moves to the next row
     *
     * @return void
     */
    public function next(): void;

    /**
     * Does nothing: the rows are read forward only. Get a new iterator from the dataset to read them again.
     *
     * @return void
     */
    public function rewind(): void;

    /**
     * Get an array of the iterator
     *
     * @param array $fields
     * @return array
     */
    public function toArray(array $fields = []): array;

    /**
     * Get an array of the underlying entities
     *
     * @return array
     */
    public function toEntities(): array;

    /**
     * Get the first element of the iterator, or null if empty
     *
     * @return mixed
     */
    public function first(): mixed;

    /**
     * Get the first element of the iterator, or throw an exception if empty
     *
     * @return mixed
     * @throws Exception\NotFoundException
     */
    public function firstOrFail(): mixed;

    /**
     * Check if the iterator has any elements
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Check if the iterator has any elements, or throw an exception if empty
     *
     * @return bool
     * @throws Exception\NotFoundException
     */
    public function existsOrFail(): bool;
}
