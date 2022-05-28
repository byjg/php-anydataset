<?php

namespace ByJG\AnyDataset\Core;
use Closure;

class RowOutput
{
    const FORMAT = 'format';
    const CUSTOM = 'custom';

    protected $fieldList = [];

    public static function getInstance()
    {
        return new RowOutput();
    }

    public function print($row, $field)
    {
        if (!isset($this->fieldList[$field])) {
            return $row->get($field);
        }

        $data = $this->fieldList[$field];

        switch ($data[0]) {
            case self::FORMAT:
                return $this->formatPattern($row, $field, $data[1]);
            case self::CUSTOM:
                return $this->formatCustom($row, $field, $data[1]);
        }
    }

    public function apply($row)
    {
        foreach ($this->fieldList as $key => $value) {
            $row->set($key, $this->print($row, $key));
        }
    }

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
     * Undocumented function
     *
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