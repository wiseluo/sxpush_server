<?php
define('VivoPush_Root', dirname(__FILE__) . '/');
function vivopushAutoload($classname) {
    $parts = explode('\\', $classname);
    $path = VivoPush_Root . implode('/', $parts) . '.php';
    
    if (file_exists($path)) {
        include($path);
    }
}

spl_autoload_register('vivopushAutoload');
