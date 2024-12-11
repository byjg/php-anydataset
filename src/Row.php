<?php

namespace ByJG\AnyDataset\Core;

class Row implements RowInterface
{
    /**
     * @var mixed
     */
    private RowInterface $entity;

    public function __construct(object|array $instance = [])
    {
        $this->entity = Row::factory($instance);
    }

    public static function factory(object|array $instance = []): RowInterface
    {
        if (is_array($instance)) {
            return new RowArray($instance);
        }
        return new RowObject($instance);
    }

    public function get(string $name): mixed
    {
        return $this->entity->get($name);
    }

    public function set(string $name, mixed $value, bool $append = false): void
    {
        $this->entity->set($name, $value, $append);
    }

    public function unset(string $name, mixed $value = null): void
    {
        $this->entity->unset($name, $value);
    }

    public function replace(string $name, mixed $oldValue, mixed $newValue): void
    {
        $this->entity->replace($name, $oldValue, $newValue);
    }

    public function toArray(?array $fields = []): array
    {
        return $this->entity->toArray($fields);
    }

    public function entity(): mixed
    {
        return $this->entity->entity();
    }
}
