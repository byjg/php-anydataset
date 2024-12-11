<?php

namespace ByJG\AnyDataset\Core;

use ByJG\Serializer\Serialize;

class RowObject implements RowInterface
{
    /**
     * @var mixed
     */
    private object $entity;

    public function __construct(object $instance)
    {
        $this->entity = $instance;
    }


    public function get(string $name): mixed
    {
        if (property_exists($this->entity, $name)) {
            return $this->entity->$name;
        }

        if (method_exists($this->entity, "get$name")) {
            $name = "get$name";
            return $this->entity->$name();
        }

        return null;
    }

    public function set(string $name, mixed $value, bool $append = false): void
    {
        if ($append) {
            throw new \InvalidArgumentException("Append is not supported for object");
        }

        if (property_exists($this->entity, $name)) {
            $this->entity->$name = $value;
            return;
        }

        if (method_exists($this->entity, "set$name")) {
            $name = "set$name";
            $this->entity->$name($value);
            return;
        }

        throw new \InvalidArgumentException("Field '$name' not found");
    }

    public function unset(string $name, mixed $value = null): void
    {
        throw new \InvalidArgumentException("Unset is not supported for object");
    }

    public function replace(string $name, mixed $oldValue, mixed $newValue): void
    {
        throw new \InvalidArgumentException("Replace is not supported for object");
    }

    public function toArray(?array $fields = []): array
    {
        $result = Serialize::from($this->entity)->toArray();

        if (empty($fields)) {
            return $result;
        }

        $retArray = [];
        foreach ($fields as $field) {
            $retArray[$field] = $result[$field] ?? null;
        }
        return $retArray;
    }

    public function entity(): mixed
    {
        return $this->entity;
    }
}
