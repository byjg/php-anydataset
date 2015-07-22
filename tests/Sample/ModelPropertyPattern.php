<?php

namespace Tests\Sample;

/**
 * @Xmlnuke:NodeName ModelPropertyPattern
 */
class ModelPropertyPattern extends \ByJG\AnyDataset\Model\BaseModel
{
	protected $_Id_Model = "";
	protected $_Client_Name = "";

	function __construct($object = null)
	{
		parent::__construct($object);
	}

	public function getIdModel()
	{
		return $this->_Id_Model;
	}

	public function getClientName()
	{
		return $this->_Client_Name;
	}

	public function setIdModel($Id)
	{
		$this->_Id_Model = $Id;
	}

	public function setClientName($Name)
	{
		$this->_Client_Name = $Name;
	}



}