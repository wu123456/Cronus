<?php

namespace frontend\controllers;

use Yii;
use yii\base\Exception;
use yii\web\Response;
use yii\web\Controller;
use frontend\models\DeckSearch;
use common\models\agot\Deck;
use common\models\agot\Card;
use common\models\agot\DeckCard;

class DeckYiiController extends Controller{

	public function actionIndex()
    {

        $userId = Yii::$app->user->id;
        if (empty($userId)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $searchModel = new DeckSearch();
        $dataProvider = $searchModel->search(['user_id' => $userId]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);

    }

    /**
     * @name  上传牌组
     * @method POST
     * @param    file            file       牌组id
     * @author wolfbian
     * @date 2017-08-14
     */
    public function actionUploadDeck(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            Yii::$app->getSession()->setFlash('error', '未登录');
            return $this->redirect(['index']);
        }

        try {
            $filename = $_FILES["file"]['tmp_name'];
            $str = simplexml_load_file($filename);
            $section = $str->section;
            $deckCards = [];
            foreach ($section as $s) {
                $cards = $s->card;
                foreach ($cards as $card) {
                    $card = json_decode(json_encode($card), true);
                    $attr = $card['@attributes'];
                    $deckCards[] = [$attr['qty'], $attr['id']];
                }
            }
            $deckId = Deck::createDeck([
                    'name' => substr($_FILES["file"]['name'], 0, -4),
                    'user_id' => $user_id,
                    'house' => '1',
                    'agenda' => '1',
                    'game_id' => '0',
                ]);

            DeckCard::changeCardsBySourceId($deckId, $deckCards);
            Yii::$app->getSession()->setFlash('success', "上传牌组成功!");
            return $this->redirect(['index']);

        } catch (\Exception $e) {
            Yii::$app->getSession()->setFlash('error', $e->getMessage());
            return $this->redirect(['index']);
        }
    }

}