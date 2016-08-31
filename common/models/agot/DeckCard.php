<?php
namespace common\models;

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

    public static function getCards($deck_id){
        $deck_cards = self::find()->where(['deck_id' => $deck_id, 'status' => self::STATUS_ACTIVE])->all();

        $card_ids = array_filter($deck_cards, function($item){
            return $item->card_id;
        });

        $cards = Card::find()->where(['id' => $card_ids, 'status' => Card::STATUS_ACTIVE)->all();

        $card_hash = [];
        foreach ($cards as $key => $value) {
            $card_hash[$value->id] = $value;
        }

        $result = [];

        foreach ($deck_cards as $key => $value) {
            $result[] = [
                'count' => $value->count,
                'card' => $card_hash[$value->card_id]
            ];
        }

        return $result;
    }



}
