AnyDataset provides a common class called `DBDataSet` for connect to a relational databases. An example on how to connect is defined below:

```php
$db = new DBDataSet('conn_name');
$iterator = $db->getIterator('select * from some_table');
```

In this example, the `conn_name` is the alias for the connection defined in the file `config/anydatasetconfig.php` like:

```php
return [
    'connections' => [
        'conn_name' => [
            'url' => 'pdodriver://root@localhost/dbname',
            'type' => 'dsn'
        ]
    ]
];
```

The fields available are:

* name
* type (optional and constant: dsn)

#### Field name

This field is just an alias for the connection string. It is a good idea to abstract the details of the database from the source code.
It need to have a connection string url.

##### Url Connection String

The connection string is look like an url. For example:

```url
DRIVER://USERNAME[:PASSWORD]@SERVER[:PORT]/DATABASE[?KEY1=VALUE1&KEY2=VALUE2&...]
```

The driver is any PDO driver available in your PHP installation. The list of all existing PDO drivers is available [here](http://www.php.net/manual/pdo.drivers.php). The driver name is the name of the PDO extension, without the 'pdo_' by default.

XMLNuke have two special drivers. One is a driver for the [SQLRelay](http://sqlrelay.sourceforge.net/). SQLRelay is a persistent database connection pooling, proxying, load balancing and query routing system for Unix and Linux. Another driver is a native implementation of the Oracle OCI8 library. This is necessary because the PDO Oracle driver still *EXPERIMENTAL* and some platforms could not install this driver.

The driver name for SQLRelay is `sqlrelay` and the driver name for oracle native implementation is `oci8`

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

**Connect to a SQLRelay instance 'test' at the server '192.168.1.3' identified by 'joao' and password '1234'**

```url
sqlrelay://joao:1234@192.168.1.3/test
```




### See also [Connecting to Data Sources](Connecting-to-Data-Sources.md)
