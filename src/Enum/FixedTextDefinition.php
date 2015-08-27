<?php

namespace ByJG\AnyDataset\Enum;

/**
 * @package xmlnuke
 */
class FixedTextDefinition
{
	public $fieldName;
	public $startPos;
	public $length;
	public $requiredValue;
	public $subTypes = array();

	/**
	 *
	 * @param string $fieldName
	 * @param int $startPos
	 * @param int $length
	 * @param bool $requiredValue
	 * @param array_of_FixedTextDefinition $subTypes
	 */
	public function __construct($fieldName, $startPos, $length, $requiredValue = "", $subTypes = null)
	{
		$this->fieldName = $fieldName;
		$this->startPos = $startPos;
		$this->length = $length;
		$this->requiredValue = $requiredValue;
		$this->subTypes = $subTypes;
	}
}

