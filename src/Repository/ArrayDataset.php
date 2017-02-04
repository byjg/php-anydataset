<?php

namespace ByJG\AnyDataset\Repository;

use UnexpectedValueException;

class ArrayDataset
{

    /**
     * @var array
     */
    protected $array;

    /**
     * Constructor Method
     *
     * @param array $array
     * @param string $fieldName
     */
    public function __construct($array, $fieldName = "value")
    {

        $this->array = array();

        if (!$array) {
            return;
        }

        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $this->array[$key] = $value;
                } elseif (!is_object($value)) {
                    $this->array[$key] = array($fieldName => $value);
                } else {
                    $result = array("__class" => get_class($value));
                    $methods = get_class_methods($value);
                    foreach ($methods as $method) {
                        if (strpos($method, "get") === 0) {
                            $result[substr($method, 3)] = $value->{$method}();
                        }
                    }
                    $this->array[$key] = $result;
                    $props = get_object_vars($value);
                    $this->array[$key] += $props;
                }
            }
        } else {
            throw new UnexpectedValueException("You need to pass an array");
        }
    }

    /**
     * Return a IteratorInterface
     *
     * @return IteratorInterface
     */
    public function getIterator()
    {
        return new ArrayDatasetIterator($this->array);
    }
}
