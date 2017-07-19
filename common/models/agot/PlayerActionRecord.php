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
        'table/change-mark' => '改变标记',
        'table/random-discard' => '弃牌',
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
            if (isset($params['face']) && $params['face'] == 0) {
                return $userName . "将一张牌反面朝上地从" . Yii::$app->params['type2chinese'][$params['from']] . "移到场上";
            }
            return $userName . "将[" . $card->name . "]从" . Yii::$app->params['type2chinese'][$params['from']] . "移到场上";
        } else if ($action == 'table/flip-card') {
            $card = Card::find()->where(['id' => $cards[$params['id']], 'status' => Card::STATUS_ACTIVE])->one();
            if (isset($params['type']) && $params['type'] == 1) {
                return $userName . "翻面了[" . $card->name . "]";
            }elseif (isset($params['type']) && $params['type'] == 2) {
                return $userName . "选中了[" . $card->name . "]";
            }
            return $userName . "转动了[" . $card->name . "]";
        } else if ($action == 'table/speak') {
            return $userName . " ： " . $params['content'];
        } else if ($action == 'table/draw-cards') {
            return $userName . "抓了" . $params['count'] . "张牌";
        } else if ($action == 'table/change-mark') {
            $card = Card::find()->where(['id' => $cards[$params['id']], 'status' => Card::STATUS_ACTIVE])->one();
            $operateList = ['1' => '增加', '2' => '减少'];
            $typeList = ['1' => '金币', '2' => '权利标记', '3' => '能力标记'];
            $operate = $operateList[$params['operate']];
            $type = $typeList[$params['type']];
            return $userName . "给[" . $card->name . "]" . $operate . "1个" . $type;
        } else if ($action == 'table/random-discard') {
            return $userName . "随机弃掉一张手牌";
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
