<?php

namespace ByJG\AnyDataset\Core;
use Closure;

class RowOutput
{
    const FORMAT = 'format';
    const CUSTOM = 'custom';

    /**
     * @var array
     */
    protected $fieldList = [];

    /**
     * @return RowOutput
     */
    public static function getInstance()
    {
        return new RowOutput();
    }

    /**
     * @param Row $row
     * @param string $field
     * @return mixed
     */
    public function print($row, $field)
    {
        if (!isset($this->fieldList[$field])) {
            return $row->get($field);
        }

        $data = $this->fieldList[$field];

        if ($data[0] == self::CUSTOM) {
            return $this->formatCustom($row, $field, $data[1]);
        }

        // self::FORMAT:
        return $this->formatPattern($row, $field, $data[1]);
    }

    /**
     * @param Row $row
     * @return Row
     */
    public function apply($row)
    {
        $newRow = new Row();

        /**
         * @psalm-suppress UnusedForeachValue
         */
        foreach ($row->toArray() as $key => $value) {
            $newRow->set($key, $this->print($row, $key));
        }

        return $newRow;
    }

    /**
     * @param Row $row
     * @param string $field
     * @param string $pattern
     * @return string
     */
    protected function formatPattern($row, $field, $pattern)
    {
        $rowParsed = $row->toArray();
        foreach ($rowParsed as $key => $value) {
            $rowParsed['{' . $key . '}'] = $value;
            unset($rowParsed[$key]);
        }
        $rowParsed['{.}'] = $field;
        $rowParsed['{}'] = $row->get($field);

        return strtr($pattern, $rowParsed);
    }

    /**
     * @param Row $row
     * @param string $field
     * @param mixed $closure
     * @return string
     */
    protected function formatCustom($row, $field, $closure)
    {
        return $closure($row, $field, $row->get($field));
    }

    /**
     * @param string $field
     * @param string $pattern
     * @return RowOutput
     */
    public function addFormat($field, $pattern)
    {
        $this->fieldList[$field] = [ self::FORMAT,  $pattern ];
        return $this;
    }

    /**
     * @param string $field
     * @param Closure $closure
     * @return RowOutput
     */
    public function addCustomFormat($field, Closure $closure)
    {
        $this->fieldList[$field] = [ self::CUSTOM, $closure ];
        return $this;
    }
}