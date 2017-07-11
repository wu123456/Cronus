<?php
namespace common\models\agot;

use Yii;
use yii\db\ActiveRecord;
use common\models\User;

/**
 * author wolfbian
 * date 2017-06-08
 *
 * PlayRecord model
 *
 * @property integer $id
 * @property integer $table_id
 * @property string $initial_situation
 * @property string $cards
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class PlayRecord extends AgotBase
{

    public static function tableName()
    {
        return '{{%play_record}}';
    }

    public static function record($tableId, $initialSituation, $cards)
    {
        $playRecord = new self([
            'table_id' => $tableId,
            'initial_situation' => json_encode($initialSituation),
            'cards' => json_encode($cards),
        ]);

        if ($playRecord->save()) {
            return $playRecord->id;
        }

        return false;
    }

    public static function getPlayActions($playId)
    {
        $playRecord = self::find()->where(['id' => $playId, 'status' => self::STATUS_ACTIVE])->one();
        if (empty($playRecord)) {
            return [];
        }
        $cards = json_decode($playRecord->cards, true);
        $sourceActions = PlayerActionRecord::getTableActions($playId);
        $actions = [];
        foreach ($sourceActions as $v) {
            $user = User::find()->where(['id' => $v->user_id])->one();
            $a = PlayerActionRecord::translateRecord($user->username, $v->action, json_decode($v->params, true), $cards);
            if ($a) {
                // 前面 yyyy-mm-dd 以及空格占11位
                $actions[] = substr($v->create_time, 11) . " : " . $a;
            }
        }
        $actions = array_reverse($actions);
        return $actions;
    }

}
