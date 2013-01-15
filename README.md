Database Migratons Module for PHPixie
====================

This module allows you to easily migrate between revisions of your database

To use this module:
* Put it in your /modules folder
* Add 'migrate' to modules array in /application/config/core.php
* There are some example migrations in /migrate/migrations folder
* Visit [http://localhost/migrate](http://localhost/migrate) to access the migration panel
* No need for writing 'up' and 'down' rules, you add 'up' rules only and the
system guesses the 'down' rules.
