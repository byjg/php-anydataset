<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Exception\IteratorException;

class FixedTextFileIterator extends GenericIterator
{

    /**
     *
     * @var FixedTextDefinition[]
     */
    protected $fields;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     *
     * @param resource $handle
     * @param FixedTextDefinition[] $fields
     */
    public function __construct($handle, $fields)
    {
        $this->fields = $fields;
        $this->handle = $handle;
        $this->current = 0;
    }

    /**
     * @access public
     * @return int
     */
    public function count()
    {
        return -1;
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if (!$this->handle) {
            return false;
        }

        if (feof($this->handle)) {
            fclose($this->handle);

            return false;
        }

        return true;
    }


    /**
     * @return SingleRow|null
     * @throws IteratorException
     */
    public function moveNext()
    {
        if ($this->hasNext()) {
            $buffer = fgets($this->handle, 4096);

            if ($buffer == "") {
                return new SingleRow();
            }

            $fields = $this->processBuffer($buffer, $this->fields);

            if (is_null($fields)) {
                throw new IteratorException("Definition does not match");
            }

            $this->current++;
            return new SingleRow($fields);
        }

        if ($this->handle) {
            fclose($this->handle);
        }
        return null;
    }

    protected function processBuffer($buffer, $fieldDefinition)
    {
        $cntDef = count($fieldDefinition);
        $fields = [];
        for ($i = 0; $i < $cntDef; $i++) {
            $fieldDef = $fieldDefinition[$i];

            $fields[$fieldDef->fieldName] = substr($buffer, $fieldDef->startPos, $fieldDef->length);
            if (!empty($fieldDef->requiredValue)
                && (
                    !preg_match("/^[" . $fieldDef->requiredValue . "]$/", $fields[$fieldDef->fieldName])
                )
            ) {
                throw new IteratorException(
                    "Expected the value '"
                    . $fieldDef->requiredValue
                    . "' and I got '"
                    . $fields[$fieldDef->fieldName]
                    . "'"
                );
            }

            if (is_array($fieldDef->subTypes)) {
                $fields[$fieldDef->fieldName] = $this->processBuffer($fields[$fieldDef->fieldName], $fieldDef->subTypes);
            }
        }

        return $fields;
    }

    public function key()
    {
        return $this->current;
    }
}
