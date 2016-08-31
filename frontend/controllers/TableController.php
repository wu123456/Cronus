<?php
namespace frontend\controllers;

use Yii;
use common\models\agot\Table;

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
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @param    int            deck_id  使用的牌组id
     * @param    int            game_id  游戏id(默认0，代表冰火)
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
        $deck_id = intval(Yii::$app->request->post("deck_id"));
        $game_id = intval(Yii::$app->request->post("game_id", 0));

        if (!in_array($table_id, Yii::$app->params['tables'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌号"];
        }

        if (!in_array($game_id, Yii::$app->params['games'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的游戏ID"];
        }

        if (!in_array($side, Yii::$app->params['game_sides'][$game_id])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌边"];
        }

        $table = new Table($table_id);

        if( !$table->ready(['user_id' => $user_id, 'game_id' => $game_id,  'side' => $side, 'deck_id' => $deck_id]) ){
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS];
    }

    /**
     * @name  取消准备
     * @method POST
     * @param    int            id   桌号
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @param    int            game_id  游戏id(默认0，代表冰火)
     * @author wolfbian
     * @date 2016-08-31
     */
    public function actionUnReady(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $table_id = intval(Yii::$app->request->post("id"));
        $side = intval(Yii::$app->request->post("side"));
        $game_id = intval(Yii::$app->request->post("game_id", 0));

        if (!in_array($table_id, Yii::$app->params['tables'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌号"];
        }

        if (!in_array($game_id, Yii::$app->params['games'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的游戏ID"];
        }

        if (!in_array($side, Yii::$app->params['game_sides'][$game_id])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌边"];
        }

        $table = new Table($table_id);

        if( !$table->unready(['user_id' => $user_id, 'game_id' => $game_id,  'side' => $side]) ){
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS];
    }



}