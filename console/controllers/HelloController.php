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
        foreach ($tables as $key => $value) {
            $table = new Table($value);
            var_dump($table->start());
        }
    }

}