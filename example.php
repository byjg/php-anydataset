<?php
require "vendor/autoload.php";

$db = new \ByJG\AnyDataset\Repository\DBDataset('mysql://root:aaaaaaa@10.10.10.101/development');

$iterator = $db->getIterator('select * from airports where idairports = [[idairports]]', ['idairports' => 898]);

// Convert all iterator to Array
print_r($iterator->toArray());

// Iterate over all elements
foreach ($iterator as $row)
{
    print_r($row->toArray());
}

