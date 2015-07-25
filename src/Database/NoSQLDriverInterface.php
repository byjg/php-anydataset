<?php

namespace ByJG\AnyDataset\Database;

interface NoSQLDriverInterface
{
	function getIterator($filter = null, $fields = null);
	function getCollection();
	function setCollection($collection);
	function insert($document);
	function update($document, $filter = null, $options = null);

	function getDbConnection();

	//function setAttribute($name, $value);
	//function getAttribute($name);
}
