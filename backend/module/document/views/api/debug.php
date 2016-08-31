<?php
use backend\assets\AppAsset;

AppAsset::register($this);
?>

<?php $this->beginPage(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="x-ua-compatible" content="ie=7" />
	<?php $this->head() ?>
	<link href="/css/document.css" rel="stylesheet" type="text/css" />
</head>
<body style="padding-left:20px;">
	<?php $this->beginBody(); ?>
	<?php echo $content; ?>
	<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>