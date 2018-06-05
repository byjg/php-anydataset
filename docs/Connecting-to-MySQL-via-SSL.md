# Connecting To MySQL via SSL

```php
<?php
$sslCa = "/path/to/ca";
$sslKey = "/path/to/Key";
$sslCert = "/path/to/cert";
$sslCaPath = "/path";
$sslCipher = "DHE-RSA-AES256-SHA:AES128-SHA";
$verifySsl = 0;  // Since PHP 7.1

$db = \ByJG\AnyDataset\Factory::getDbRelationalInstance(
    "mysql://localhost/database?ca=$sslCa&key=$sslKey&cert=$sslCert&capath=$sslCaPath&verifyssl=$verifySsl&cipher=$sslCipher"
);

$iterator = $db->getIterator('select * from table where field = :value', ['value' => 10]);
foreach ($iterator as $row) {
    // Do Something
    // $row->getField('field');
}
