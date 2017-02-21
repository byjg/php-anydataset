The DbCached class implements the DbDriverInterface and it is a wrapper for a PSR-6 cache object.

You can install any PSR-5 implementation. We suggested "byjg/cache"
 
The basic usage is:

```php
<?php

$dbDriver = \ByJG\AnyDataset\Factory::getDbRelationalInstance('mysql://root:password@192.168.1.181/test');

$dbCached = new \ByJG\AnyDataset\Store\DbCached($dbDriver, \ByJG\Cache\Factory::createFilePool('prefix'), 600));

$iterator = $dbCached->getIterator('select * from teste where a = :nome or a = :nome2', ['nome' => 'Joao', 'nome2' => 'Vieira']);
```

The result of this iterator will be cache for a 600 seconds.
