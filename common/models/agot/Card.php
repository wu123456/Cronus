<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * author wolfbian
 * date 2016-07-25
 *
 * Card model
 *
 * @property integer $id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class Card extends AgotBase{

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
        return '{{%card}}';
    }



}
