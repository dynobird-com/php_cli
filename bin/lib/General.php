<?php

function camelCase($str)
{
    $i = array("-", "_");
    $str = preg_replace('/([a-z])([A-Z])/', "\\1 \\2", $str);
    $str = preg_replace('@[^a-zA-Z0-9\-_ ]+@', '', $str);
    $str = str_replace($i, ' ', $str);
    $str = str_replace(' ', '', ucwords(strtolower($str)));
    return $str;
}

function uncamelCase($str)
{
    $str = preg_replace('/([a-z])([A-Z])/', "\\1_\\2", $str);
    $str = strtolower($str);
    $str = str_replace(' ', '_', $str);
    return $str;
}
