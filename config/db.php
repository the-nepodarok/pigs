<?php

$path = 'C:/OpenServer/domains/pigs/db/pigs.db';

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlite:' . $path,
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'enableSchemaCache' => true,

    // Duration of schema cache.
    'schemaCacheDuration' => 3600,

    // Name of the cache component used to store schema information
    'schemaCache' => 'cache',
//    'on afterOpen' => function($event) {
//        // $event->sender refers to the DB connection
//        $event->sender->createCommand("PRAGMA foreign_keys = ON;")->execute();
//    }

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
