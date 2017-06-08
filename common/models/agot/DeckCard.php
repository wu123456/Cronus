<?php
namespace common\models\agot;

use Yii;
use yii\db\ActiveRecord;

/**
 * author wolfbian
 * date 2016-08-24
 *
 * Deck model
 *
 * @property integer $id
 * @property integer $deck_id
 * @property integer $card_id
 * @property integer $count
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class DeckCard extends AgotBase
{

    const SNAP = 1;
    const DETAIL = 0;

    public static function tableName()
    {
        return '{{%deck_card}}';
    }

    public static function getCards($deckId, $type)
    {
        $deckCards = self::find()->where(['deck_id' => $deckId, 'status' => self::STATUS_ACTIVE])->all();

        $cardIds = [];

        foreach ($deckCards as $key => $value) {
            $cardIds[] = $value->card_id;
        }

        $cards = Card::find()->where(['id' => $cardIds, 'status' => Card::STATUS_ACTIVE])->all();

        $cardHash = [];
        foreach ($cards as $key => $value) {
            $cardHash[$value->id] = $value;
        }

        $result = [];

        if ($type == self::DETAIL) {
            foreach ($deckCards as $key => $value) {
                $count = $value->count;
                while($count > 0){
                    $count--;
                    $result[] = clone $cardHash[$value->card_id];
                }
            }
        }else if($type == self::SNAP){
            foreach ($deckCards as $key => $value) {
                $result[] = [
                    'count' => $value->count,
                    'card_id' => $value->card_id,
                ];
            }
        }

        return $result;
    }

    public static function changeCards($deckId, $cards)
    {

        self::updateAll(['status' => self::STATUS_DELETED], ['deck_id' => $deckId, 'status' => self::STATUS_ACTIVE]);

        if (empty($cards)) {
            return true;
        }

        $inserts = [];

        foreach ($cards as $card) {
            $inserts[] = [$deckId, $card['id'], $card['count'], self::STATUS_ACTIVE];
        }

        //批量插入
        return self::getDb()->createCommand()->batchInsert(self::tableName(), 
            ['deck_id', 'card_id' ,'count', 'status'], $inserts)->execute();
    }



}
