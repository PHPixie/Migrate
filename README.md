# Migrate

PHPixie migration library

[![Author](http://img.shields.io/badge/author-@dracony-blue.svg?style=flat-square)](https://twitter.com/dracony)
[![Source Code](http://img.shields.io/badge/source-phpixie/migrate-blue.svg?style=flat-square)](https://github.com/phpixie/migrate)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](https://github.com/phpixie/orm/blob/master/LICENSE)


PHPixie Migrate allows you to version your database schema and apply
updates to it in a conistent way. It also allows you to define some data
to be inserted in the database, which is useful when writing tests or
demo-ing the code.

### Configuration

Let's look at the default configuration in `assets/config/migrate.php`:

```php
return array(
    // migration configs
    'migrations' => array(
        'default' => array(

            // database connection name
            'connection' => 'default',

            // migration files path, relative to /assets/migrate/
            'path'       => 'migrations',

            // optional:

            // name of the table to keep version data it
            'migrationTable' => '__migrate',

            // name of the version field in the migration table
            'lastMigrationField' => 'lastMigration'
        )
    ),

    // seed data configs (we'll cover it later)
    'seeds' => array(
        'default' => array(

            // database connection name
            'connection' => 'default',

            // seed files path, relative to /assets/migrate
            'path' => 'seeds'
        )
    )
);
```

Most proably you won't need to change anything here, unless you are
handling multiple databases, or different sets of seed data.

### Creating and destroying the database

You can now create and destroy your database using the command line.
This is done using the `framework:database` command:

```
framework:database ACTION [ CONFIG ]
Create or drop a database

Arguments:
ACTION    Either 'create' or 'drop'
CONFIG    Migration configuration name, defaults to 'default'
```

E.g. running `conole framework:database create` will create a database.

### Migrations

First let's start with a brief introduction. Migrations allow you to keep
your database schema modifications in your code, which is much more
convenient than sharing database dumps or manually applying changes on
your production database. They work in  simple way: a special table
is created in the database that has a single row and column and keeps
the name of the last applied migration. When a migration is executed
all migrations that have a name "larger" than the current one will be
applied in `natsort()` order. E.g. if we have files 1.sql, 2.sql,...22.sql
and the latest executed migration is 13.sql then all migrations between
14 and 22 will be executed and the last migration field will be set to 22.
The migrations can be written in .sql and .php formats.

### SQL migrations

These are very simple. It is just an SQL file with statememnts delimited
by the `-- statement` separator:

```sql
CREATE TABLE fairies(
    id int NOT NULL,
    name VARCHAR(255)
);

-- statement

CREATE TABLE flowers(
    id int NOT NULL,
    name VARCHAR(255)
);
```

### PHP migrations

These are just PHP files that also allow you to execute SQL commands
and also provide access to the Database component.

```php
$this->execute("CREATE TABLE fairies(
    id int NOT NULL,
    name VARCHAR(255)
)");

$this->message("Output some message to the console");

// Usual Database queries
$this->connection()->updateQuery()
    ->table('users')
    ->set(['role' => 'user'])
    ->execute();
```

PHP migrations allow you a bit more flexibility than SQL ones, but SQL
migration files can also be executed directly on the database without
even using the Migrate component.

It is highly recommended to add some short description to your migration
names, and since we are using `natsort()` order you can safely write any
comment you like after for example an underscore: `33_fairies_table.sql`.

You can also use subfolders in your migrations directory, which is
helpful if you have a lot of files there. In that case the sorting
will be applied to the entire subpath, not just the file name. E.g.
you can have your files in such structure: `/2016/03/22/fairies_table.sql`.

To execute the migrations use the `framework:migrate` command:

```
framework:migrate [ CONFIG ]
Run migrations on the database

Arguments:
CONFIG    Migration configuration name, defaults to 'default'
```

We also need to address some questions here.

*Why arent there down migrations for rollback?*

If you think from the perspective of the database itself, there is no
such thing as a schema rollback. Rolling back is just applying another
migration that reverses previous changes. In many cases a rollback is not
even possible, for example if you rollback table deletion, the rollback
might recreate the table structure but won't bring back the data in it.


*Why are changes written in raw SQL and not using some universal methods
like `createTable` etc.?*

The problem of universal methods is that they often omit the subtle
differences between databases and make too many assumptions. There is
also the possibility that after an update such universal library might
start creating the tables in a slightly different fashion and then your
production database that had the migrations applied months ago will be in
a different state than a new database whre the same migrations have been
applied more recently. Additionaly there already exist many tools for
creating and converting database schemas that there is no need to
replicate this functionality in a migration library.

### Seeds

Seeds are sets of data that can be used to fill the database. This can be
some default users, item categories etc. They also can be used to prepare
your application for some functional tests. Each seed file contains data
for a single table and it's name must match the name of that table. The
files themselves can be either *.json* or *.php*, e.g:

```php
// /assets/migrate/seeds/fairies.php

<?php

return array(
    array(
        'id'   => 1,
        'name' => 'Pixie'
    ),
    array(
        'id'   => 2,
        'name' => 'Trixie'
    ),
);
```

or

```json
// /assets/migrate/seeds/flowers.json

[
    {
        "id": 1,
        "name": "daisy"
    },
    {
        "id": 2,
        "name": "Rose"
    },
]
```

When using .php files you also have access to the Database component:

```php
// /assets/migrate/seeds/fairies.php

<?php
$this->connection()->insertQuery()
    ->data([
        'id'   => 1,
        'name' => 'Pixie'
     ])
     ->execute();
```

To insert the seed data use the `framework:seed` command:

```
framework:seed [ --truncate ] [ CONFIG ]
Seed the database with data

Options:
truncate    Truncate the tables before inserting the data.

Arguments:
CONFIG    Seed configuration name, defaults to 'default'
```

If some tables that are to be seeded already contain some data this will
result in an error. To cler the tables before inserting the data use the
`--truncate` flag.

You can create multiple seed profiles in separate directories for the same
database connection by adding them to `/assets/config/migrate.php`
configuration file.

### Using without the framework

As all the other PHPixie components you can use Migrate without the
framework. For example like this:

```php
$slice = new \PHPixie\Slice();
$database = new \PHPixie\Database($slice->arrayData(array(
    'default' => array(
        'database' => 'phpixie',
        'user'     => 'phpixie',
        'password' => 'phpixie',
        'adapter'  => 'mysql', // one of: mysql, pgsql, sqlite
        'driver'   => 'pdo'
    )
)));

$filesystem = new \PHPixie\Filesystem();
$migrate = new \PHPixie\Migrate(
    $filesystem->root(__DIR__.'/assets/migrate'),
    $database,
    $slice->arrayData(array(
    'migrations' => array(
        'default' => array(
            'connection' => 'default',
            'path'       => 'migrations',
        )
    ),
    'seeds' => array(
        'default' => array(
            'connection' => 'default',
            'path' => 'seeds'
        )
    )
)));

$cli = new \PHPixie\CLI();
$console = new \PHPixie\Console($slice, $cli, $migrate->consoleCommands());
$console->runCommand();
```

In this case the console commands will be `run`, `seed` and `database`,
without the `framework` prefix.
