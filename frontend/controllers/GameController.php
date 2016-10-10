<?php
namespace frontend\controllers;

use Yii;

/**
 * Game controller
 */
class GameController extends JsonBaseController{

    /**
     * @name  卡牌移动
     * @method POST
     * @author wolfbian
     * @date 2016-08-20
     * @param    int            card_id
     * @param    int            from
     * @param    int            to
     */
    public function actionMoveCard(){
        
    }

    /**
     * @name  洗卡
     * @method POST
     * @author wolfbian
     * @date 2016-08-20
     * @param    int            type (0：手牌，1：牌库，2：弃牌区，3：死亡牌区)
     */
    public function actionShuttleCard(){

    }

    /**
     * @name  在同区域内移动卡牌
     * @method POST
     * @author wolfbian
     * @date 2016-08-20
     * @param    array          起始坐标 {x: ***, y: ***}              
     * @param    array          终点坐标 {x: ***, y: ***}              
     */
    public function actionMoveCardInboard(){

    }

    /**
     * @name  卡牌状态改变
     * @method POST
     * @author wolfbian
     * @date 2016-08-20
     */
    public function actionChangeCardState(){

    }

}