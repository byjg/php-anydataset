<?php

namespace ByJG\AnyDataset\Core;

interface RowInterface
{
    public function get(string $name): mixed;
    public function set(string $name, mixed $value, bool $append = false): void;
    public function unset(string $name, mixed $value = null): void;
    public function replace(string $name, mixed $oldValue, mixed $newValue): void;
    public function toArray(?array $fields = []): array;
    public function entity(): mixed;

}