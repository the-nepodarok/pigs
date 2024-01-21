<?php

$path = 'C:/OpenServer/domains/pigs/db/pigs.db';

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlite:' . $path,
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'on afterOpen' => function($event) {
        // $event->sender refers to the DB connection
        $event->sender->createCommand("PRAGMA foreign_keys = ON;")->execute();
    }

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
