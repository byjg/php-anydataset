<?php

namespace ByJG\AnyDataset\Database;

class PdoDblib extends DbPdoDriver
{

    public function __construct($connUri)
    {
        parent::__construct($connUri, null, null, null);

        // Solve the error:
        // SQLSTATE[HY000]: General error: 1934 General SQL Server error: Check messages from the SQL Server [1934] (severity 16) [(null)]
        // http://gullele.wordpress.com/2010/12/15/accessing-xml-column-of-sql-server-from-php-pdo/
        // http://stackoverflow.com/questions/5499128/error-when-using-xml-in-stored-procedure-pdo-ms-sql-2008
        $this->getDbConnection()->exec('SET QUOTED_IDENTIFIER ON');
        $this->getDbConnection()->exec('SET ANSI_WARNINGS ON');
        $this->getDbConnection()->exec('SET ANSI_PADDING ON');
        $this->getDbConnection()->exec('SET ANSI_NULLS ON');
        $this->getDbConnection()->exec('SET CONCAT_NULL_YIELDS_NULL ON');
    }
}
