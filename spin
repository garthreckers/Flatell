#!/usr/bin/env php
<?php

if ($argv[1] == 'install') {
    $path = rtrim(dirname(__file__), '/');
    $controller_path = $path . '/controller';
    $views_path = $path . '/views';
    $views_ext_path = $path . '/views/extendables/';
    $views_macros_path = $path . '/views/macros/';

    if (!file_exists($controller_path)) {
        mkdir($controller_path, 0777);
    }

    if (!file_exists($views_path)) {
        mkdir($views_path, 0777);
    }

    if (!file_exists($views_ext_path)) {
        mkdir($views_ext_path, 0777);
    }

    if (!file_exists($views_macros_path)) {
        mkdir($views_macros_path, 0777);
    }

}