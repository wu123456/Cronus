<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Congratulations!</h1>

        <p class="lead"></p>

    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-6">
                <h2>组牌</h2>

                <p>目前提供牌组上传功能，同时内置了部分公共牌组。</p>

                <p><a class="btn btn-default" href="/deck-yii/index">去组牌</a></p>
            </div>
            <div class="col-lg-6">
                <h2>开始游戏</h2>

                <p>在桌子页面坐下，坐下时选择牌组，即为准备游戏，如果对面座位也有人坐下，游戏很快就会开始。</p>
                <p>离开前请手动退座位（点击自己在桌子上显示的名字）</p>
                <p><a class="btn btn-default" href="/site/vtables">去游戏</a></p>
            </div>
        </div>

    </div>
</div>
