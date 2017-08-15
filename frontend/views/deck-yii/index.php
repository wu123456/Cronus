<?php

use yii\helpers\Html;
use yii\grid\GridView;

use common\models\UserCommentTypeRecord;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SettingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '我的牌组';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="setting-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <form action="/deck-yii/upload-deck" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label for="exampleInputFile">上传牌组</label>
        <input type="file" id="file" name="file">
      </div>
      <button type="submit" class="btn btn-default">确定</button>
    </form>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'name',
            'create_time',
            'update_time'
        ],
    ]); ?>

</div>