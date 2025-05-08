<?php

namespace app\services;

use Yii;

class FileReaderService
{
    /**
     * @param string $path
     * @return \Generator|int
     */
    public function readLine(string $path): int|\Generator
    {
        $file = fopen(Yii::$app->basePath . '/web/' . $path, "r");

        while (!feof($file)) {
            yield fgets($file);
        }

        fclose($file);
        return 0;
    }
}