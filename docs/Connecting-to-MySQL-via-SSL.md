# Connecting To MySQL via SSL

```php
<?php
$sslCa = "/path/to/ca";
$sslKey = "/path/to/Key";
$sslCert = "/path/to/cert";

$db = \ByJG\AnyDataset\Factory::getDbRelationalInstance(
    "mysql://localhost/database?ca=$sslCa&key=$sslKey&cert=$sslCert"
);

$iterator = $db->getIterator('select * from table where field = :value', ['value' => 10]);
foreach ($iterator as $row) {
    // Do Something
    // $row->getField('field');
}
