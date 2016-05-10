<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Enum\FixedTextDefinition;
use ByJG\AnyDataset\Exception\IteratorException;

class FixedTextFileIterator extends GenericIterator
{

    /**
     *
     * @var FixedTextDefinition[]
     */
    protected $_fields;

    /**
     * @var resource
     */
    protected $_handle;

    /**
     * @var int
     */
    protected $_current = 0;

    /**
     *
     * @param int $handle
     * @param FixedTextDefinition[] $fields
     */
    public function __construct($handle, $fields)
    {
        $this->_fields = $fields;
        $this->_handle = $handle;
        $this->_current = 0;
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
        if (!$this->_handle) {
            return false;
        } else {
            if (feof($this->_handle)) {
                fclose($this->_handle);
                return false;
            } else {
                return true;
            }
        }
    }


    /**
     * @return SingleRow|null
     * @throws IteratorException
     */
    public function moveNext()
    {
        if ($this->hasNext()) {
            $buffer = fgets($this->_handle, 4096);

            if ($buffer == "") {
                return new SingleRow();
            }

            $fields = $this->processBuffer($buffer, $this->_fields);

            if (is_null($fields)) {
                throw new IteratorException("Definition does not match");
            }

            $this->_current++;
            return new SingleRow($fields);
        } else {
            if ($this->_handle) {
                fclose($this->_handle);
            }
            return null;
        }
    }

    protected function processBuffer($buffer, $fieldDefinition)
    {
        $cntDef = count($fieldDefinition);
        $fields = [];
        for ($i = 0; $i < $cntDef; $i++) {
            $fieldDef = $fieldDefinition[$i];

            $fields[$fieldDef->fieldName] = substr($buffer, $fieldDef->startPos, $fieldDef->length);
            if (!empty($fieldDef->requiredValue) && (!preg_match("/^[" . $fieldDef->requiredValue . "]$/",
                    $fields[$fieldDef->fieldName]))) {
                throw new IteratorException("Expected the value '" . $fieldDef->requiredValue . "' and I got '" . $fields[$fieldDef->fieldName] . "'");
            } elseif (is_array($fieldDef->subTypes)) {
                $fields[$fieldDef->fieldName] = $this->processBuffer($fields[$fieldDef->fieldName], $fieldDef->subTypes);
            }
        }

        return $fields;
    }

    function key()
    {
        return $this->_current;
    }
}
