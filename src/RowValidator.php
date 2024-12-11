<?php

namespace ByJG\AnyDataset\Core;
use Closure;

class RowValidator
{
    /**
     * @var array
     */
    protected array $fieldValidator = [];

    const REQUIRED="required";
    const NUMBER="number";
    const REGEX="regex";
    const CUSTOM="custom";

    /**
     * @param array|string $fieldList
     * @param string $property
     * @param mixed $value
     * @return void
     */
    protected function setProperty(array|string $fieldList, string $property, mixed $value)
    {
        foreach ((array)$fieldList as $field) {
            $this->fieldValidator[$field] = [ $property => $value ];
        }
    }

    /**
     * @return RowValidator
     */
    public static function getInstance()
    {
        return new RowValidator();
    }

    /**
     * Return an empty array if no errors found, otherwise and array with the errors
     *
     * @param Row $row
     * @return array
     */
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

    /**
     * Return null if the value is not empty, otherwise a string with the error message
     *
     * @param string $field
     * @param array $properties
     * @param mixed $value
     * @return string|null
     */
    protected function checkRequired($field, $properties, $value)
    {
        if (isset($properties[self::REQUIRED]) && $properties[self::REQUIRED] && empty($value)) {
            return "$field is required";
        }
        return null;
    }

    /**
     * Return null if the value is numeric, otherwise a string with the error message
     *
     * @param string $field
     * @param array $properties
     * @param mixed $value
     * @return string|null
     */
    protected function checkNumber($field, $properties, $value)
    {
        if (isset($properties[self::NUMBER]) && $properties[self::NUMBER] && !is_numeric($value)) {
            return "$field needs to be a number";
        }
        return null;
    }

    /**
     * Return null if the value matches with the regular expression, otherwise a string with the error message
     *
     * @param string $field
     * @param array $properties
     * @param mixed $value
     * @return string|null
     */
    protected function checkRegex($field, $properties, $value)
    {
        if (isset($properties[self::REGEX]) && !empty($properties[self::REGEX]) && !preg_match($properties[self::REGEX], $value)) {
            return "Regex expression for $field doesn't match";
        }
        return null;
    }

    /**
     * Return null if the closure returns null, otherwise the value returned by the Closure
     *
     * @param string $field
     * @param array $properties
     * @param mixed $value
     * @return string|null
     */
    protected function checkCustom($field, $properties, $value)
    {
        $result = null;
        if (isset($properties[self::CUSTOM]) && $properties[self::CUSTOM] instanceof Closure) {
            $result =  $properties[self::CUSTOM]($value);
        }
        return empty($result) ? null : $result;
    }

    /**
     * @param string $field
     * @return RowValidator
     */
    public function requiredField($field)
    {
        return $this->requiredFields([$field]);
    }

    /**
     * @param array $fieldList
     * @return RowValidator
     */
    public function requiredFields($fieldList)
    {
        $this->setProperty($fieldList, self::REQUIRED, true);
        return $this;
    }

    /**
     * @param array $fieldList
     * @return RowValidator
     */
    public function numericFields($fieldList)
    {
        $this->setProperty($fieldList, self::NUMBER, true);
        return $this;
    }

     /**
     * @param array|string $field
     * @param string $regEx
     * @return RowValidator
     */
    public function regexValidation($field, $regEx)
    {
        $this->setProperty($field, self::REGEX, $regEx);
        return $this;
    }

    /**
     * @param array|string $field
     * @param Closure $closure
     * @return RowValidator
     */
    public function customValidation($field, $closure)
    {
        $this->setProperty($field, self::CUSTOM, $closure);
        return $this;
    }

}