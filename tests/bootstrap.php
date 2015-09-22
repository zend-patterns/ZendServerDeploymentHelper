<?php
chdir(dirname(__DIR__));

$paths = array(
    dirname(__DIR__),
    dirname(__FILE__) . '/lib',
    get_include_path()
);

set_include_path(join(PATH_SEPARATOR, $paths));

// Setup autoloading
require 'init_autoloader.php';

define('SCRIPT_ROOT', dirname(__FILE__));