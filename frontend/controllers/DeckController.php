<?php
namespace frontend\controllers;

use Yii;
use common\models\agot\Deck;
use common\models\agot\Card;

/**
 * Deck controller
 * date 2016-08-24
 * author wolfbian
 */
class DeckController extends JsonBaseController{

    /**
     * @name  获取牌组列表
     * @method GET
     * @author wolfbian
     * @date 2016-08-24
     */
    public function actionDecks(){
    	$user_id = Yii::$app->user->id;
    	if (empty($user_id)) {
    		return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
    	}

        return ['code' => self::CODE_SUCCESS, 'data' => Deck::getDeckArray($user_id)];
    }

    /**
     * @name  获取牌组
     * @method GET
     * @param    int            id 牌组id
     * @author wolfbian
     * @date 2016-09-01
     */
    public function actionDeckDetail(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $id = intval(Yii::$app->request->get("id"));

        // $deck = Deck::find()->where(['user_id' => $user_id, 'id' => $id, 'status' => Deck::STATUS_ACTIVE])->one();
        $deck = Deck::getDeck($id, $user_id);

        if (empty($deck)) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "无权限"];
        }

        return ['code' => self::CODE_SUCCESS, 'data' => $deck->getDeckDetails()];
    }


    /**
     * @name  删除牌组
     * @method POST
     * @param    int            id 牌组id
     * @author wolfbian
     * @date 2016-09-01
     */
    public function actionDeleteDeck(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $id = intval(Yii::$app->request->post("id"));

        $ret = Deck::deleteDeck($id, $user_id);

        if (!$ret) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS];
    }

    /**
     * @name  创建牌组
     * @method POST
     * @param    int            house   家族
     * @param    int            agenda   议政
     * @param    string         name     牌组名称
     * @param    int            game_id  游戏id(默认0，代表冰火)
     * @author wolfbian
     * @date 2016-09-01
     */
    public function actionCreateDeck(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $house = intval(Yii::$app->request->post("house"));
        $agenda = intval(Yii::$app->request->post("agenda"));
        $name = (Yii::$app->request->post("name"));
        $game_id = intval(Yii::$app->request->post("game_id", 0));

        $deck_id = Deck::createDeck(['house' => $house, 'agenda' => $agenda, 'game_id' => $game_id, 'user_id' => $user_id, 'name' => $name]);

        if (!$deck_id) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS, 'data' => $deck_id];
    }


    /**
     * @name  修改牌组
     * @method POST
     * @param    int            id       牌组id
     * @param    int            house    家族（可选）
     * @param    int            agenda   议政（可选）
     * @param    string         name     牌组名称（可选）
     * @param    array          cards    牌（可选）
     * @author wolfbian
     * @date 2016-09-01
     */
    public function actionModifyDeck(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $id = intval(Yii::$app->request->post("id", -1));
        $house = intval(Yii::$app->request->post("house", -1));
        $agenda = intval(Yii::$app->request->post("agenda", -1));
        $name = (Yii::$app->request->post("name", -1));
        $cards = Yii::$app->request->post("cards", -1);

        $deck = Deck::getDeck($id, $user_id);

        if (!$deck) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "无权限"];
        }

        $basic_change = [];
        if ($house != -1) {
            $basic_change['house'] = $house;
        }
        if ($agenda != -1) {
            $basic_change['agenda'] = $agenda;
        }
        if ($name != -1) {
            $basic_change['name'] = $name;
        }

        $ret1 = true;
        $ret2 = true;
        if ($name != -1 || $agenda != -1 || $house != -1) {
            $ret1 = $deck->changeBasicInfo($basic_change);
        }

        if ($cards != -1) {
            $ret2 = $deck->changeCards($cards);
        }

        if (!$ret1 || !$ret2) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS, 'data' => $id];
    }



}