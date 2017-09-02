<?php
namespace common\helpers;
use Yii;

class GlobalHelper
{

    /**
     * connect db 重新连接数据库
     * @param string $db
     * @return \yii\db\Connection 返回数据库操作句柄
     */
    public static function connectDb($db = 'db'){
        $db_handle  = Yii::$app->$db;
        if(empty($db_handle)) {
            return false;
        }

        try{
            $db_handle->createCommand("select 1")->queryOne();
        }catch (\Exception $e){
            $db_handle->close();
            $db_handle->open();
        }
    }
}