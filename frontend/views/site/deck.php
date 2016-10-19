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
	    text-align: center;
	}
	.deck-info {
	    width: 100%;
	    height: 36px;
	    float: left;
	    margin-bottom: 10px;
	    background-color: #ffb100;
	    text-align: center;
	}
</style>
<div class="deck">

	<div class="left-block">
		
	</div>

	<div class="right-block" ondrop="drop(event)" ondragover="allowDrop(event)">
		
	</div>
</div>

<script src="/js/jquery-3.0.0.min.js"></script>
<script type="text/javascript">
	
	var cardInfo; //全局卡牌信息
	var deckInfo; //全局牌组信息
	var deckIndex;

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
        		deckInfo = [];
        		deckIndex = 0;
        		initView();
      		}
      	});
	}

	function initView(){
		length = cardInfo.length;
		for (var i = 0; i < length; i++) {
			card = cardInfo[i];
			//console.log(card);
			cardNode = "<div draggable='true' id='card" + card.id + "' class='card-info' ondragstart='drag(event)' data-index='" + i + "'>" + card.name + "</div>";
			$('.left-block').append(cardNode);
		}
		initEvent();
	}

	function initEvent(){
		
	}

	function drag(ev){
		ev.dataTransfer.setData("Text",ev.target.id);
	}

	function allowDrop(ev){
		ev.preventDefault();
	}

	function drop(ev){
		ev.preventDefault();
		data = ev.dataTransfer.getData("Text");
		index = $('#'+data).data('index');
		card = cardInfo[index];
		deckInfo.push(card);
		deckNode = "<div id='deck" + deckIndex + "' class='deck-info' onclick='remove(this)' data-index='" + deckIndex + "'>" + card.name + "</div>";
		$('.right-block').append(deckNode);
		deckIndex ++;
	}

	function remove(obj){
		$(obj).hide();
		index = $(obj).data('index');
		deckInfo.splice(index, 1);
	}

</script>
