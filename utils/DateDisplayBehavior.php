<?php
namespace app\utils;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/*
 * Устанавливает отображение даты по заданному часовоу поясу
 */
class DateDisplayBehavior extends Behavior
{
    public $data;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }

    public function afterFind()
    {
        $time = strtotime($this->data->datetime.' UTC');
        $this->data->datetime = date("Y-m-d H:i:s", $time);
    }
}