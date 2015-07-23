<?php

require "vendor/autoload.php";

$db = new \ByJG\AnyDataset\Repository\DBDataSet('mysql://root:aaaaaaa@10.10.10.101/development');

$iterator = $db->getIterator('select * from airports where idairports = [[idairports]]', ['idairports' => 898]);

print_r($iterator->toArray());

//foreach ($iterator as $row)
//{
//    print_r($row->toArray());
//}

