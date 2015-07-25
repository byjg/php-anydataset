<?php

namespace ByJG\AnyDataset\Database;

class PdoDblib extends DBPDODriver
{
	public function __construct($connMngt)
	{
		parent::__construct($connMngt, $strcnn, $preOptions, $postOptions);

		// Solve the error:
		// SQLSTATE[HY000]: General error: 1934 General SQL Server error: Check messages from the SQL Server [1934] (severity 16) [(null)]
		// http://gullele.wordpress.com/2010/12/15/accessing-xml-column-of-sql-server-from-php-pdo/
		// http://stackoverflow.com/questions/5499128/error-when-using-xml-in-stored-procedure-pdo-ms-sql-2008
		$this->_db->exec('SET QUOTED_IDENTIFIER ON');
		$this->_db->exec('SET ANSI_WARNINGS ON');
		$this->_db->exec('SET ANSI_PADDING ON');
		$this->_db->exec('SET ANSI_NULLS ON');
		$this->_db->exec('SET CONCAT_NULL_YIELDS_NULL ON');
	}
}
