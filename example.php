<?php
require "vendor/autoload.php";

$db = \ByJG\AnyDataset\Factory::getDbRelationalInstance('mysql://root:aaaaaaa@localhost/development');

$iterator = $db->getIterator('select * from airports where idairports = [[idairports]]', ['idairports' => 898]);

// Convert all iterator to Array
print_r($iterator->toArray());

// Iterate over all elements
foreach ($iterator as $row)
{
    print_r($row->toArray());
}

