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
     * @param    int            game_id  游戏id(默认0，代表冰火)
     * @author wolfbian
     * @date 2016-08-31
     */
    public function unready($params){
        $info = $this->info;

        if (!isset($info['game_id'])) {
            return false;
        }

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
    public function start(){
        $info = $this->info;

        if (isset($info['start']) && $info['start']) {
            return false;
        }

        $info['start'] = true;

        if (!isset($info['side'])) {
            return false;
        } 

        if (!isset($info['side'][$params['side']])) {
            return false;
        }

        if (isset($info['side'][$params['side']]) != $params['user_id']) {
            return false;
        }

        unset($info['side'][$params['side']]);

        return $this->info = $info;
    }

}
