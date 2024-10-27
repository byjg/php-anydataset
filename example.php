<?php
require "vendor/autoload.php";

$dataset = new \ByJG\AnyDataset\Core\AnyDataset("example");

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    print $row->toArray();
}


$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->and("field1", \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 10);
$iterator2 = $dataset->getIterator($filter);

$iterator->toArray();

