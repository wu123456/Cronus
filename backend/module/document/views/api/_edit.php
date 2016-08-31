<?php
/* @var $caption string */
/* @var $values array */

use yii\helpers\Html;
?>
<div class="edit-box">
	<h3 class="caption">
		<?= $caption ?>
		<a href="javascript:void(0);" style="font-size:15px;display:none;" class="edit-btn">编辑</a>
	</h3>
	
	<pre class="edit-value"><?php echo empty($value) ? 'Empty.' : $value; ?></pre>
	
	<div class="edit-item" style="display:none;">
		<?php echo Html::textarea("DocumentApi[{$name}]", $value, [
			'id' => "DocumentApi_{$name}",
			'class' => 'edit-field',
			'style' => 'width:100%;height:140px;margin-bottom:8px;'
		]); ?>
		<button type="button" class="edit-save btn btn-primary" data-loading-text="保存中..." autocomplete="off">保存</button>
		<button type="button" class="edit-cancle btn btn-primary">取消</button>
	</div>
</div>
