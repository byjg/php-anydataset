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
    protected array $fieldList = [];

    /**
     * @return RowOutput
     */
    public static function getInstance(): RowOutput
    {
        return new RowOutput();
    }

    /**
     * @param RowInterface $row
     * @param string $field
     * @return mixed
     */
    public function print(RowInterface $row, string $field): mixed
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
     * @param RowInterface $row
     * @return RowInterface
     */
    public function apply(RowInterface $row): RowInterface
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
     * @param RowInterface $row
     * @param string $field
     * @param string $pattern
     * @return string
     */
    protected function formatPattern(RowInterface $row, string $field, string $pattern): string
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
     * @param RowInterface $row
     * @param string $field
     * @param mixed $closure
     * @return string
     */
    protected function formatCustom(RowInterface $row, string $field, Closure $closure): string
    {
        return $closure($row, $field, $row->get($field));
    }

    /**
     * @param string $field
     * @param string $pattern
     * @return static
     */
    public function addFormat(string $field, string $pattern): static
    {
        $this->fieldList[$field] = [ self::FORMAT,  $pattern ];
        return $this;
    }

    /**
     * @param string $field
     * @param Closure $closure
     * @return static
     */
    public function addCustomFormat(string $field, Closure $closure): static
    {
        $this->fieldList[$field] = [ self::CUSTOM, $closure ];
        return $this;
    }
}