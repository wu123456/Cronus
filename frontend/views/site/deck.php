<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Deck';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
	.deck {
		height: 700px;
	}
	.left-block {
		width: 80%;
		height: 100%;
		display: inline-block;
		background-color: purple;
	}
	.right-block {
		width: 18%;
		height: 100%;
		display: inline-block;
		background-color: green;
	}
	.card-info {
		width: 80px;
	    height: 100px;
	    display: inline-block;
	    background-color: #ffb100;
	    float: left;
	    margin-top: 20px;
	    margin-left: 20px;
	    margin-right: 10px;
	}
</style>
<div class="deck">

	<div class="left-block">
		
	</div>

	<div class="right-block" ondrapenter="drapEnter" ondragover="dragOver" ondrop="drop">
		
	</div>
</div>

<script src="/js/jquery-3.0.0.min.js"></script>
<script type="text/javascript">
	
	var cardInfo; //全局卡牌信息

	$(function(){

		initData();
		
	});

	function initData(){
		$.ajax({
			url: "/card/cards",
			error:function(result){

			},
			success: function(result){
        		cardInfo = result.data;
        		//console.log(cardInfo);
        		initView();
      		}
      	});
	}

	function initView(){
		length = cardInfo.length;
		for (var i = 0; i < length; i++) {
			//console.log(cardInfo[i]);
			cardNode = "<div draggable='true' class='card-info'></div>";
			$('.left-block').append(cardNode);
		};
	}

	function drapEnter(){
		console.log(111);
	}

	function dragOver(e){
		console.log(222);
		e.preventDefault();
	}

	function drop(){
		console.log(333);
	}
</script>
