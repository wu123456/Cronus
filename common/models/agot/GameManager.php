<?php
namespace common\models\agot;

use Yii;
use yii\base\Model;

/**
 * author wolfbian
 * date 2016-08-30
 *
 * GameManager model
 */
class GameManager extends Model{

    private $game_id;

    public function __construct($config) {
        $this->game_id = $config['game_id'];
    }

    public function getGameCache(){
        return json_decode(Yii::$app->redis->get($this->game_id), true);
    }

    public function saveGameCache($c){
        return Yii::$app->redis->set($this->game_id, json_encode($c));
    }

    public function initGame($params){
        $c = [
            'A' => [
                'deck' => $d,
                'plot' => $d,
                'hand' => $d,
                'dead' => $d,
                'discard' => $d,
            ],
            'B' => [
                'deck' => $d,
                'plot' => $d,
                'hand' => $d,
                'dead' => $d,
                'discard' => $d,
            ],
            'playground' => [
            ],
        ];
        return $this->saveGameCache($c);
    }

    public function moveCard(){

    }

}
