<?php
namespace frontend\controllers;

use Yii;
use common\models\User;

/**
 * User controller
 * date 2016-09-05
 * author wolfbian
 */
class UserController extends JsonBaseController{

    /**
     * @name  查询用户信息
     * @method GET
     * @param  int            uid      用户id
     * @author wolfbian
     * @date 2016-09-05
     */
    public function actionGetUserInfo(){
        $uid = intval(Yii::$app->request->get("uid"));

        if (empty($uid)) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "输入错误"];
        }

        $user = User::findIdentity($uid);

        if (empty($user)) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "查无此人"];
        }

        return ['code' => self::CODE_SUCCESS, 'data' => ['name' => $user->username]];
    }



}