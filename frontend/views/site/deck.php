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
		overflow: auto;
	}
	.right-block {
		width: 18%;
		height: 90%;
		display: inline-block;
		background-color: green;
		overflow: auto;
	}
	.right-block-top {
		width: 17.95%;
		height: 9%;
		background-color: green;
		overflow: auto;
		position: relative;
    	bottom: 100.75%;
    	right: -80.35%;
	}
	.right-block-top-text {
		width: 100%;
		height: 50%;
		text-align: center;
	}
	.right-block-top-button {
		width: 75%;
	    position: relative;
	    left: 12.5%;
	    top: 9%;
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

	<div class="right-block-top">
		<input type="text" class="right-block-top-text" placeholder="卡组名称" />
		<input type="button" class="right-block-top-button" value="完成卡组" />
	</div>
</div>

<script src="/js/jquery-3.0.0.min.js"></script>
<script type="text/javascript">
	
	var cardInfo; //全局卡牌信息
	var deckInfo; //全局牌组信息

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
			console.log(card);
			cardNode = "<div draggable='true' id='card" + card.id + "' class='card-info' ondragstart='drag(event)' data-index='" + i + "' style='opacity: 1; background-image: url(&quot;/image/card/" + card.picture_url + ".png&quot;); background-size: 100% 100%;'>" + card.name + "</div>";
			$('.left-block').append(cardNode);
		}
		initEvent();
	}

	function initEvent(){
		$('.right-block-top-button').click(createDeck);
	}

	function createDeck(){
		console.log('QAQ');
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
		id = card.id;
		if(typeof deckInfo[id] == "undefined"){
			deckInfo[id] = 1;
		}else{
			deckInfo[id] ++;
		}
		deckNode = "<div id='deck" + id + "' class='deck-info' onclick='remove(this)' data-id='" + id + "'>" + card.name + "</div>";
		$('.right-block').append(deckNode);
	}

	function remove(obj){
		id = $(obj).data('id');
		deckInfo[id] --;
		$(obj).remove();
	}

</script>
