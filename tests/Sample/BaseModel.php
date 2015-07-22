<?php

namespace Tests\Sample;

use ByJG\AnyDataset\Model\BaseModel as BaseModelGeneric;

class BaseModel extends BaseModelGeneric
{
	public $Id = "";
	protected $_Name = "";

	function __construct($object = null)
	{
		parent::__construct($object);
	}

	public function getName()
	{
		return $this->_Name;
	}

	public function setName($Name)
	{
		$this->_Name = $Name;
	}
}