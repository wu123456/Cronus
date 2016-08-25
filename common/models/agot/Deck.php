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
 * @property integer $user_id
 * @property integer $house
 * @property integer $agenda_id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Deck extends AgotBase{
    const STATUS_DELETED = 1;
    const STATUS_ACTIVE = 0;

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }



}
