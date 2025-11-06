<?php

namespace ByJG\AnyDataset\Core;

use Iterator;

/**
 * @extends Iterator<int|string, mixed>
 */
interface IteratorInterface extends Iterator
{
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
