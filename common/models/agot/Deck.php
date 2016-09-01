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
 * @property integer $user_id
 * @property integer $house
 * @property integer $agenda_id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Deck extends AgotBase{

    private $_cards = null;

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
        return '{{%deck}}';
    }

    public function getCards(){
        if ($this->_cards === null) {
            $this->_cards = DeckCard::getCards($this->id);
        }
        return $this->_cards;
    }

    public function getPlots(){
        $cards = $this->cards;
        $plots = [];
        foreach ($cards as $card) {
            if ($card->type == Card::TYPE_PLOT) {
                $plot[] = $card;
            }
        }
        return $plot;
    }

    public function getNormalCards(){
        $cards = $this->cards;
        $normal = [];
        foreach ($cards as $card) {
            if ($card->type != Card::TYPE_PLOT) {
                $normal[] = $card;
            }
        }
        return $normal;
    }


    public static function findById($id){
        return Deck::find()->where(['id' => $info['side'][$value]['deck_id']], 'status' => Deck::STATUS_ACTIVE)->one();
    }



}
