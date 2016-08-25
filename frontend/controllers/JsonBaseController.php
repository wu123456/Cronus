<?php

namespace frontend\controllers;

use Yii;
use yii\base\Exception;
use yii\web\Response;
use yii\web\Controller;

class JsonBaseController extends Controller{

	const CODE_NOLOGIN = 1002;

    public function init(){

        if ($this->request->get('callback')) {
            Yii::$app->response->format = Response::FORMAT_JSONP;
        }else{
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        parent::init();
    }

}