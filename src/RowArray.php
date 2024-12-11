<?php

namespace ByJG\AnyDataset\Core;

class RowArray implements RowInterface
{
    /**
     * @var mixed
     */
    private array $entity = [];

    public function __construct(array $instance = [])
    {
        $this->entity = $instance;
    }


    public function get(string $name): mixed
    {
        return $this->entity[$name] ?? null;
    }

    public function set(string $name, mixed $value, bool $append = false): void
    {
        if (!isset($this->entity[$name])) {
            $this->entity[$name] = $value;
            return;
        }

        if ($append) {
            if (!is_array($this->entity[$name])) {
                $this->entity[$name] = [$this->entity[$name]];
            }
            $this->entity[$name][] = $value;
        } else {
            $this->entity[$name] = $value;
        }
    }

    public function unset(string $name, mixed $value = null): void
    {
        if (empty($value)) {
            unset($this->entity[$name]);
            return;
        }

        if (!is_array($this->entity[$name])) {
            if ($this->entity[$name] == $value) {
                unset($this->entity[$name]);
            }
            return;
        }

        $this->entity[$name] = array_filter($this->entity[$name], function ($item) use ($value) {
            return $item != $value;
        });

        $this->entity[$name] = array_values($this->entity[$name]);

        return;
    }

    public function replace(string $name, mixed $oldValue, mixed $newValue): void
    {
        if (!is_array($this->entity[$name])) {
            $this->entity[$name] = $this->entity[$name] == $oldValue ? $newValue : $this->entity[$name];
            return;
        }

        $this->entity[$name] = array_map(function ($item) use ($oldValue, $newValue) {
            return $item == $oldValue ? $newValue : $item;
        }, $this->entity[$name]);
    }

    public function toArray(?array $fields = []): array
    {
        if (empty($fields)) {
            return $this->entity;
        }

        // return an array with only the fields in $fields
        return array_intersect_key($this->entity, array_flip($fields));

    }

    public function entity(): mixed
    {
        return $this->entity;
    }
}
