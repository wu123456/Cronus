<?php

namespace backend\components\widgets;

class ActiveField extends \yii\widgets\ActiveField
{
	public function dropDownList($items, $options = [])
	{
		if (!isset($options['class'])) {
			$options['class'] = '';
		}
		return parent::dropDownList($items, $options);
	}
	
	public function listBox($items, $options = [])
	{
		if (!isset($options['class'])) {
			$options['class'] = '';
		}
		return parent::listBox($items, $options);
	}
	
	public function textarea($options = [])
	{
		if (!isset($options['class'])) {
			$options['class'] = 'tarea';
		}
		return parent::textarea($options);
	}
}
