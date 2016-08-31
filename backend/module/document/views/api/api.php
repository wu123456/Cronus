<?php
use yii\bootstrap\Tabs;
use yii\helpers\Url;
use backend\assets\AppAsset;
use backend\components\widgets\SideNavWidget;

AppAsset::register($this);
?>

<?php $this->beginPage(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="x-ua-compatible" content="ie=7" />
	<?php $this->head() ?>
	<script src="/assets/198e383e/jquery.min.js" type="text/javascript"></script>
	<title>Cronus项目API文档</title>
</head>
<body>
<div id="cpcontainer" style="margin: 60px 30px 0;">
	<?php $this->beginBody(); ?>
	<nav role="navigation" class="navbar-inverse navbar-fixed-top navbar" id="w13767">
		<div class="navbar-header">
			<a href="<?php echo Url::to(['/document/api']); ?>" class="navbar-brand">Cronus项目API文档</a>
		</div>
	</nav>
	<div class="row">
	    <div class="col-md-3">
	        <?php echo SideNavWidget::widget([
	            'id' => 'navigation',
	            'items' => $navItems,
	            'view' => $this,
	        ]); ?>
	    </div>
	    <div class="col-md-9 api-content" role="main">
	    
	    	<?php if ($model): ?>
	    	<h1>
	    		<?php echo $model->title; ?>
	    		<?php if ($model->author): ?><span style="font-size:16px;margin-left:20px;">— <?php echo $model->author; ?></span><?php endif; ?>
	    	</h1>
	    	<pre> URL地址：{host}/<?php echo $model->route; ?><br/> 请求方式：<?php echo $model->method; ?><?php echo $model->uses ? "<br/> <b>用途：{$model->uses}</b>" : ''; ?></pre>
	    	<?php
echo Tabs::widget([
    'items' => [
        [
            'label' => '请求和返回',
            'content' => $this->render('_table', ['caption' => "请求参数", 'values' => $model->params])
						. $this->render('_edit', ['caption' => '描述', 'name' => 'desc', 'value' => $model->data['desc']])
						. $this->render('_edit', ['caption' => '返回示例', 'name' => 'response', 'value' => $model->data['response']]),
            'active' => true,
        ],
        [
            'label' => '在线调试',
            'content' => $this->render('_debug', ['route' => $debugRoute, 'debugUrl' => $debugUrl, 'model' => $model]),
        ]
    ],
]);
	    	?>
	    	<?php else: ?>
	    	<h1>在线调试</h1>
	    	<div class="input-group" style="width:300px;">
		      <input type="text" class="form-control" placeholder="输入路由" id="debug-route">
		      <span class="input-group-btn">
		        <button class="btn btn-primary" type="button" id="go-debug">去调试</button>
		      </span>
		    </div>
	    	<h1>接口注释规范</h1>
	    	<p>@name表示接口名称，不注释则为action函数名，后面跟url下发用的key，中括号括起</p>
	    	<p>@uses表示接口简介/用途等，可空</p>
	    	<p>@method表示请求方式，不注释默认为get</p>
	    	<p>@param表示请求参数，可空可多个，后面分别跟类型、参数名，备注</p>
	    	<p>@author表示接口作者/负责人，可空</p>
	    	<pre>
/**
 * 注册步骤一：手机号获取验证码
 * 
 * @name	获取注册验证码 [userRegGetCode]
 * @uses	用户注册是拉取验证码
 * @method	get
 * @param	string $phone 手机号
 * @author	yakehuang
 */
 public function actionRegGetCode($phone)
 {
 }</pre>
 			<h1>APP接口固定参数</h1>
			<?php echo $this->render('_table', ['caption' => "", 'values' => [
				['name' => 'clientType', 'type' => 'string', 'desc' => '终端版本，取值范围：  ios/android/pc/wap'],
				['name' => 'appVersion', 'type' => 'string', 'desc' => 'app版本，例如：1.0.0'],
				['name' => 'deviceName', 'type' => 'string', 'desc' => '设备名称，例如：iphone5s/xiaomi3'],
				['name' => 'osVersion', 'type' => 'string', 'desc' => '设备os版本，例如：8.1/4.0.0'],
			]]); ?>
 			<h1>错误码规范 (待补充)</h1>
 			<table class="table table-condensed table-bordered table-striped table-hover request-table" style="table-layout: fixed;">
	            <tr>
	                <th style="width: 160px;">错误码</th>
	                <th>注释</th>
	            </tr>
	            <tr>
	                <td>>0</td>
	                <td>业务逻辑错误</td>
	            </tr>
	            <tr>
	                <td>-1</td>
	                <td>系统通用错误，未指定具体错误码</td>
	            </tr>
	            <tr>
	                <td>-2</td>
	                <td>未登录</td>
	            </tr>
		    </table>
	    	<?php endif; ?>
	    </div>
	</div>
	<?php $this->endBody(); ?>
</div>
<script type="text/javascript">
$(function(){
	$('.caption').mouseover(function(){
		$('a', this).show();
	}).mouseout(function(){
		$('a', this).hide();
	});

	$('.edit-btn').click(function(){
		var box = $(this).parent().parent();
		$('.edit-item', box).show();
		$('.edit-value', box).hide();
		$('.edit-field', box).val($('.edit-value', box).html() != 'Empty.' ? $('.edit-value', box).html() : '');
	});

	$('.edit-cancle').click(function(){
		$(this).parent().hide();
		$('.edit-value', $(this).parent().parent()).show();
	});

	$('.edit-save').click(function(){
		var btn = $(this).button('loading');
		$.ajax({
			url: '<?php echo Url::to(['document/api-save', 'action' => $action]); ?>',
			type: 'post',
			data: {
				'<?php echo Yii::$app->getRequest()->csrfParam; ?>': '<?php echo Yii::$app->getRequest()->getCsrfToken(); ?>',
				'DocumentApi[response]': $('#DocumentApi_response').val(),
				'DocumentApi[desc]': $('#DocumentApi_desc').val()
			},
			success: function(data){
				btn.button('reset');
				if (data.result) {
					//alert('保存成功');
					window.location.reload();
				} else {
					alert('保存失败');
				}
			},
			error: function(){
				btn.button('reset');
				alert('发生错误');
			}
		});
	});

	$('#go-debug').click(function(){
		var route = $('#debug-route').val();
		if (!route) {
			alert('路由不能为空');
		} else {
			window.open("<?php echo Url::toRoute('document/api-debug'); ?>&route=" + route);
		}
	});
});
</script>
<script src="/assets/2678189c/js/bootstrap.js"></script>
</body>
</html>
<?php $this->endPage(); ?>