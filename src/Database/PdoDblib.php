<?php

/*
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 *  Copyright:
 *
 *  XMLNuke: A Web Development Framework based on XML.
 *
 *  Main Specification and Implementation: Joao Gilberto Magalhaes, joao at byjg dot com
 *
 *  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
 *  for more information.
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License
 *  as published by the Free Software Foundation; either version 2
 *  of the License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
 */

/**
 * @package xmlnuke
 */
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
		//$this->execSql('SET NUMERIC_ROUNDABORT OFF');
		//$this->execSql('set dateformat ymd');
	}
}
