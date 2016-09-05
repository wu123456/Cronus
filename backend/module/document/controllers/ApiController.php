<?php
namespace backend\module\document\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use backend\models\ActionModel;
use backend\models\DocumentApi;

/**
 * Document controller
 */
class ApiController extends Controller
{
	public $layout = false;
	
	public $verifyPermission = false;
	
	/**
	 * 获得请求对象
	 */
	public function getRequest()
	{
		return \Yii::$app->getRequest();
	}
	
	/**
	 * 获得返回对象
	 */
	public function getResponse()
	{
		return \Yii::$app->getResponse();
	}

	/**
	 * 文档首页
	 */
	public function actionIndex()
	{
		$action = $this->request->get('action');
		$navItems = [];
		$currentAction = null;
		$debugRoute = $debugUrl = '';
		$configs = Yii::$app->params['apiList'];
		foreach ($configs as $config) {
			$items = [];
			$rf = new \ReflectionClass($config['class']);
			$methods = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				if (strpos($method->name, 'action') === false || $method->name == 'actions') {
					continue;
				}
				$actionModel = new ActionModel($method);
				if($actionModel->getTitle() == $method->name) {
					continue;
				}
				$active = false;
				if ($action) {
					list($class, $actionName) = explode('::', $action);
					if ($class == $config['class'] && $actionName == $method->name) {
						$currentAction = $actionModel;
						$debugRoute = $actionModel->getRoute();
						$debugUrl = str_replace(
							["admin"],
							["www"],
							$this->request->getHostInfo() . $this->request->getBaseUrl()
						) . '/' . $debugRoute;
						$active = true;
					}
				}
				
				$items[] = [
					'label' => $actionModel->getTitle(),
					'url' => Url::to(['', 'action' => "{$config['class']}::{$method->name}"]),
					'active' => $active,
				];
			}
			$navItems[] = [
				'label' => $config['label'],
				'url' => '#',
				'items' => $items
			];
		}
		if ($currentAction) {
			// $api = DocumentApi::findOne(['name' => $action]);
			// $api || $api = new DocumentApi();
			$currentAction->data = [
				// 'response' => $api->response,
				// 'desc' => $api->desc,
				'response' => '',
				'desc' => '',
			];
		}
		return $this->render('api', [
			'action' => $action,
			'navItems' => $navItems,
			'model' => $currentAction,
			'debugRoute' => $debugRoute,
			'debugUrl' => $debugUrl,
		]);
	}
	
	/**
	 * 保存接口文档信息
	 */
	public function actionSave($action)
	{
		$this->response->format = Response::FORMAT_JSON;
		$model = DocumentApi::findOne(['name' => $action]);
		if (!$model) {
			$model = new DocumentApi();
		}
		if ($model->load($this->request->post()) && $model->validate()) {
			$model->name = $action;
			if ($model->save()) {
				return ['result' => true];
			}
		}
		return ['result' => false];
	}
	
	/**
	 * 在线调试，目前只支持frontend下的controller
	 * @param string $route
	 */
	public function actionDebug($route)
	{
		$this->layout = "@backend/module/document/views/api/debug";
		try {
			$route = trim($route, '/');
			// 通过路由找到controller和action名称
			list($controllerId, $actionId) = explode('/', $route);
			$className = str_replace(' ', '', ucwords(str_replace('-', ' ', $controllerId)));
			$actionName = str_replace(' ', '', ucwords(str_replace('-', ' ', $actionId)));
			$class = "frontend\\controllers\\" . $className . "Controller";
			$action = "action" . $actionName;
		
			$rf = new \ReflectionClass($class);
			$method = $rf->getMethod($action);
			$actionModel = new ActionModel($method);
		} catch (\Exception $e) {
			return '无对应的Controller或Action，请检测route参数是否正确';
		}

		
		$debugUrl = str_replace('backend', 'frontend', $this->request->getBaseUrl()) . '/' . $route;
		return $this->render('_debug', [
			'debugUrl' => $debugUrl,
			'route' => $route,
			'model' => $actionModel,
		]);
	}
}