<?php
namespace frontend\controllers;

use Yii;
use common\models\agot\Table;
use common\filters\PlayerActionRecorder;
use common\models\agot\PlayRecord;

/**
 * Table controller
 * date 2016-08-30
 * author wolfbian
 */
class TableController extends JsonBaseController{

    public function behaviors()
    {  
        return [  
            'access' => [  
                'class' => PlayerActionRecorder::className(),  
                'only'=>['speak', 'shuffle-card', 'move-card', 'leave-card', 'play-onto-board', 'draw-cards', 'flip-card', 'change-mark'],  
            ],
        ];  
    }  

    /**
     * @name  获取桌子列表情况
     * @method GET
     * @author wolfbian
     * @date 2016-09-07
     */
    public function actionTables()
    {
        $tables = Yii::$app->params['tables'];
        $data = [];
        foreach ($tables as $key => $value) {
            $t = new Table($value);
            $data[] =  $t->getTableInfo();
        }
        return ['code' => self::CODE_SUCCESS, 'data' => $data];
    }

    /**
     * @name  获取该局比赛玩家行动记录
     * @method GET
     * @author wolfbian
     * @date 2017-06-08
     */
    public function actionActionRecords()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);
        $records = PlayRecord::getPlayActions($table->getPlayId());
        return ['code' => self::CODE_SUCCESS, 'data' => $records];
    }

    /**
     * @name  玩家发言
     * @method POST
     * @author wolfbian
     * @date 2017-06-08
     */
    public function actionSpeak()
    {
        return ['code' => self::CODE_SUCCESS, 'data' => ""];
    }

    /**
     * @name  获取桌子详情
     * @method GET
     * @author wolfbian,chui
     * @date 2016-10-11
     */
    public function actionTable()
    {

        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);
        $info = $table->info;
        $sides = $info['side'];
        if($sides[0]['user_id'] == $userId){
            $side = 0;
            $self_side = $sides[0];
            $other_side = $sides[1];
        }else{
            $side = 1;
            $self_side = $sides[1];
            $other_side = $sides[0];
        }
        $self_side['library'] = count($self_side['library']);
        foreach ($other_side as $key => $value) {
            if (in_array($key, ['house', 'agenda'])) {
                // do nothing
            }elseif(is_array($value)){
                $other_side[$key] = count($value);
            }else{
                unset($other_side[$key]);
            }
        }

        foreach ($info['playground'] as $key => $v) {
            if (isset($v['face']) && $v['face'] == 0) {
                unset($info['playground'][$key]['card_id']);
            }
        }

        $data = [   'side' => $side,
                    'self_side' => $self_side,
                    'other_side' => $other_side,
                    'playground' => $info['playground'],
                ];

        Table::clearNeedRefresh($userId);

        return ['code' => self::CODE_SUCCESS, 'data' => $data];
    }

    /**
     * @name  洗卡
     * @method POST
     * @author wolfbian
     * @date 2016-10-04
     * @param    int            type (0：手牌，1：牌库，2：弃牌区，3：死亡牌区)
     */
    public function actionShuffleCard()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);

        $type = intval(Yii::$app->request->post("type"));
        $side = intval(Yii::$app->request->post("side"));

        $ret = $table->shuffle(['type' => $type, 'side' => $side]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => $ret[1]];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }

    /**
     * @name  卡牌移动
     * @method POST
     * @author wolfbian
     * @date 2016-10-05
     * @param    string      id   // 本场比赛，卡牌的id
     * @param    array    to
     */
    public function actionMoveCard()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);

        $id = Yii::$app->request->post("id");
        $to = Yii::$app->request->post("to");

        $ret = $table->moveCard(['id' => $id, 'to' => $to]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => []];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }

    /**
     * @name  卡牌离场
     * @method POST
     * @author wolfbian
     * @date 2016-10-16
     * @param    string      id   // 本场比赛，卡牌的id
     * @param    array    to  (0：手牌，1：牌库，2：弃牌区，3：死亡牌区，4：战略牌)
     */
    public function actionLeaveCard()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);

        $id = Yii::$app->request->post("id");
        $to = Yii::$app->request->post("to");

        $ret = $table->leaveCard(['id' => $id, 'side' => $table->getSideByUserId($userId), 'to' => $to]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => []];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }

    /**
     * @name  打入场卡牌
     * @method POST
     * @author wolfbian
     * @date 2016-10-09
     * @param    string      id   // 本场比赛，卡牌的id
     * @param    int      form // (0：手牌，1：牌库，2：弃牌区，3：死亡牌区，4：战略牌区)
     * @param    array    to
     */
    public function actionPlayOntoBoard()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);

        $id = Yii::$app->request->post("id");
        $from = intval(Yii::$app->request->post("from"));
        $to = Yii::$app->request->post("to");
        $face = Yii::$app->request->post("face");

        $ret = $table->playOntoBoard(['id' => $id, 'to' => $to, 'from' => $from, 'face' => $face, 'side' => $table->getSideByUserId($userId)]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => $ret[1]];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }

    /**
     * @name  抓牌
     * @method POST
     * @author wolfbian
     * @date 2016-10-09
     * @param    int       // 抓牌数量
     */
    public function actionDrawCards()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);

        $count = Yii::$app->request->post("count", 1);

        $ret = $table->drawCard(['count' => $count, 'side' => $table->getSideByUserId($userId)]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => []];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }

    /**
     * @name  翻转卡牌
     * @method POST
     * @author wolfbian
     * @date 2016-10-09
     * @param    string      id   // 本场比赛，卡牌的id
     * @param    integer     type // 0 横置或者竖立 1 正面或者反面
     */
    public function actionFlipCard()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);

        $id = Yii::$app->request->post("id");

        $type = Yii::$app->request->post("type", 0);
        $ret = $table->changeCardState(['id' => $id, 'type' => $type]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => $ret[1]];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }

    public function actionChangeMark(){
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $tableId = Table::getTableIdByUserId($userId);
        $table = new Table($tableId);

        $id = Yii::$app->request->post("id");
        $type = Yii::$app->request->post("type", 0);
        $operate = Yii::$app->request->post("operate", 0);
        $ret = $table->changeCardMark(['id' => $id, 'type' => $type, 'operate' => $operate]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => $ret[1]];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }


    public function actionNeedRefresh()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $ret = Table::getNeedRefresh($userId);
        return ['code' => self::CODE_SUCCESS, 'data' => $ret];
    }
    
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
    public function actionReady()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $tableId = intval(Yii::$app->request->post("id"));
        $side = intval(Yii::$app->request->post("side"));
        $deck_id = intval(Yii::$app->request->post("deck_id"));
        $game_id = intval(Yii::$app->request->post("game_id", 0));

        if (!in_array($tableId, Yii::$app->params['tables'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌号"];
        }

        if (!in_array($game_id, Yii::$app->params['games'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的游戏ID"];
        }

        if (!in_array($side, Yii::$app->params['game_sides'][$game_id])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌边"];
        }

        if (empty($deck_id)) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的牌组"];
        }

        $table = new Table($tableId);

        if( !$table->ready(['user_id' => $userId, 'game_id' => $game_id,  'side' => $side, 'deck_id' => $deck_id]) ){
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS];
    }

    /**
     * @name  取消准备
     * @method POST
     * @param    int            id   桌号
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @author wolfbian
     * @date 2016-08-31
     */
    public function actionUnReady()
    {
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $tableId = intval(Yii::$app->request->post("id"));
        $side = intval(Yii::$app->request->post("side"));

        if (!in_array($tableId, Yii::$app->params['tables'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌号"];
        }

        $table = new Table($tableId);

        if( !$table->unready(['user_id' => $userId,  'side' => $side]) ){
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS];
    }

}