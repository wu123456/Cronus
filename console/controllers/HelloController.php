<?php
namespace console\controllers;

use Yii;
use yii\db\Query;
use common\models\agot\Table;

/**
 * 调用方式：./yii hello/index 
 */
class HelloController extends \yii\console\Controller {

    public function actionIndex() {
        $tables = Yii::$app->params['tables'];
        $game_sides = Yii::$app->params['game_sides'];
        foreach ($tables as $key => $value) {
            $table = new Table($value);
            echo $value . "\t";
            var_dump($table->start($game_sides));
        }
    }

}