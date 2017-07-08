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

    const TYPE_PUBLIC = 0;
    const TYPE_PRIVATE = 1;

    public static function tableName()
    {
        return '{{%deck}}';
    }

    public function getCards(){
        if ($this->_cards === null) {
            $this->_cards = DeckCard::getCards($this->id, DeckCard::DETAIL);
        }
        return $this->_cards;
    }

    public function getCardsSnap(){
        return DeckCard::getCards($this->id, DeckCard::SNAP);
    }

    public function getPlots()
    {
        $cards = $this->cards;
        $plots = [];
        foreach ($cards as $card) {
            if ($card->type == Card::TYPE_PLOT) {
                $plots[] = $card->id;
            }
        }
        return $plots;
    }

    // 家族牌 
    // 一套牌中只能有一张家族牌，所以获取到就返回咯
    public function getHouse()
    {
        $cards = $this->cards;
        foreach ($cards as $card) {
            if ($card->type == Card::TYPE_HOUSE) {
                return $card->id;
            }
        }
        return 0;
    }

    // 议政牌 
    // 一套牌中只能有一张议政牌，所以获取到就返回咯
    public function getAgenda()
    {
        $cards = $this->cards;
        foreach ($cards as $card) {
            if ($card->type == Card::TYPE_AGENDA) {
                return $card->id;
            }
        }
        return 0;
    }

    public function getNormalCards()
    {
        $cards = $this->cards;
        $normal = [];
        foreach ($cards as $card) {
            if ($card->type != Card::TYPE_PLOT
                && $card->type != Card::TYPE_HOUSE
                && $card->type != Card::TYPE_AGENDA
                ) {
                $normal[] = $card->id;
            }
        }
        return $normal;
    }

    public function getDeckDetails(){
        return [
            'cards' => $this->getCardsSnap(),
            'deck_id' => $this->id,
            'name' => $this->name,
            'house' => $this->house,
            'agenda' => $this->agenda,
        ];
    }

    public function changeBasicInfo($params){
        foreach ($params as $key => $value) {
            $this->$key = $value;
        }
        return $this->save();
    }

    public function changeCards($cards){
        $ret = DeckCard::changeCards($this->id, $cards);
        return $ret;
    }


    public static function findById($id){
        return self::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->one();
    }


    /**
     * @name  创建牌组
     * @author wolfbian
     * @date 2016-09-01
     */
    public static function createDeck($params){
        $deck = new Deck();
        $deck->name = $params['name'];
        $deck->user_id = $params['user_id'];
        $deck->agenda = $params['agenda'];
        $deck->house = $params['house'];
        $deck->game_id = $params['game_id'];

        if (!$deck->save()) {
            return false;
        }

        return $deck->id;
    }

    public static function getDeck($id, $user_id){
        $deck = self::findById($id);

        if (empty($deck)) {
            return $deck;
        }

        if ($deck->user_id == $user_id || $deck->type == self::TYPE_PUBLIC) {
            return $deck;
        }

        return null;
    }

    public static function deleteDeck($id, $user_id){
        $deck = self::findById($id);
        if ($deck->user_id != $user_id ) {
            return false;
        }
        return $deck->delete();
    }

    public static function getDeckArray($user_id){
        $decks = self::find()->where(['user_id' => [0, $user_id], 'status' => self::STATUS_ACTIVE])->asArray()->all();
        if (empty($decks)) {
            return [];
        }
        return $decks;
    }



}
