oAuth Demo App
==============

This is a quick dev hack together of a working PHP application that uses
oAuth. It is *not for production* as it is not secured in any way, but it can be
used as a learning tool running locally.

Installation
------------

Requires PHP5.2+ with:

* SQLite extension (`php_pdo_sqlite`)
* APC extension
* oAuth PECL extension

To install the application you should run `/setup.php` to create the DB and required tables.
Note that re-running this script will delete all current data.

Security (there is none!)
-------------------------

Note that this application is a learning tool with almost no validation or
security. It should not be deployed to production, but rather run locally as
a learning tool.