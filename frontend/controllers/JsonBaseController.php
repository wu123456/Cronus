<?php

namespace frontend\controllers;

use Yii;
use yii\base\Exception;
use yii\web\Response;
use yii\web\Controller;

class JsonBaseController extends Controller{

	const CODE_NOLOGIN = 1002;
	const CODE_SYSTEM_ERROR = 1001;
	const CODE_SUCCESS = 0;

    public function init(){

        if (Yii::$app->request->get('callback')) {
            Yii::$app->response->format = Response::FORMAT_JSONP;
        }else{
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        parent::init();
    }

}