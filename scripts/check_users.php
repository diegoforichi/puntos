<?php
$path = $argv[1] ?? null;
if (!$path) {
    fwrite(STDERR, "Debe indicar ruta SQLite" . PHP_EOL);
    exit(1);
}
$db = new PDO('sqlite:' . $path);
foreach ($db->query('SELECT username,email,password FROM usuarios') as $row) {
    print_r($row);
}
