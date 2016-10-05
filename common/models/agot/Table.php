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
        return 4;
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
     * @param    int            deck_id   玩家id
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
            list($info['side'][$value]['hands'], $info['side'][$value]['library']) = Table::shuffleAndDivideCards($normal);
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
        // type (0：手牌，1：牌库，2：弃牌区，3：死亡牌区)
        $type2name = ['0' => 'hands', '1' => 'library', '2' => 'discard' , '3' => 'dead'];
        if (isset($type2name[$params['type']])) {
            return [false, '不存在的类型'];
        }
        $name = $type2name[$params['type']];

        $cards = $info['side'][$params['side']][$name];
        shuffle($cards);
        $info['side'][$params['side']][$name] = $cards;

        return [true, $cards];

    }

    public static function shuffleAndDivideCards($cards, $l = 7){
        shuffle($cards);
        return [array_slice($cards, 0, $l), array_slice($cards, $l)];
    }

}
