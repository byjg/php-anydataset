AnyDataset provides a common class called `DBDataSet` for connect to a relational databases. An example on how to connect is defined below:

```php
<?php
$db = \ByJG\AnyDataset\Factory::getDbRelationalInstance('pdodriver://root@localhost/dbname');
$iterator = $db->getIterator('select * from some_table');
```

In this example the system uses a connection string using the format of URI (Universal Resource Identifier). 

The URI have the follow format:

```url
DRIVER://USERNAME[:PASSWORD]@SERVER[:PORT]/DATABASE[?KEY1=VALUE1&KEY2=VALUE2&...]
```

The driver is any PDO driver available in your PHP installation. The list of all existing PDO drivers is available [here](http://www.php.net/manual/pdo.drivers.php). The driver name is the name of the PDO extension, without the 'pdo_' by default.

Anydataset also implements a native implementation of the Oracle OCI8 library `oci8`. This is necessary because the PDO Oracle driver still *EXPERIMENTAL* and some platforms could not install this driver.

Another possibility is to connect to a database is file based. The DSN syntax is:

```url
DRIVER:///path[?PARAMETERS]

or

DRIVER://C:/PATH[?PARAMETERS]
```

Below some real examples:

**Connect to MySQL at server localhost and database test identified by 'root' with no password:**

```url
mysql://root@localhost/test
```

**Connect to MySQL at server localhost and database test identified by 'root' and password '1234'**

```url
mysql://root:1234@localhost/test
```

**Connect to a SQL Lite database at /data/users.sqlite**

```url
sqlite:///data/users.sqlite
```
''

### See also [Connecting to Data Sources](Connecting-to-Data-Sources.md)
