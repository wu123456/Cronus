<?php
namespace common\models\agot;

use Yii;
use common\behavior\MyTimestampBehavior;
use yii\db\ActiveRecord;

/**
 * author wolfbian
 * date 2016-07-25
 *
 * AgotBase model
 *
 * @property integer $id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class AgotBase extends ActiveRecord{
    
    const STATUS_DELETED = 1;
    const STATUS_ACTIVE = 0;

    /**
     * @inheritdoc
     */
    public function behaviors(){
        return [
            MyTimestampBehavior::className(),
        ];
    }

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
    public static function findIdentity($id){
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public function delete(){
        if ($this->status == self::STATUS_ACTIVE) {
            $this->status = self::STATUS_DELETED;
            return $this->save();
        }
        return false;
    }


}
