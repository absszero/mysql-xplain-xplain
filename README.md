mysql-xplain-xplain
===================

[![Build Status](https://travis-ci.org/rap2hpoutre/mysql-xplain-xplain.png?branch=master)](https://travis-ci.org/rap2hpoutre/mysql-xplain-xplain) [![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/rap2hpoutre/mysql-xplain-xplain/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

Explain Explainer for MySQL Queries. Something like an upgraded `EXPLAIN` command for browser. Highlights and explains MySQL queries. It gives also hints and links. Hope it will help. More info here : http://mxx.mnstrl.org/

Getting started
---------------
 - [Download last release archive](https://github.com/rap2hpoutre/mysql-xplain-xplain/releases/download/v0.2.0/rap2h-mysql-explain-explain-0.2.0.zip) and unzip it into your webroot folder or install via composer : `composer create-project rap2h/mysql-explain-explain --prefer-dist`
 - Launch your web server, goto http://localhost/xplain or something like that
 - Start typing your queries


Console
----

### Setup connection
In `confg/db.php`
```php
<?php return array(
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'pass',
    'base' => 'my_database'
);
```
Or
```shell
php bin/explain --host=localhost --user=root --pass=pass --base=my_database
```

### Query from string
```shell
php bin/explain "select * from books where id = 1"
```

### Query from SQL file
```shell
php bin/explain ./query_data.sql
```

### Output  danger queries
```shell
php bin/explain ./query_data.sql --danger
```

### Output  warning queries
```shell
php bin/explain ./query_data.sql --warning
```

Why?
----

MySQL EXPLAIN command is sometimes hard to understand. We try to make it more readable with some improvements. In a web browser.

Contributors
------------
  - tazorax
  - rap2hpoutre
  - slythas
  - vincent-aubert
