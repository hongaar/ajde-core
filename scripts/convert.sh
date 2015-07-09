#!/bin/bash

php namespacer.phar map --mapfile types.php --source Ajde
php namespacer.phar transform --mapfile types.php

