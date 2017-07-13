<?php
namespace common\models\agot;

use Yii;
use yii\base\Model;

/**
 * author wolfbian
 * date 2016-08-31
 *
 * Table model
 */
class Table extends Model
{

    private $_table_id;

    public static function getTableIdByUserId($userId)
    {
        return Yii::$app->redis->get("user::" . $userId);
    }

    public function __construct($tableId) 
    {
        $this->_table_id = $tableId;
    }

    public function getInfo()
    {
        $ret = json_decode(Yii::$app->redis->get("table" . $this->_table_id), true);
        if (empty($ret)) {
            $ret = [];
        }
        return $ret;
    }

    public function setInfo($info)
    {
        return Yii::$app->redis->set("table" . $this->_table_id, json_encode($info));
    }

    public function getPlayId()
    {
        return isset($this->info['play_id']) ? $this->info['play_id'] : 0;
    }

    /**
     * @name  准备
     * @param    int            user_id   玩家id
     * @param    int            deck_id   牌组id
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @param    int            game_id  游戏id(默认0，代表冰火)
     * @author wolfbian
     * @date 2016-08-31
     */
    public function ready($params)
    {

        // 如果用户已经坐下了，不能再在其他位置坐下
        if (Yii::$app->redis->get("user::" . $params['user_id'])) {
            return false;
        }

        $info = $this->info;

        if (isset($info['game_id'])) {
            if ($info['game_id'] != $params['game_id']) {
                return false;
            }
        }else {
            $info['game_id'] = $params['game_id'];
        }

        if (isset($info['side'])) {
            if(isset($info['side'][$params['side']])){
                return false;
            }else{
                $info['side'][$params['side']] = [
                    'user_id' => $params['user_id'],
                    'deck_id' => $params['deck_id'] 
                ];
            }
        } else {
            $info['side'] = [];
            $info['side'][$params['side']] = [
                'user_id' => $params['user_id'],
                'deck_id' => $params['deck_id'] 
            ];
        }

        Yii::$app->redis->set("user::" . $params['user_id'], $this->_table_id);

        return $this->info = $info;
    }

    /**
     * @name  取消准备
     * @param    int            user_id   玩家id
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @author wolfbian
     * @date 2016-08-31
     */
    public function unready($params)
    {
        $info = $this->info;

        if (!isset($info['side'])) {
            return false;
        } 

        if (!isset($info['side'][$params['side']])) {
            return false;
        }

        if (!isset($info['side'][$params['side']]['user_id'])) {
            return false;
        }

        if ($info['side'][$params['side']]['user_id'] != $params['user_id']) {
            return false;
        }

        unset($info['side'][$params['side']]);

        Yii::$app->redis->del("user::" . $params['user_id']);

        return $this->info = $info;
    }

    /**
     * @name  开始游戏，初始化战场
     * @author wolfbian
     * @date 2016-08-31
     */
    public function start($gameSides)
    {
        $info = $this->info;

        if (isset($info['start']) && $info['start']) {
            return false;
        }

        $info['start'] = true;

        if (!isset($info['side'])) {
            return false;
        } 

        if (!isset($gameSides[$info['game_id']])) {
            return false;
        } 

        $cards = [];

        foreach ($gameSides[$info['game_id']] as $value) {
            if (!isset($info['side'][$value])) {
                return false;
            }
            $deck = Deck::findById($info['side'][$value]['deck_id']);
            if (empty($deck)) {
                return false;
            }

            $plot_temp = $deck->getPlots();
            $plot = [];
            foreach ($plot_temp as $key => $p) {
                $id = 'p' . $value . $key;
                $plot[$id] = ['id' => $id, 'card_id' => $p];
                $cards[$id] = $p;
            }
            $info['side'][$value]['plot'] = $plot;
            $info['side'][$value]['discard'] =  [];
            $info['side'][$value]['dead'] =  [];
            // 家族牌
            $houseCardId = $deck->getHouse();
            $houseId = 'h' . $value . $houseCardId;
            $info['side'][$value]['house'] = ['id' => $houseId, 'card_id' => $houseCardId];
            $cards[$houseId] = $houseCardId;
            // 议政牌
            $agendaCardId = $deck->getAgenda();
            $agendaId = 'a' . $value . $agendaCardId;
            $info['side'][$value]['agenda'] = ['id' => $agendaId, 'card_id' => $agendaCardId];
            $cards[$agendaId] = $agendaCardId;

            $normal_temp = $deck->getNormalCards();
            $normal = [];
            foreach ($normal_temp as $k => $v) {
                $id = 'c' . $value . $k;
                $normal[] = ['id' => $id, 'card_id' => $v];
                $cards[$id] = $v;
            }
            list($hands, $library) = Table::shuffleAndDivideCards($normal);

            $info['side'][$value]['hands'] = [];
            foreach ($hands as $hand) {
                $info['side'][$value]['hands'][$hand['id']] = $hand;
            }

            $info['side'][$value]['library'] = [];
            foreach ($library as $l) {
                $info['side'][$value]['library'][$l['id']] = $l;
            }

            Yii::$app->redis->set("user::" . $info['side'][$value]['user_id'], $this->_table_id);
        }

        $info['playground'] = [];

        $playId = PlayRecord::record($this->_table_id, $info, $cards);

        $info['play_id'] = $playId;


        return $this->info = $info;
    }

    public function getTableInfo()
    {
        $info = $this->info;

        $info['name'] = "第".$this->_table_id."桌";
        $info['id'] = $this->_table_id;

        return $info;
    }

    // type , side
    public function shuffle($params)
    {
        $info = $this->info;
        // type (0：手牌，1：牌库，2：弃牌区，3：死亡牌区， 4：战略牌)
        $type2name = Yii::$app->params['type2name'];
        if (!isset($type2name[$params['type']])) {
            return [false, '不存在的类型'];
        }
        $name = $type2name[$params['type']];

        $cards = $info['side'][$params['side']][$name];
        shuffle($cards);
        $info['side'][$params['side']][$name] = $cards;
        $ret = $this->setInfo($info);
        return [$ret, $cards];
    }

    /**
     * @name  移动卡牌
     * @param    int            id   卡牌的id
     * @param    array          to
     * @author caohui
     * @date 2016-10-08
     */
    public function moveCard($params)
    {
        $id = $params['id'];
        $to = $params['to'];
        $info = $this->info;
        if (empty($info['playground'][$id])) {
            return [false, '该牌不在场上'];
        }
        $info['playground'][$id]['x'] = $to['x'];
        $info['playground'][$id]['y'] = $to['y'];
        $ret = $this->setInfo($info);
        return [$ret];
    }

    /**
     * @name  卡牌离场
     * @param    string             id   卡牌的id
     * @param    int                to
     * @author wolfbian
     * @date 2016-10-16
     */
    public function leaveCard($params)
    {
        $id = $params['id'];
        $to = $params['to'];
        $info = $this->info;
        $type2name = Yii::$app->params['type2name'];
        if (!isset($type2name[$to])) {
            return [false, '不存在的类型'];
        }
        $name = $type2name[$to];

        if (empty($info['playground'][$id])) {
            return [false, '该牌不在场上'];
        }

        $cards = $info['side'][$params['side']][$name];
        $cards[$id] = $info['playground'][$id];
        unset($info['playground'][$id]);
        $info['side'][$params['side']][$name] = $cards;
        $ret = $this->setInfo($info);
        return [$ret, $cards];
    }

    /**
     * @name  卡牌进场
     * @param    int            id   卡牌的id
     * @param    int            side    
     * @param    int            form // (0：手牌，1：牌库，2：弃牌区，3：死亡牌区，4：战略牌库)
     * @param    array          to
     * @author wolfbian
     * @date 2016-10-09
     */
    public function playOntoBoard($params)
    {
        $id = $params['id'];
        $from = $params['from'];
        $to = $params['to'];
        $side = $params['side'];
        $face = intval($params['face']);

        $info = $this->info;
        $type2name = Yii::$app->params['type2name'];
        if (!isset($type2name[$from])) {
            return [false, '不存在的类型'];
        }
        $name = $type2name[$from];
        $cards = $info['side'][$side][$name];
        $card = $cards[$id];
        unset($cards[$id]);
        $info['side'][$side][$name] = $cards;
        $info['playground'][$id] = $card;
        $info['playground'][$id]['x'] = $to['x'];
        $info['playground'][$id]['y'] = $to['y'];
        $info['playground'][$id]['face'] = $face;
        $ret = $this->setInfo($info);
        return [$ret, $info['playground'][$id]['card_id']];
    }

    public function changeCardState($params)
    {
        $id = $params['id'];
        $type = $params['type'];
        $typeList = ['0' => 'stand', '1' => 'face', '2' => 'nofocus'];
        if (!isset($typeList[$type])) {
            return [false, '不存在的类型'];
        }
        $info = $this->info;
        $type = $typeList[$type];
        // if no target state, just change state follow the rule : 0 => 1, 1 => 0
        // if no old state, default value is 1
        if(isset($params['target_state'])){
            $info['playground'][$id][$type] = $params['target_state'];
        }else{
            $state = isset($info['playground'][$id][$type]) ? $info['playground'][$id][$type] : 1;
            $info['playground'][$id][$type] = ($state + 1) % 2;
        }
        $ret = $this->setInfo($info);
        return [$ret, $info['playground'][$id]['card_id']];
    }

    public function changeCardMark($params)
    {
        $id = $params['id'];
        $type = $params['type'];
        $typeList = ['1' => 'gold', '2' => 'power', '3' => 'strength'];
        if (!isset($typeList[$type])) {
            return [false, '不存在的类型'];
        }
        $operate = $params['operate'];
        // 1为加， 2为减
        if ($operate != 1 && $operate != 2) {
            return [false, '不存在的操作'];
        }
        $info = $this->info;
        $type = $typeList[$type];
        
        $info['playground'][$id][$type] = isset($info['playground'][$id][$type]) ? $info['playground'][$id][$type] : 0;
        if ($operate == 1) {
            $info['playground'][$id][$type]++;
        }else{
            $info['playground'][$id][$type]--;
        }
        
        $ret = $this->setInfo($info);
        return [$ret, $info['playground'][$id][$type]];
    }

    public function getSideByUserId($userId)
    {
        $info = $this->info;
        $sides = $info['side'];
        foreach ($sides as $key => $side) {
            if ($side['user_id'] == $userId) {
                return $key;
            }
        }
        return -1;
    }

    public function drawCard($params)
    {
        $count = $params['count'];
        $side = $params['side'];
        $info = $this->info;
        $fromCards = $info['side'][$side]['library'];
        $card = array_pop($fromCards);
        $toCards = $info['side'][$side]['hands'];
        $toCards[$card['id']] = $card;
        $info['side'][$side]['library'] = $fromCards;
        $info['side'][$side]['hands'] = $toCards;
        $ret = $this->setInfo($info);
        return [$ret];
    }

    public function getOpUserId($userId)
    {
        $info = $this->info;
        $sides = $info['side'];
        foreach ($sides as $key => $side) {
            if ($side['user_id'] != $userId) {
                return $side['user_id'];
            }
        }
        return -1;
    }

    public function noticeOp($userId)
    {
        $opUserId = $this->getOpUserId($userId);
        self::setNeedRefresh($opUserId);
    }

    public function randomDiscard($params)
    {
        $side = $params['side'];
        $info = $this->info;

        $hands = $info['side'][$side]['hands'];
        $discard = $info['side'][$side]['discard'];
        if (empty($hands)) {
            return [false, '无手牌'];
        }
        $randomCardId = array_rand($hands);
        $discard[$randomCardId] = $hands[$randomCardId];
        unset($hands[$randomCardId]);
        $info['side'][$side]['hands'] = $hands;
        $info['side'][$side]['discard'] = $discard;
        $ret = $this->setInfo($info);
        return [$ret];
    }

    

    public static function shuffleAndDivideCards($cards, $l = 7)
    {
        shuffle($cards);
        return [array_slice($cards, 0, $l), array_slice($cards, $l)];
    }

    public static function getNeedRefresh($userId)
    {
        return Yii::$app->redis->get("need_refresh_" . $userId);
    }

    public static function setNeedRefresh($userId)
    {
        return Yii::$app->redis->set("need_refresh_" . $userId, 1);
    }

    public static function clearNeedRefresh($userId)
    {
        return Yii::$app->redis->del("need_refresh_" . $userId);
    }

}
