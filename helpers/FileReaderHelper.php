<?php

namespace app\helpers;

use Yii;

class FileReaderHelper
{
    /**
     * @param string $path
     * @return \Generator|int
     */
    public function getNextLine(string $path): int|\Generator
    {
        $file = fopen(Yii::$app->basePath . '/web/' . $path, "r");

        while (!feof($file)) {
            yield fgets($file);
        }

        fclose($file);
        return 0;
    }
}