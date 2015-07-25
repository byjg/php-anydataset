<?php

namespace ByJG\AnyDataset\Database;

interface DBDriverInterface
{
	function getIterator($sql, $array = null);
	function getScalar($sql, $array = null);
	function getAllFields($tablename);	
	function executeSql($sql, $array = null);
	
	function beginTransaction();
	function commitTransaction();
	function rollbackTransaction();
	
	function getDbConnection();

	function setAttribute($name, $value);
	function getAttribute($name);
}
