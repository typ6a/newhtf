<?php

require_once 'include/config.php';

$raw = ' 7994 Sunset Blvd West Hollywood,CA | 323-375-3419 ';

p($raw);

$parts = explode('|', $raw);
$str = trim($parts[0]);

p($str);

p('street: ' . preg_replace('/^([^,]+),.+$/', '$1', $str));
p('city: ' . preg_replace('/^[^,]+,([^,]+),.+$/', '$1', $str));
p('state: ' . preg_replace('/^.+, ?(\w{2})$/', '$1', $str));