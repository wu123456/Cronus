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
class Table extends Model{

    private $_table_id;

    public static function getTableIdByUserId($user_id){
        return 1;
    }

    public function __construct($table_id) {
        $this->_table_id = $table_id;
    }

    public function getInfo(){
        $ret = json_decode(Yii::$app->redis->get("table" . $this->_table_id), true);
        if (empty($ret)) {
            $ret = [];
        }
        return $ret;
    }

    public function setInfo($info){
        return Yii::$app->redis->set("table" . $this->_table_id, json_encode($info));
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
    public function ready($params){
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

        return $this->info = $info;
    }

    /**
     * @name  取消准备
     * @param    int            user_id   玩家id
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @author wolfbian
     * @date 2016-08-31
     */
    public function unready($params){
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

        return $this->info = $info;
    }

    /**
     * @name  开始游戏，初始化战场
     * @author wolfbian
     * @date 2016-08-31
     */
    public function start($game_sides){
        $info = $this->info;

        if (isset($info['start']) && $info['start']) {
            return false;
        }

        $info['start'] = true;

        if (!isset($info['side'])) {
            return false;
        } 

        if (!isset($game_sides[$info['game_id']])) {
            return false;
        } 

        foreach ($game_sides[$info['game_id']] as $value) {
            if (!isset($info['side'][$value])) {
                return false;
            }
            $deck = Deck::findById($info['side'][$value]['deck_id']);
            if (empty($deck)) {
                return false;
            }

            $info['side'][$value]['plots'] =  $deck->getPlots();
            $info['side'][$value]['discard'] =  [];
            $info['side'][$value]['dead'] =  [];

            $normal_temp = $deck->getNormalCards();
            $normal = [];
            foreach ($normal_temp as $k => $v) {
                $normal[] = ['id' => 'c' . $value . $k, 'card_id' => $v];
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
        }

        $info['playground'] = [];

        return $this->info = $info;
    }

    public function getTableInfo(){
        $info = $this->info;

        $info['name'] = "第".$this->_table_id."桌";
        $info['id'] = $this->_table_id;

        return $info;
    }

    // type , side
    public function shuffle($params){
        $info = $this->info;
        // type (0：手牌，1：牌库，2：弃牌区，3：死亡牌区， 4：战略牌)
        $type2name = ['0' => 'hands', '1' => 'library', '2' => 'discard' , '3' => 'dead', '4' => 'plot'];
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
    public function moveCard($params){
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
    public function leaveCard($params){
        $id = $params['id'];
        $to = $params['to'];
        $info = $this->info;

        $type2name = ['0' => 'hands', '1' => 'library', '2' => 'discard' , '3' => 'dead', '4' => 'plot'];
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
     * @param    int            form // (0：手牌，1：牌库，2：弃牌区，3：死亡牌区)
     * @param    array          to
     * @author wolfbian
     * @date 2016-10-09
     */
    public function playOntoBoard($params){
        $id = $params['id'];
        $from = $params['from'];
        $to = $params['to'];
        $side = $params['side'];

        $info = $this->info;

        $type2name = ['0' => 'hands', '1' => 'library', '2' => 'discard' , '3' => 'dead'];
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
        $ret = $this->setInfo($info);
        return [$ret];
    }

    public function changeCardState($params){
        $id = $params['id'];
        $type = $params['type'];
        $info = $this->info;
        // if no target state, just change state follow the rule : 0 => 1, 1 => 0
        // if no old state, default value is 1
        if(isset($params['target_state'])){
            $info['playground'][$id][$type] = $params['target_state'];
        }else{
            $state = isset($info['playground'][$id][$type]) ? $info['playground'][$id][$type] : 1;
            $info['playground'][$id][$type] = ($state + 1) % 2;
        }
        $ret = $this->setInfo($info);
        return [$ret];
    }

    public function getSideByUserId($user_id){
        $info = $this->info;
        $sides = $info['side'];
        foreach ($sides as $key => $side) {
            if ($side['user_id'] == $user_id) {
                return $key;
            }
        }
        return -1;
    }

    

    public static function shuffleAndDivideCards($cards, $l = 7){
        shuffle($cards);
        return [array_slice($cards, 0, $l), array_slice($cards, $l)];
    }

}
