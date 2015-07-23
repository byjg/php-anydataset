<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\DatasetException;
use SparQL\Connection;


class SparQLDataSet
{
	/**
	 * @var object
	 */
	private $_db;

	/**
	 *
	 * @param string $json
	 */
	public function __construct($url, $namespaces = null)
	{
		$this->_db = new Connection( $url );

		if( !$this->_db )
		{
			throw new DatasetException($this->_db->errno() . ": " . $this->_db->error());
		}

		if (is_array($namespaces))
		{
			foreach ($namespaces as $key => $value)
			{
				$this->_db->ns( $key, $value );
			}
		}

		if (function_exists('dba_open')) {
			$cache = sys_get_temp_dir() . "/caps.db";
			$this->_db->capabilityCache( $cache );
		}
	}

	public function getCapabilities()
	{
		$return = array();

		if (function_exists('dba_open')) {
			foreach( $this->_db->capabilityCodes() as $code )
			{
				$return[$code] = array($this->_db->supports( $code ), $this->_db->capabilityDescription($code));
			}
		}

		return $return;
	}

	/**
	*@access public
	*@param string $sql
	*@param array $array
	*@return DBIterator
	*/
	public function getIterator($sparql)
	{
		$result = $this->_db->query( $sparql );

		if( !$result )
		{
			throw new DatasetException($this->_db->errno() . ": " . $this->_db->error());
		}

		return new SparQLIterator($result);
	}

}
?>