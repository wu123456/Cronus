<?php
namespace console\controllers;

use Yii;
use yii\db\Query;
use common\models\agot\Table;
use common\models\agot\Card;
use common\models\agot\Deck;
use common\models\agot\DeckCard;
use common\helpers\GlobalHelper;

/**
 * 调用方式：./yii hello/index 
 * 调用方式：./yii hello/import-cards 
 */
class HelloController extends \yii\console\Controller {



    public function actionIndex() {
        $tables = Yii::$app->params['tables'];
        $game_sides = Yii::$app->params['game_sides'];

        while(true){
            foreach ($tables as $key => $value) {
                $table = new Table($value);
                echo $value . "\t";
                if ($table->start($game_sides)) {
                    echo "start";
                }else{
                    echo "running or unready";
                }
                echo "\r\n";
            }
            echo "\r\n";
            sleep(15);
            GlobalHelper::connectDb();
        }        
        
    }

    public function actionImportCards($filename){
    	$map = self::map();
    	$cards = self::cards($filename);
    	foreach ($cards as $key => $card) {
    		$model = new Card;
    		$model->picture_url = $card['@attributes']['id'];

    		foreach ($card['property'] as $key => $property) {
    			$p = $property['@attributes'];
    			if ( isset($map[$p['name']]) ) {
    				$model->$map[$p['name']] = $p['value'];
    			}
    		}
    		// $model->name = $card['property'][0]['@attributes']['value'];
    		$model->save();
    	}
    }	

    public static function cards($filename){
        // $filename = './console/controllers/exchangeTrade/except.json';
        // $handle = fopen($filename, "r");
        // $str = fread($handle, filesize ($filename));
        // $array = json_decode($str, true);
        // echo "\nexcept array length is " . count($array) . "\n";
        // return $array;
        $filename = './set/'. $filename;
        // $handle = fopen($filename, "r");
        // $str = fread($handle, filesize ($filename));

        $str = simplexml_load_file($filename);
        $str = json_decode(json_encode($str), true);
        return $str['cards']['card'];
    }

    public static function map(){
    	$map = [
    		'名称' => 'name',
    		'Name' => 'name',
            '类型' => 'type',
    		'阵营' => 'house',
    		// 'House' => 'house',
    		// '属性' => 'attribute',
    		'能力' => 'text',
    		// 'Text' => 'text',
    	];
    	return $map;
    }

    public function actionImportDeck()
    {
        $cards = [["1", "a8c94f92-2b17-4a78-bf0f-4411838b9826", "拜拉席恩"],
                    ["1", "0da73715-efc3-449c-9030-daa6bf8b20c9", "海怪的旗帜"],
                    ["1", "d10a1ced-df94-419b-af8e-4eadc2eb4688", "野火袭击"],
                    ["1", "f68a9d16-dea2-4eae-a03f-8fbc48e2c85c", "听候差遣"],
                    ["1", "c9e6fcab-2b73-4a09-a5df-07426703a834", "国王之治"],
                    ["1", "3935f7d8-87d5-4c02-ac86-2463e99d2c4d", "幸免于难"],
                    ["2", "29f2ef9a-c8e6-46e6-86b6-f0c9440804a6", "盛夏的歌谣"],
                    ["1", "e5ac9f1c-e422-441d-b155-109040e45b51", "丰收时节"],
                    ["2", "499ed82d-cc0e-43a5-89ba-a748b388f528", "小指头"],
                    ["1", "0c97b091-96c0-46df-8442-e904025304fb", "克礼森学士"],
                    ["2", "6ab37ed8-df99-410d-a9ff-7afe98f7ee22", "梅丽珊卓"],
                    ["3", "78ca6089-6d16-4e41-8df7-40042e3dc077", "劳勃·拜拉席恩"],
                    ["1", "6967fd81-f9f9-4077-8d5a-c9ca189a5e41", "希琳·拜拉席恩"],
                    ["3", "21c87233-3c2e-4a66-9ea0-ffaf5ac0594c", "狂热的追随者"],
                    ["3", "2a669f95-cad1-43eb-b7a5-4aac56b411de", "龙石岛信徒"],
                    ["3", "b84482ca-cca3-46ee-90dd-22417b0f9879", "枪骑兵先锋"],
                    ["1", "1913dcf2-dc68-4427-a3ae-5deb5e054d8c", "温达米尔学士"],
                    ["2", "cbeb3a37-d4c1-4697-b8d2-e366b4569002", "淹人"],
                    ["1", "d57749cb-274c-440e-b6b1-f81a98b5cd9f", "新封领主"],
                    ["3", "91b7190f-d0ba-4c3b-b9e2-5e7d2c872acb", "淹神的牧师"],
                    ["1", "215060c3-addf-49c5-b935-5f2bee06f52e", "巴利斯坦·赛尔弥爵士"],
                    ["2", "6a4a085b-2477-497c-91c6-b0c3c6a609da", "白渡鸦"],
                    ["1", "27af4d0c-ce1f-452c-8758-1f366755c895", "派洛斯学士"],
                    ["2", "6f350923-3da1-4779-96c6-8ceb8341e81e", "亚夏女祭司"],
                    ["3", "0add28a1-ac16-4b9f-b17d-711130395b4d", "史坦尼斯·拜拉席恩"],
                    ["1", "ffdf0b1b-ea89-4c8f-9419-1d268b7af6f5", "湿发伊伦"],
                    ["1", "e5ac9f1c-e422-441d-b155-109040e45b27", "维克塔利昂·葛雷乔伊"],
                    ["1", "32b0d392-802e-4b0b-9df5-dd0eeee256b9", "铁王座"],
                    ["3", "90452d99-6a6f-4d0d-b3a1-bb92e7110d1d", "国王大道"],
                    ["3", "51a3e04f-3f44-4417-8d7c-1f116acd5c80", "玫瑰大道"],
                    ["3", "1daa814f-c6b8-4dcc-b4f7-169968be4bc5", "龙石岛港口"],
                    ["2", "8b8d9c4c-a100-4d95-b68b-5156766035d9", "图桌厅"],
                    ["3", "3a89059b-0e56-4b47-aad7-8d2b0a245149", "红堡"],
                    ["2", "e6b3dd0d-d102-47d4-a66e-94799fe83988", "海婊子号"],
                    ["1", "e5ac9f1c-e422-441d-b155-109040e45b28", "老威克岛"],
                    ["3", "a8bebc54-d01c-424d-8839-460ec09b733f", "罂粟花奶"],
                    ["3", "495f1553-c28b-49a7-b1d6-9cace43d9d2a", "烂醉如泥"],
                    ["3", "50402306-cc27-4e3e-9924-aa13f430cb60", "在圣火中看见"],
                    ["3", "8f9f0932-a151-4fcb-b1ed-f82a806c82c6", "民众的支持"]];
    
        $deckId = Deck::createDeck([
                'name' => '统御鹿',
                'user_id' => '0',
                'house' => '1',
                'agenda' => '1',
                'game_id' => '0',
            ]);

        DeckCard::changeCardsBySourceId($deckId, $cards);
    }

}