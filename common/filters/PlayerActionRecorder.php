<?php
namespace common\filters;

use Yii;
use yii\base\ActionFilter;
use common\models\agot\Table;
use common\models\agot\PlayerActionRecord;

/**
 * 用来记录一局游戏内玩家的所有动作
 *
 * @author wolfbian
 */
class PlayerActionRecorder extends ActionFilter
{

    public function beforeAction($action)
    {
        $paramsGet = Yii::$app->request->get();
        $paramsPost = Yii::$app->request->post();
        $params = array_merge($paramsGet, $paramsPost);
        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return true;
        }
        $tableId = Table::getTableIdByUserId($userId);
        if (empty($tableId)) {
            return true;
        }
        $table = new Table($tableId);
        $playId = $table->getPlayId();
        $side = $table->getSideByUserId($userId);

        PlayerActionRecord::addRecord([
            'side' => $side,
            'play_id' => $playId,
            'user_id' => $userId,
            'action' => $action->controller->id . "/" .$action->id,
            'params' => json_encode($params),
            ]);

        $table->noticeOp($userId);
        return true;
    }

}
