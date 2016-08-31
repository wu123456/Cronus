<?php

namespace backend\components\widgets;

class ActiveForm extends \yii\widgets\ActiveForm
{
	public function init()
	{
		parent::init();
		$this->fieldConfig = array(
			'class' => 'backend\components\widgets\ActiveField',
			'template' => "{input}\n{error}",
			'inputOptions' => array(
				'class' => 'txt',
			)
		);
	}
}