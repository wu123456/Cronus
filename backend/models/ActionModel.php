<?php

namespace backend\models;

use yii\base\Component;

class ActionModel extends Component
{
	private $_rfMethod;
	
	/**
	 * 接口方法名
	 */
	private $_name;
	
	/**
	 * 接口名称
	 */
	private $_title;
	
	/**
	 * 请求方法
	 */
	private $_method;
	
	/**
	 * 接口参数
	 */
	private $_params = [];
	
	/**
	 * 接口参数默认值
	 */
	private $_paramsDefaultValues = [];
	
	/**
	 * 路由
	 */
	private $_route;
	
	/**
	 * 接口作者
	 */
	private $_author;
	
	/**
	 * 简介
	 */
	private $_uses;
	
	/**
	 * 数据库中保存的扩展内容
	 */
	public $data;
	
	public function __construct(\ReflectionMethod $method)
	{
		$this->_rfMethod = $method;
		parent::__construct([]);
	}
	
	public function init()
	{
		$this->_name = $this->_rfMethod->name;
		
		$params = $this->_rfMethod->getParameters();
		foreach ($params as $p) {
			if ($p->isDefaultValueAvailable()) {
				$this->_paramsDefaultValues[$p->getName()] = $p->getDefaultValue();;
			}
		}
		
		$comment = $this->_rfMethod->getDocComment();
		if (preg_match_all('/@param\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			foreach ($matches[1] as $match) {
				$info = preg_split("/[\s]+/", $match, 3);
				$param = [
					'type' => isset($info[0]) ? $info[0] : '',
					'name' => isset($info[1]) ? $info[1] : '',
					'desc' => isset($info[2]) ? $info[2] : '',
				];
				$this->_params[] = $param;
			}
		}
		
		if (preg_match('/@name\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			$this->_title = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_title = $this->_rfMethod->name;
		}
		
		if (preg_match('/@method\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			$this->_method = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_method = 'GET';
		}
		
		if (preg_match('/@author\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			$this->_author = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_author = '';
		}
		
		if (preg_match('/@uses\s*(.*)\n/', $comment, $matches) && !empty($matches[1])) {
			$this->_uses = trim($matches[1], "\t\n\r\0\x0B");
		} else {
			$this->_uses = '';
		}
		
		$ms = explode("\\", $this->_rfMethod->class);
		$moduleName = null;
		for ($i=0; $i < count($ms); $i++) { 
			if(($ms[$i] == "modules" || $ms[$i] == "module") && $i < count($ms) - 1) {
				$moduleName = trim(preg_replace_callback('/([A-Z])/', function($matches){
					return '-' . strtolower($matches[0]);
				}, $ms[$i+1]), '-');
				break;
			}
		}
		$className = $ms[count($ms) - 1];
		$controllerId = trim(preg_replace_callback('/([A-Z])/', function($matches){
			return '-' . strtolower($matches[0]);
		}, substr($className, 0, strlen($className) - 10)), '-');
		$actionId = trim(preg_replace_callback('/([A-Z])/', function($matches){
			return '-' . strtolower($matches[0]);
		}, substr($this->_name, 6)), '-');
		if(empty($moduleName)) {
			$this->_route = "{$controllerId}/{$actionId}";
		} else {
			$this->_route = "{$moduleName}/{$controllerId}/{$actionId}";
		}
	}
	
	public function getParamDefaultValue($paramName)
	{
		return isset($this->_paramsDefaultValues[$paramName]) ? $this->_paramsDefaultValues[$paramName] : '';
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function getTitle()
	{
		return $this->_title;
	}
	
	public function getMethod()
	{
		return strtoupper($this->_method);
	}
	
	public function getParams()
	{
		return $this->_params;
	}
	
	public function getParamsDefaultValues()
	{
		return $this->_paramsDefaultValues;
	}
	
	public function getRoute()
	{
		return $this->_route;
	}
	
	public function getAuthor()
	{
		return $this->_author;
	}
	
	public function getUses()
	{
		return $this->_uses;
	}
}