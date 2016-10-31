<?php
namespace frontend\controllers;

use Yii;
use common\models\agot\Card;

/**
 * Card controller
 * date 2016-08-24
 * author wolfbian
 */
class CardController extends JsonBaseController{

    /**
     * @name  查询卡牌
     * @method GET
     * @param  array            condition      条件数组
     * @author wolfbian
     * @date 2016-08-24
     */
    public function actionCards(){

        $condition = Yii::$app->request->get("condition");

        if (empty($condition)) {
        	$condition = [];
        }

        $cards = Card::find()->where($condition)->all();

        return ['code' => self::CODE_SUCCESS, 'data' => $cards];

    }

}