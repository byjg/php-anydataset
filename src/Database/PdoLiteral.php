<?php

namespace ByJG\AnyDataset\Database;

use PDO;

class PdoLiteral extends DBPDODriver
{
	public function __construct(ConnectionManagement $connMngt)
	{
		$strcnn = $connMngt->getDbConnectionString();

		$postOptions = [
			PDO::ATTR_EMULATE_PREPARES => true
		];

		parent::__construct($connMngt, $strcnn, [], $postOptions);
	}
}
