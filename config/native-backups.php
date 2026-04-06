<?php

$defaultSqlitePath = defined('NATIVE_PHP')
    ? storage_path('database.sqlite')
    : database_path('database.sqlite');

$sqlitePath = env('NATIVE_SQLITE_PATH', $defaultSqlitePath);
$defaultBackupsPath = dirname($sqlitePath).DIRECTORY_SEPARATOR.'backups';

return [
    'sqlite_path' => $sqlitePath,

    'backups_path' => env('NATIVE_SQLITE_BACKUPS_PATH', $defaultBackupsPath),

    'max_files' => env('NATIVE_SQLITE_MAX_FILES', 100),
];
