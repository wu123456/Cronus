<?php
namespace console\controllers;

use Yii;
use yii\db\Query;
use common\models\agot\Table;
use common\models\agot\Card;

/**
 * 调用方式：./yii hello/index 
 * 调用方式：./yii hello/import-cards 
 */
class HelloController extends \yii\console\Controller {



    public function actionIndex() {
        $tables = Yii::$app->params['tables'];
        $game_sides = Yii::$app->params['game_sides'];
        foreach ($tables as $key => $value) {
            $table = new Table($value);
            echo $value . "\t";
            var_dump($table->start($game_sides));
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

}