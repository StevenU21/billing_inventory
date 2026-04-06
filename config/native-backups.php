<?php

return [
    'sqlite_path' => env('NATIVE_SQLITE_PATH') ?: (
        file_exists(database_path('database.sqlite'))
        ? database_path('database.sqlite')
        : (env('APPDATA')
            ? rtrim(env('APPDATA'), '\\/') . DIRECTORY_SEPARATOR . 'blessaboutique' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite'
            : storage_path('app/nativephp/database/database.sqlite')
        )
    ),

    'backups_path' => env('NATIVE_SQLITE_BACKUPS_PATH', 'C:\\blessaboutique_backups'),

    'max_files' => env('NATIVE_SQLITE_MAX_FILES', 100),
];
