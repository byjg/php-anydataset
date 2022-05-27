<?php

namespace ByJG\AnyDataset\Core;
use Closure;

class RowValidator
{
    protected $fieldValidator = [];

    const REQUIRED="required";
    const NUMBER="number";
    const REGEX="regex";
    const CUSTOM="custom";

    protected function setProperty($fieldList, $property, $value)
    {
        foreach ((array)$fieldList as $field) {
            if (!isset($this->fieldValidator[$field])) {
                $this->fieldValidator[$field] = [];
            }
            $this->fieldValidator[$field] = [ $property => $value ];
        }
    }

    public static function getInstance()
    {
        return new RowValidator();
    }

    public function validate(Row $row)
    {
        $errors = [];
        foreach ($this->fieldValidator as $field => $properties) {
            $errors[] = $this->checkRequired($field, $properties, $row->get($field));
            $errors[] = $this->checkNumber($field, $properties, $row->get($field));
            $errors[] = $this->checkRegex($field, $properties, $row->get($field));
            $errors[] = $this->checkCustom($field, $properties, $row->get($field));
        }
        return array_values(array_filter($errors, function($value) { return !empty($value); }));
    }

    protected function checkRequired($field, $properties, $value)
    {
        if (isset($properties[self::REQUIRED]) && $properties[self::REQUIRED] && empty($value)) {
            return "$field is required";
        }
        return null;
    }

    protected function checkNumber($field, $properties, $value)
    {
        if (isset($properties[self::NUMBER]) && $properties[self::NUMBER] && !is_numeric($value)) {
            return "$field needs to be a number";
        }
        return null;
    }

    protected function checkRegex($field, $properties, $value)
    {
        if (isset($properties[self::REGEX]) && !empty($properties[self::REGEX]) && !preg_match($properties[self::REGEX], $value)) {
            return "Regex expression for $field doesn't match";
        }
        return null;
    }

    protected function checkCustom($field, $properties, $value)
    {
        $result = null;
        if (isset($properties[self::CUSTOM]) && $properties[self::CUSTOM] instanceof Closure) {
            $result =  $properties[self::CUSTOM]($value);
        }
        return empty($result) ? null : $result;
    }

    public function requiredField($field)
    {
        return $this->requiredFields([$field]);
    }

    public function requiredFields($fieldList)
    {
        $this->setProperty($fieldList, self::REQUIRED, true);
        return $this;
    }

    public function numericFields($fieldList)
    {
        $this->setProperty($fieldList, self::NUMBER, true);
        return $this;
    }

    public function regexValidation($field, $regEx)
    {
        $this->setProperty($field, self::REGEX, $regEx);
        return $this;
    }

    public function customValidation($field, $closure)
    {
        $this->setProperty($field, self::CUSTOM, $closure);
        return $this;
    }

}