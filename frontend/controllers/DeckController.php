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
    }



}