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
class DeckCard extends AgotBase{

    const SNAP = 1;
    const DETAIL = 0;

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%deck_card}}';
    }

    public static function getCards($deck_id, $type){
        $deck_cards = self::find()->where(['deck_id' => $deck_id, 'status' => self::STATUS_ACTIVE])->all();

        $card_ids = array_filter($deck_cards, function($item){
            return $item->card_id;
        });

        $cards = Card::find()->where(['id' => $card_ids, 'status' => Card::STATUS_ACTIVE])->all();

        $card_hash = [];
        foreach ($cards as $key => $value) {
            $card_hash[$value->id] = $value;
        }

        $result = [];

        if ($type == self::DETAIL) {
            foreach ($deck_cards as $key => $value) {
                $count = $value->count;
                while($count > 0){
                    $count--;
                    $result[] = clone $card_hash[$value->card_id];
                }
            }
        }else if($type == self::SNAP){
            foreach ($deck_cards as $key => $value) {
                $result[] = [
                    'count' => $value->count,
                    'card_id' => $value->id,
                ];
            }
        }

        return $result;
    }

    public static function changeCards($deck_id, $cards){

        self::updateAll(['status' => self::STATUS_DELETED], ['deck_id' => $deck_id, 'status' => self::STATUS_ACTIVE]);

        if (empty($cards)) {
            return true;
        }

        $inserts = [];

        foreach ($cards as $card) {
            $inserts[] = [$deck_id, $card['id'], $card['count']];
        }

        //批量插入
        return self::getDb()->createCommand()->batchInsert(self::tableName(), 
            ['deck_id', 'card_id' ,'count'], $inserts)->execute();
    }



}
