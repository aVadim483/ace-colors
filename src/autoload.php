<?php
/**
 * This file is part of the AceColors package
 * https://github.com/aVadim483/ace-colors
 *
 */

spl_autoload_register(function ($class) {
    if (0 === strpos($class, 'avadim\\AceColors\\')) {
        include __DIR__ . '/' . str_replace('avadim\\AceColors\\', '/', $class) . '.php';
    }
});

// EOF