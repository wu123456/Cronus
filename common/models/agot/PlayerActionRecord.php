<?php
namespace common\models\agot;

use Yii;
use yii\db\ActiveRecord;

/**
 * author wolfbian
 * date 2017-06-07
 *
 * PlayerActionRecord model
 *
 * @property integer $id
 * @property integer $play_id
 * @property integer $user_id
 * @property string $action
 * @property string $side
 * @property string $params
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class PlayerActionRecord extends AgotBase
{

    static $action2text = [
        'table/shuffle-card' => '洗牌',
        'table/move-card' => '卡牌移动',
        'table/leave-card' => '卡牌离场',
        'table/play-onto-board' => '打入场卡牌',
        'table/draw-cards' => '抓牌',
        'table/flip-card' => '翻转卡牌',
        'table/speak' => '发言',
    ];

    public static function tableName()
    {
        return '{{%player_action_record}}';
    }

    public static function translateRecord($userName, $action, $params, $cards)
    {
        if ($action == 'table/shuffle-card') {
            return $userName . "将" . Yii::$app->params['type2chinese'][$params['type']] . "进行了洗牌";
        } else if($action == 'table/leave-card') {
            $card = Card::find()->where(['id' => $cards[$params['id']], 'status' => Card::STATUS_ACTIVE])->one();
            return $userName . "将[" . $card->name . "]从场上移到" . Yii::$app->params['type2chinese'][$params['to']];
        } else if ($action == 'table/play-onto-board') {
            $card = Card::find()->where(['id' => $cards[$params['id']], 'status' => Card::STATUS_ACTIVE])->one();
            return $userName . "将[" . $card->name . "]从" . Yii::$app->params['type2chinese'][$params['from']] . "移到场上";
        } else if ($action == 'table/flip-card') {
            $card = Card::find()->where(['id' => $cards[$params['id']], 'status' => Card::STATUS_ACTIVE])->one();
            return $userName . "转动了[" . $card->name . "]";
        } else if ($action == 'table/speak') {
            return $userName . " ： " . $params['content'];
        }
        return false;
    }

    public static function getTableActions($playId)
    {
        $sourceRecords = self::find()->where(['play_id' => $playId, 'status' => self::STATUS_ACTIVE])
                    ->all();
        
        return $sourceRecords;
    }

}
