<?php
namespace console\controllers;

use Yii;
use yii\db\Migration;
use yii\db\mysql\Schema;

/**
 * 调用方式：./yii mysql-init/index 
 */
class MysqlInitController extends \yii\console\Controller {


    public function actionIndex(){
        $mir = new Migration();  

        $tables = [
            // 'tb_card' => [
            //     'id' => 'pk', 
            //     'type' => Schema::TYPE_SMALLINT, 
            //     'status' => Schema::TYPE_SMALLINT,
            //     'create_time' => Schema::TYPE_TIMESTAMP ." NOT NULL DEFAULT CURRENT_TIMESTAMP",
            //     'update_time' => Schema::TYPE_TIMESTAMP ." NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            // ],

            // 'tb_deck' => [
            //     'id' => 'pk', 
            //     'user_id' => Schema::TYPE_INTEGER, 
            //     'house' => Schema::TYPE_INTEGER, 
            //     'agenda_id' => Schema::TYPE_INTEGER, 
            //     'status' => Schema::TYPE_SMALLINT, 
            //     'create_time' => Schema::TYPE_TIMESTAMP ." NOT NULL DEFAULT CURRENT_TIMESTAMP",
            //     'update_time' => Schema::TYPE_TIMESTAMP ." NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            // ],

            'tb_deck_card' => [
                'id' => 'pk', 
                'deck_id' => Schema::TYPE_INTEGER, 
                'card_id' => Schema::TYPE_INTEGER, 
                'count' => Schema::TYPE_INTEGER, 
                'status' => Schema::TYPE_SMALLINT, 
                'create_time' => Schema::TYPE_TIMESTAMP ." NOT NULL DEFAULT CURRENT_TIMESTAMP",
                'update_time' => Schema::TYPE_TIMESTAMP ." NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            ],


        ];

        foreach ($tables as $tableName => $tableColumns) {
            $mir->createTable($tableName, $tableColumns);  
        }

        
    }

}