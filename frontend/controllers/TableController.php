<?php
namespace frontend\controllers;

use Yii;

/**
 * Table controller
 * date 2016-08-30
 * author wolfbian
 */
class TableController extends JsonBaseController{

    
    /**
     * @name  准备
     * @method POST
     * @param    int            id   桌号
     * @param    int            side 在桌子的哪一边 0:A 1:B 2:C
     * @author wolfbian
     * @date 2016-08-30
     */
    public function actionReady(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $table_id = intval(Yii::$app->request->post("id"));
        $side = intval(Yii::$app->request->post("side"));
    }



}