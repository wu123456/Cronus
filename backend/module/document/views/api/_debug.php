<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<script src="/js/jsbeautify.js" type="text/javascript"></script>

<div class="container-fluid" style="padding:0;">
<div class="row">
	<div class="col-md-4">
		<h3>路由：<?php echo Html::encode($route); ?></h3>
		<form role="form" id="debug-form">
		  <?php if ($model->params): ?>
		  <?php foreach ($model->params as $param): ?>
		  <div class="form-group">
		    <label>
		    	<?php echo $param['name']; ?>
		    	<?php if ($param['name'] == "\$sign"): ?>
		    	<a href="javascript:void(0);" id="get-sign"> 点击获取签名</a>
		    	<?php endif; ?>
		    </label>
		    <?php if ($param['type'] == "file"): ?>
		    <input type="file" class="form-control <?php echo $param['type']; ?>" name="<?php echo trim($param['name'], '$'); ?>" value="<?php echo $model->getParamDefaultValue(trim($param['name'], '$')); ?>">
		    <?php else: ?>
		    <input type="text" class="form-control <?php echo $param['type']; ?>" name="<?php echo trim($param['name'], '$'); ?>" value="<?php echo $model->getParamDefaultValue(trim($param['name'], '$')); ?>">
		    <?php endif; ?>
		  </div>
		  <?php endforeach; ?>
		  <?php else: ?>
		  <div class="form-group">无参数</div>
		  <?php endif; ?>
		  <button id="submit-btn" type="button" class="btn btn-primary" data-loading-text="提交中..." autocomplete="off">提交</button>
		</form>
	</div>
	<div class="col-md-8" role="main">
		<h3>请求返回:</h3>
		<pre id="response">Empty.</pre>
	</div>
</div>
</div>
<script type="text/javascript">
$(function(){
	$('#submit-btn').click(function(){
		var btn = $(this).button('loading');
		// var data = {};
		// $('.form-control').each(function(){
		// 	if ($(this).val() != '') {
		// 		if ($(this).hasClass("array") || $(this).hasClass("Array")) {
		// 			data[$(this).attr('name')] = JSON.parse($(this).val());
		// 		}else{
		// 			data[$(this).attr('name')] = $(this).val();
		// 		}
		// 	}
		// });
		var data = new FormData(document.getElementById("debug-form"));
		$.ajax({
			url: '<?php echo $debugUrl; ?>',
			type: '<?php echo $model->method; ?>',
			data: data,
			processData:false,
            contentType:false,
			success: function(retData) {
				btn.button('reset');
				if (typeof retData === 'string' && retData.indexOf('content="text/html;') != -1) {
					var url = '<?php echo $debugUrl; ?>?';
					for (key in data) {
						url += key + '=' + data[key] + '&';
					}
					window.open(url);
					$('#response').html('该接口是返回html页面，请允许浏览器弹出新页面或自行在浏览器调试');
				} else {
					var formatText = js_beautify(JSON.stringify(retData), 4, ' ');
					$('#response').html(formatText);
				}
			},
			error: function(retData) {
				btn.button('reset');
				alert('发生错误');
			}
		});
	});

	$('#get-sign').click(function(){
		// var data = {};
		// $('.form-control').each(function(){
		// 	if ($(this).val() != '') {
		// 		data[$(this).attr('name')] = $(this).val();
		// 	}
		// });
		var data = new FormData(document.getElementById("debug-form"));
		// data['<?php echo Yii::$app->getRequest()->csrfParam; ?>'] = '<?php echo Yii::$app->getRequest()->getCsrfToken(); ?>';
		$.ajax({
			url: '<?php echo Url::toRoute('document/get-sign'); ?>',
			type: 'post',
			data: data,
			success: function(retData) {
				$('input[name=sign]').val(retData);
			},
			error: function(retData) {
				alert('发生错误');
			}
		});
	});
});
</script>