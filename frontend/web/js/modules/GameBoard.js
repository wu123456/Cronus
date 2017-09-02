import React  from 'react'
import $  from 'jquery'
import showMessage  from './Dialog' 
import showMenuWithMouse  from './PopupMenu' 
import Util from './Util'
import SocketClient from './SocketClient'


const Component = React.Component;
const EventManage = $("<div></div>");
let moveElement;

let board_width = 0;
let board_high = 700;
let card_width = 75;
let card_high = 100;
let board_point = {}; // 战场左上角距离屏幕左上角的未知

let discard_x = -20;
let library_x = -350;
let plot_x = -240;
let dead_x = -130;
let my_y = 580; 
let op_y = 20;
let my_house_y = 380;
let op_house_y = 230;
let house_x = -20;
let agenda_x = -130;

// 0：手牌，1：牌库，2：弃牌区，3：死亡牌区，4：战略牌区，5：战场
let BLOCK_PLOT = 4;
let BLOCK_DEAD = 3; 
let BLOCK_DISCARD = 2; 
let BLOCK_LIBRARY = 1; 
let BLOCK_HANDS = 0; 
let BLOCK_PLAYGROUND = 5;

// 展示卡牌详情
EventManage.on("show_card", function(event, params){
	let style = {
					backgroundImage: "url(" + params['url'] + ")",
					height: '480px',
					width: '360px',
					backgroundSize:	"100% 100%",
				};
	showMessage({
			message: <div style = {style}></div>
	});
});

// 禁用系统自带右键菜单
window.document.oncontextmenu = function(){ 
	return false;
}  

// 定时刷新战场
// setInterval(function(){
// 	$.getJSON(
// 		'/table/need-refresh',
// 		{
// 			t : new Date() - 0
// 		},
// 		function(ret){
// 			if (ret.code == 0 && ret.data) {
// 				EventManage.trigger("refresh_cards");
// 				EventManage.trigger("refresh_chat_box");
// 			};
// 		}
// 	)


// },1000);

const socketClient = new SocketClient(window.location.protocol + "//" + document.domain + ":8005" , {
			path: "/agot-chat"
		});

socketClient.addListener('refresh', function(ret){
	if (ret.code == 0 && ret.data['need_refresh']) {
		console.log(1)
		EventManage.trigger("refresh_cards");
		EventManage.trigger("refresh_chat_box");
	}else{
		console.log(2)
	}
});


// 战场定义
class GameBoard extends Component {

	constructor(props) {
		super(props);
        this.state = {
            playground: {},
            hands: {},
            discard: {},
            library: 0,
            plot: {},
            dead: {},
            house: {},
            agenda: {},

            op_hands: 0,
            op_plot: 0,
            op_library: 0,
            op_discard: 0,
            op_dead: 0,
            op_house: {},
            op_agenda: {},
            side: 0,
        };
    }

    // 弹窗形式，展示卡牌列表
    showCards(name, cards, blockType) {
    	let message = [];
    	for(let i in cards){
    		message.push(<Card block={blockType} margin="5" key={cards[i]['id']} id={cards[i]['id']} card_id={cards[i]['card_id']}/>);
    	}
    	showMessage({
    		title : name,
			message: <div>{message}<div className="clearfix"></div></div>,
			noMask : 1
		});
    }

    drawCards(count){
    	let self = this;
    	$.post(
			'/table/draw-cards',
			{count : count},
			function(ret){
				if (ret.code == 0) {
					self.getCards();
				}
			},
			'json'
		);
    }

    // react组件渲染方法，里面包括了对战页面各个组件的渲染逻辑，为前端核心逻辑。
	render() {
		let cards = [];

		let hands = this.state.hands;
		let playground = this.state.playground;
		let discard = this.state.discard;
		let library = this.state.library;
		let plot = this.state.plot;
		let dead = this.state.dead;
		let op_hands = this.state.op_hands;
		let side = this.state.side;
		let house = this.state.house;
		let agenda = this.state.agenda;
		let op_library = this.state.op_library;
		let op_discard = this.state.op_discard;
		let op_dead = this.state.op_dead;
		let op_plot = this.state.op_plot;
		let op_house = this.state.op_house;
		let op_agenda = this.state.op_agenda;

		let blocks = [
			<Block onClick={this.showCards.bind(this, "我方战略牌", this.state.plot, BLOCK_PLOT)} key="my_plot" x={plot_x} y={my_y} height={100} width={75} title={"plot"} number={Util.count(plot)}/>,

			<Block onDoubleClick={this.drawCards.bind(this, 1)} key="my_library" x={library_x} y={my_y} height={100} width={75} title={"lib"} number={library}/>,

			<Block onClick={this.showCards.bind(this, "我方弃牌堆", this.state.discard, BLOCK_DISCARD)} key="my_discard" x={discard_x} y={my_y} height={100} width={75} title={"discard"} number={Util.count(discard)}/>,

			<Block onClick={this.showCards.bind(this, "我方死亡牌堆", this.state.dead, BLOCK_DEAD)} key="my_dead" x={dead_x} y={my_y} height={100} width={75} title={"dead"} number={Util.count(dead)}/>,

			<Block key="op_plot" x={plot_x} y={op_y} height={100} width={75} title={"plot"} number={op_plot}/>,

			<Block key="op_library" x={library_x} y={op_y} height={100} width={75} title={"lib"} number={op_library}/>,

			<Block onClick={this.showCards.bind(this, "对方弃牌堆", this.state.op_discard)} key="op_discard" x={discard_x} y={op_y} height={100} width={75} title={"discard"} number={op_discard}/>,

			<Block onClick={this.showCards.bind(this, "对方死亡牌堆", this.state.op_dead)} key="op_dead" x={dead_x} y={op_y} height={100} width={75} title={"dead"} number={op_dead}/>,
			];



		// 渲染手牌区域
		// 手牌区域最大宽度定好，450，
		// 每张牌大小为75，正常情况显示6张牌
		// 当手牌多于6张，牌的部分将被覆盖
		let handsCount = Util.count(hands);
		let handsDistance = 75;
		if (handsCount > 6) {
			handsDistance = (450 - 75) / (handsCount - 1); 
		}
		let j = 0; // index
		for(let i in hands){
			let x = 20 + handsDistance * j;
			let y = 580;
			cards.push(<Card x={x} y={y} key={"hands" + hands[i]['id']} id={hands[i]['id']} card_id={hands[i]['card_id']} is_in_hand={true}/>);
			j++;
		}

		for(let i in playground){
			let y = playground[i]['y'];
			if(side == 1){
				y = board_high - card_high - y;
			}
			let cardId = playground[i]['face'] !== 0 ? playground[i]['card_id'] : 'back';
			let isStanding = playground[i]['stand'] !== 0 ? 1 : 0;
			let isNoFocus = playground[i]['nofocus'] !== 0 ? 1 : 0;
			let key = "play" + playground[i]['id'] + "x" + playground[i]['x'] + "y" + y
						+ 'card_id' + cardId + 'isStanding' + isStanding + 'isNoFocus' + isNoFocus;
			cards.push(<Card x={playground[i]['x']} y={y} key={key} 
				id={playground[i]['id']} 
				card_id={playground[i]['face'] !== 0 ? playground[i]['card_id'] : 'back'} 
				is_standing={isStanding}
				isNoFocus={isNoFocus}
				inPlayground={true}/>);
		}

		// 渲染对手手牌区域方法
		// 手牌区域最大宽度定好，450，
		// 每张牌大小为75，正常情况显示6张牌
		// 当手牌多于6张，牌的部分将被覆盖
		handsCount = op_hands;
		handsDistance = 75;
		if (handsCount > 6) {
			handsDistance = 450 / handsCount; 
		}
		for(let i = 0; i < op_hands; i++){
			let x = 20 + handsDistance * i;
			let y = 20;
			cards.push(<Card unmovable={true} x={x} y={y} key={"op_hands" + i} id={"unknown"} card_id={"back"}/>);
		}

		// 设置弃牌区域，死亡牌区域，战略牌区域，牌库区域
		setCard(discard, discard_x, my_y);
		setCard(dead, dead_x, my_y);
		setCard(plot, plot_x, my_y);

		function setCard(type, x, y){
			if(!Util.empty(type)){
				let c;
				for(let i in type){
					c = <Card key={ new Date() + i} x={x} y={y} id={type[i]['id']} card_id={type[i]['card_id']}/>;
				}
				cards.push(c);
			}
		}

		if(library > 0){
			let c = <Card key={"my_library"} x={library_x} y={my_y} id={"unknown"} card_id={"back"}/>;
			cards.push(c);
		}

		if(op_library > 0){
			let c = <Card key={"op_library"} x={library_x} y={op_y} id={"unknown"} card_id={"back"}/>;
			cards.push(c);
		}

		if(op_dead > 0){
			let c = <Card key={"op_dead"} x={dead_x} y={op_y} id={"unknown"} card_id={"back"}/>;
			cards.push(c);
		}

		if(op_plot > 0){
			let c = <Card key={"op_plot"} x={plot_x} y={op_y} id={"unknown"} card_id={"back"}/>;
			cards.push(c);
		}

		if(op_discard > 0){
			let c = <Card key={"op_discard"} x={discard_x} y={op_y} id={"unknown"} card_id={"back"}/>;
			cards.push(c);
		}


		// 设置家族牌、议政牌
		if (!Util.empty(house)) {
			let c = <Card key={"my_house"} x={house_x} y={my_house_y} id={house['id']} card_id={house['card_id']}/>;
			cards.push(c);
		}

		if (!Util.empty(agenda)) {
			let c = <Card key={"my_agenda"} x={agenda_x} y={my_house_y} id={agenda['id']} card_id={agenda['card_id']}/>;
			cards.push(c);
		}

		if (!Util.empty(op_house)) {
			let c = <Card key={"op_house"} x={house_x} y={op_house_y} id={op_house['id']} card_id={op_house['card_id']}/>;
			cards.push(c);
		}

		if (!Util.empty(op_agenda)) {
			let c = <Card key={"op_agenda"} x={agenda_x} y={op_house_y} id={op_agenda['id']} card_id={op_agenda['card_id']}/>;
			cards.push(c);
		}
					

		return (<div className="game-board" ref="t"
					onDrop = {this.handleDrop.bind(this)}
					onDragOver={this.handDragover.bind(this)}
					>
					{cards}
					{blocks}
					<ChatBox ref="chatBox"/>
				</div>);
	}

	componentDidMount() {
		board_width = this.refs.t.clientWidth;
		board_point = Util.getPoint(this.refs.t);
		this.bindEvent();
		this.getCards();
	}

	bindEvent(){

		let self = this;

		EventManage.on('refresh_chat_box', function(){
			self.refs.chatBox.refresh();
		});

		EventManage.on('refresh_cards', function(){
			console.log(12134);
			let my_side = 0;
			$.getJSON(
				'/table/table',
				{},
				function(ret){
					if(ret.code != 0){
						showMessage(ret.msg);
					}

					// 想让该方法只执行一次
					if (!self.socketClientSend) {
						self.socketClientSend = true;
						socketClient.send('game/refresh', {user_id : ret.data['self_side']['user_id']});
					}
					

					let my_hand = ret.data['self_side']['hands'];
					let my_discard = ret.data['self_side']['discard'];
					let my_dead = ret.data['self_side']['dead'];
					let my_library = ret.data['self_side']['library'];
					let my_plot = ret.data['self_side']['plot'];
					let my_house = ret.data['self_side']['house'];
					let my_agenda = ret.data['self_side']['agenda'];
					
					let op_hand = ret.data['other_side']['hands'];
					let op_discard = ret.data['other_side']['discard'];
					let op_dead = ret.data['other_side']['dead'];
					let op_library = ret.data['other_side']['library'];
					let op_plot = ret.data['other_side']['plot'];
					let op_house = ret.data['other_side']['house'];
					let op_agenda = ret.data['other_side']['agenda'];

					let playground = ret.data['playground'];
					let side = ret.data['side'];
					self.setState({
						hands: my_hand, 
						discard: my_discard,
						dead: my_dead,
						library: my_library,
						plot: my_plot,
						house: my_house,
						agenda: my_agenda,
						playground: playground,
						op_hands: op_hand,
						op_discard: op_discard,
						op_dead: op_dead,
						op_library: op_library,
						op_plot: op_plot,
						op_house: op_house,
						op_agenda: op_agenda,
						side: side
					});
				}
			);
		});

		

		EventManage.on('card_move', function(event, params){
			if(!inPlayground(params['from']) && inPlayground(params['to'])){
				params['to'] = opPosition(params['to'], self.state.side);
				$.post(
					'/table/play-onto-board',
					{
						id : params['id'],
						from : getBlockType(params['from']),
						to : params['to'],
						face : params['face']
					},
					function(ret){
						self.getCards();
					},
					'json'
				)
			}else if(inPlayground(params['from']) && inPlayground(params['to'])){
				params['to'] = opPosition(params['to'], self.state.side);
				$.post(
					'/table/move-card',
					{
						id : params['id'],
						to : params['to']
					},
					function(ret){
						// 如果出错，再刷新页面
						if (ret.code != 0) {
							self.getCards();
						};
					},
					'json'
				)
			}else if(inPlayground(params['from']) && !inPlayground(params['to'])){
				$.post(
					'/table/leave-card',
					{
						id : params['id'],
						to : getBlockType(params['to'])
					},
					function(ret){
						self.getCards();
					},
					'json'
				)
			}else{
				self.getCards();
			}
		})

		// 0：手牌，1：牌库，2：弃牌区，3：死亡牌区，4：战略牌，5：战场
		function getBlockType(p){
			if(inLibrary(p)){
				return BLOCK_LIBRARY;
			}else if(inDiscard(p)){
				return BLOCK_DISCARD;
			}else if(inDead(p)){
				return BLOCK_DEAD;
			}else if(inPlot(p)){
				return BLOCK_PLOT;
			}else if(inHand(p)){
				return BLOCK_HANDS;
			}else if (inPlayground) {
				return BLOCK_PLAYGROUND;
			}
			return -1;
		}

		function inHand(p){
			if (p.block === BLOCK_HANDS) {
				return true;
			}
			if(p.y >= my_y){
				return true;
			}
			return false;
		}

		function inPlayground(p){
			if (p.block === BLOCK_PLAYGROUND) {
				return true;
			}
			if (p.block !== undefined) {
				return false;
			}
			if(p.y > op_y + card_high && p.y < my_y){
				return true;
			}
			return false;
		}

		function inLibrary(p){
			if (p.block === BLOCK_LIBRARY) {
				return true;
			}
			return inBlock(p, library_x, my_y);
		}

		function inDiscard(p){
			if (p.block === BLOCK_DISCARD) {
				return true;
			}
			return inBlock(p, discard_x, my_y);
		}

		function inPlot(p){
			if (p.block === BLOCK_PLOT) {
				return true;
			}
			return inBlock(p, plot_x, my_y);
		}

		function inDead(p){
			if (p.block === BLOCK_DEAD) {
				return true;
			}
			return inBlock(p, dead_x, my_y);
		}

		function inBlock(p, x, y){
			if(p.y >= y && p.x <= board_width + x && p.x >= board_width + x - card_width){
				return true;
			}
			return false;
		}

		function opPosition(p, n){
			if(n){
				p.y = board_high - card_high - p.y;
			}
			return p;
		}
	}

	getCards() {
		
		EventManage.trigger("refresh_cards");
		EventManage.trigger("refresh_chat_box");
	}

	handleDrop(event) {
		if (!moveElement) {
			return;
		}

		let srcElement = moveElement;
		event.persist();
		moveElement = null;
		EventManage.trigger("card_move", {
			id : srcElement._id, 
			from : {x : srcElement.x, y : srcElement.y, block : srcElement.block}, 
			to : {x : event.pageX - srcElement._x, y : event.pageY - srcElement._y},
			face : event.shiftKey ? 0 : 1
		});

		if (srcElement.props.block === undefined || [BLOCK_DISCARD, BLOCK_PLOT, BLOCK_DEAD, BLOCK_LIBRARY].indexOf(srcElement.props.block) == -1 ) {
			srcElement.setState({x: event.pageX - srcElement._x, y: event.pageY - srcElement._y});
			return;
		}
		srcElement.setState({gone: true});
		return;

	}

	handDragover(event) {
		event.preventDefault()
	}
}

// 卡牌定义
class Card extends Component {

	constructor(props) {
		super(props);

		// 生成横置状态
		let standingCode = this.props.is_standing; // 1表示站着，0表示躺着
		// 如果standingCode是undefined，则默认为1，isStanding=true
		let isStanding = (standingCode != 0);

		// 生成正反面状态
		let faceCode = this.props.card_id; // 1表示正面，0表示反面
		// 如果FaceCode是undefined，则默认为1，isFace=true
		let isFace = 1;

		if (faceCode == "back") {
			isFace = 0;
		};

        this.state = {
            opacity: 1,
            isFace: isFace,
            isStanding: isStanding,
            isNoFocus: this.props.isNoFocus === 0 ? 0 : 1
        }
    }

	render() {

		let gone = this.state.gone || false;
		if (gone) {
			return null;
		}

		// x, y 用于绝对定位
		let x = this.state.x || this.props.x || 0;
		let y = this.state.y || this.props.y || 0;
		let margin = this.state.margin || this.props.margin || 0;
		let cardId = this.state.card_id || this.props.card_id;
		let name = this.state.name || cardId || this.state.id || this.props.id ||"";
		let unmovable = this.props.unmovable;
		let class_name = 'card';
		let style = {opacity:this.state.opacity, margin:margin + "px", background: this.props.color};
		let isStanding = this.state.isStanding;
		let isNoFocus = this.state.isNoFocus;
		const LYING_DOWN_CLASS = 'card-lying-down';

		if(this.state.isFace && this.state.url){
			style['backgroundImage'] = "url(" + this.state.url + ")";
			style['backgroundSize'] = "100% 100%";
			name = "";
		}

		if (!this.state.isFace) {
			style['backgroundImage'] = "url(/image/back.png)";
			style['backgroundSize'] = "100% 100%";
			name = "";
		}

		if (!isNoFocus) {
			style['border'] = "3px solid red";
		}

		let gold = this.state.gold || this.props.gold|| "";
		if(gold){
			gold = "金币：" + gold;
		}

		let power = this.state.power || this.props.power|| "";
		if(power){
			power = "标记：" + power;
		}

		let strength = this.state.strength || this.props.strength || "";
		if(strength){
			if(strength > 0 ){
				strength = "能力：+" + strength;
			}else{
				strength = "能力：" + strength;
			}
		}

		if(x || y){
			class_name = 'card2';
			style.top = y + "px";
			if(x >= 0){
				style.left = x + "px";
			}else{
				style.right = -x + "px";
			}
		}

		if (!isStanding) {
			class_name = class_name + ' ' + LYING_DOWN_CLASS;
		};

		if(!unmovable){
			return (<div className={class_name} draggable="true" ref="s"
						onDragStart = {this.handleDragStart.bind(this)}
						onDragEnd = {this.handleDragEnd.bind(this)}
						onDrag = {this.handleDrag.bind(this)}
						onDoubleClick = {this.handleDbClick.bind(this)}
						onClick = {this.handleClick.bind(this)}
						onMouseOver = {this.handleMover.bind(this)}
						onMouseOut = {this.handleMout.bind(this)}
						onContextMenu = {this.handleContextMenu.bind(this)}
						style={style}
						> 
						{name}
						<p className="card-mark">{gold}</p>
						<p className="card-mark">{power}</p>
						<p className="card-mark">{strength}</p>
				</div>);
		}

		return (<div className={class_name} ref="s"
						style={style}
						> 
						{name}
						<p className="card-mark">{gold}</p>
						<p className="card-mark">{power}</p>
						<p className="card-mark">{strength}</p>
				</div>);
	}

	componentDidMount(){
		let cardId = parseInt(this.state.card_id || this.props.card_id);
		if (!cardId) {
			return;
		}
		this.showPic(cardId);
	}

	showPic(cardId){
		
		$.getJSON(
			'/card/cards',
			{
				condition : {
					'id' : cardId
				}
			},
			function(ret){
				if(ret.code != 0){
					return showMessage(ret.msg);
				}

				let card = ret['data'][0];
				if(card){
					this.setState({name : card['name'], url : "/image/card/" + card['picture_url'] + ".png"});
				}
			}.bind(this)
		);
	}

	handleDragLeave() {
	}

	handleClick(event) {
		if (event.shiftKey) {
			let self = this;
			$.post(
				'/table/flip-card',
				{
					id : self.props.id,
					type : 2
				},
				function(ret){
					if (ret.code == 0) {
						self.setNoFocusState(!self.state.isNoFocus);
					};
				},
				'json'
			);
		}
	}

	handleMover() {
		if(this.state && this.state.url){
			// this.to 为时间计数器变量
			this.to = setTimeout(function(){
				EventManage.trigger('show_card', {url: this.state.url});
			}.bind(this) , 3000);
		}
	}

	handleContextMenu(event){
		// this.to 为时间计数器变量
		if(this.to){
			clearInterval(this.to);
		}

		if (!this.props.inPlayground) {
			return false;
		}
		let self = this;
		showMenuWithMouse([
					{
						name: "返回手牌",
						event: function(){
							EventManage.trigger("card_move", 
								{
									id : self.props.id, 
									from : {
										block : BLOCK_PLAYGROUND
									}, 
									to : {
										block : BLOCK_HANDS
									}
								});

						}
					},  
					{
						name: "返回牌堆",
						event: function(){
							EventManage.trigger("card_move", 
								{
									id : self.props.id, 
									from : {
										block : BLOCK_PLAYGROUND
									}, 
									to : {
										block : BLOCK_LIBRARY
									}
								});

						}
					},
					{
						name: "进入死亡牌堆",
						event: function(){
							EventManage.trigger("card_move", 
								{
									id : self.props.id, 
									from : {
										block : BLOCK_PLAYGROUND
									}, 
									to : {
										block : BLOCK_DEAD
									}
								});

						}
					}, 
					{
						name: "进入弃牌堆",
						event: function(){
							EventManage.trigger("card_move", 
								{
									id : self.props.id, 
									from : {
										block : BLOCK_PLAYGROUND
									}, 
									to : {
										block : BLOCK_DISCARD
									}
								});

						}
					}, 
					{
						name: "横置/竖立",
						event: this.handleDbClick.bind(this)
					}, 
					{
						name: "翻面",
						event: function(){
							$.post(
								'/table/flip-card',
								{
									id : self.props.id,
									type : 1
								},
								function(ret){
									if (ret.code == 0) {
										self.setFaceState(!self.state.isFace, ret.data);
									};
								},
								'json'
							);
						}
					}, 
					{
						name: "+1 标记",
						event: this.addMark.bind(this, 2, 1)
					},
					{
						name: "-1 标记",
						event: this.addMark.bind(this, 2, 2)
					},
					{
						name: "+1 金币",
						event: this.addMark.bind(this, 1, 1)
					},
					{
						name: "-1 金币",
						event: this.addMark.bind(this, 1, 2)
					},
		], event);

	}

	// 添加标记 type 1：金币 2：权力 3：能力
	// operate 1：添加 ， 2：减少
	addMark(type, operate) {
		let typeList = {
			1 : "gold",
			2 : "power",
			3 : "strength"
		}
		let self = this;
		$.post(
			'/table/change-mark',
			{
				id : self.props.id,
				type : type,
				operate : operate
			},
			function(ret){
				if (ret.code == 0) {
					var s = {};
					s[typeList[type]] = ret.data;
					self.setState(s);
				}
			},
			'json'
		);
	}

	handleMout() {
		// this.to 为时间计数器变量
		if(this.to){
			clearInterval(this.to);
		}
	}

	handleDragStart(event) {
		// this.to 为时间计数器变量
		if(this.to){
			clearInterval(this.to);
		}

	    this.setState({opacity:0.2});
	    // _x, _y 为鼠标位置 - 卡牌左端与战场左端的距离
	    // 包括战场左端到屏幕左端的距离 和 鼠标到卡牌左端的距离
	    // this._x = event.pageX - this.refs.s.offsetLeft;
	    // this._y = event.pageY - this.refs.s.offsetTop;
	    // 换为以下计算方式，之前只能计算在board中的card值，在其他容器中的会有偏差
	    let point = Util.getPoint(this.refs.s);
	    this._x = event.pageX - point.left + board_point.left;
	    this._y = event.pageY - point.top + board_point.top;
	    
	    // x, y为相对于战场左上角的偏移量
	    this.x = this.state.x || this.props.x || 0;
		this.y = this.state.y || this.props.y || 0;
		this.block = this.state.block || this.props.block;
	    this._id = this.state.id || this.props.id || "";
	    moveElement = this;
	}

	handleDragEnd() {
	    this.setState({opacity:1})
	}

	handleDrag() {
	}

	handleDbClick() {

		// this.to 为时间计数器变量
		if(this.to){
			clearInterval(this.to);
		}

		// 手牌不需要躺下来_(:з」∠)_
		let isInHand = this.props.is_in_hand;
		if (isInHand) {
			return;
		};

		let self = this;
		$.post(
			'/table/flip-card',
			{id : self.props.id},
			function(ret){
				if (ret.code == 0) {
					self.setStandingState(!self.state.isStanding);
				};
			},
			'json'
		);
	}

	setStandingState(isStanding) {
		EventManage.trigger("refresh_chat_box");
		this.setState({isStanding: isStanding});
	}

	setFaceState(isFace, cardId) {
		if (!parseInt(this.state.card_id || this.props.card_id)) {
			this.showPic(cardId);
		}
		EventManage.trigger("refresh_chat_box");
		this.setState({isFace: isFace});
	}

	setNoFocusState(isNoFocus) {
		EventManage.trigger("refresh_chat_box");
		this.setState({isNoFocus: isNoFocus});
	}
}

class Board extends Component {
	render() {
		return <div><GameBoard/></div>;
	}
}

class Block extends Component{
	render() {
		let style;
		if(this.props.x >= 0){
			style = {top: this.props.y, left: this.props.x, height: this.props.height, width: this.props.width};
		}else{
			style = {top: this.props.y, right: -(this.props.x), height: this.props.height, width: this.props.width};
		}
		let number = (this.state && this.state.number) || this.props.number || 0;
		let text = (this.props.title || "") + " " + number;
		return (
			<div onClick={this.props.onClick || function(){}} 
			onDoubleClick={this.props.onDoubleClick || function(){}}
			className="block" style={style}>
				{text}
			</div>)
	}
}

class Line extends Component{
	render() {
		return (
				<div className="line"> {this.props.content} </div>
			)
	}
}

class ButtonList extends Component {
	render() {
		return (
				<div className="button-list">
					<div className="btn btn-default btn-size" onClick={this.discardHand.bind(this)}>随机弃牌</div>
					<div className="btn btn-default btn-size" onClick={this.throwCoin.bind(this)}>投硬币</div>
					<div className="btn btn-default btn-size" onClick={this.showLib.bind(this, 0)}>查看牌库上面</div>
					<div className="btn btn-default btn-size" onClick={this.showLib.bind(this, 1)}>查看牌库下面</div>
					<div className="btn btn-default btn-size" onClick={shuffleCard}>洗牌</div>
					<div className="btn btn-default btn-size" onClick={resetCard}>重调</div>
					<div className="btn btn-default btn-size" onClick={surrender}>认输</div>
				</div>
			)
		
	}

	showLib(type) {
		let title = "";
		if (type === 0) {
			title = "牌库前";
		}else{
			title = "牌库最后";
		}
		let message = [];
		
		showMessage({
			title : "请选择查看数量",
			message: <div><input id="watch_count_input" name="watch_count" placeholder="1"/></div>,
			confirm : function(){
				let inputValue = $("#watch_count_input").val();
				
				$.getJSON(
					'/table/show-lib',
					{
						type : type,
						count : inputValue
					},
					function(ret){
						if (ret.code != 0) {
							alert(ret.msg);
							return;
						}
						EventManage.trigger("refresh_chat_box");
						showCards(title + Util.count(ret.data) + "张", ret.data, BLOCK_LIBRARY)
					}
				)
			}
		});
	}

	discardHand() {
		let self = this;
		$.post(
			'/table/random-discard',
			{},
			function(ret){
				if (ret.code != 0) {
					alert(ret.msg);
					return;
				}
				EventManage.trigger("refresh_cards");
				EventManage.trigger("refresh_chat_box");
			},
			'json'
		)
	}

	throwCoin() {
		let self = this;
		$.post(
			'/table/throw-coin',
			{},
			function(ret){
				if (ret.code != 0) {
					alert(ret.msg);
					return;
				}
				EventManage.trigger("refresh_chat_box");
			},
			'json'
		)
	}
}

class ChatBox extends Component {

	constructor(props) {
		super(props);
        this.state = {
            left : 50,
            bottom : 0
        }

        this.refresh = function(){
			let self = this;
			$.getJSON(
				'/table/action-records',
				function(ret){
					if (ret.code != 0) {
						return;
					}
					self.setState({actions: ret.data});
				}
			)
		}
    }


	render() {
		let contents = [];
		let actions = (this.state && this.state.actions) || [];
		for(let i in actions){
			contents.push(<Line key={"l" + i} content={actions[i]} />);
		}
		return (
				<div className="chat-box" 
					style={{left: this.state.left + "px", bottom: this.state.bottom + "px"}}>
					<div onDrag={this.handleDrag.bind(this)} 
						onDragStart={this.handleDragStart.bind(this)}
						draggable={true}
						className="title">对话框</div>
					<ButtonList/>
					<div className="content">
						{contents}
					</div>
					<input ref={"input"} />
					<div onClick={this.speak.bind(this)} className="button">发送</div>
				</div>
		)
	}

	speak(){
		let self = this;
			$.post(
				'/table/speak',
				{
					content: self.refs.input.value
				},
				function(ret){
					if (ret.code != 0) {
						alert(ret.msg);
						return;
					}
					self.refresh();
				},
				'json'
			)
	}

	handleDrag(e){
		let moveX = e.pageX - this.startX;
		let moveY = e.pageY - this.startY;
	    let left = this.state.left + moveX;
		let bottom = this.state.bottom - moveY;
		this.startX = e.pageX;
		this.startY = e.pageY;
		this.setState({
			left: left,
			bottom: bottom
		});
	}

	handleDragStart(e){
		this.startX = e.pageX;
		this.startY = e.pageY;
	}

	componentDidMount() {
		this.refresh();
	}

	
}


// 弹窗形式，展示卡牌列表
function showCards(name, cards, blockType) {
	let message = [];
	for(let i in cards){
		message.push(<Card block={blockType} margin="5" key={cards[i]['id']} id={cards[i]['id']} card_id={cards[i]['card_id']}/>);
	}
	showMessage({
		title : name,
		message: <div>{message}<div className="clearfix"></div></div>,
		noMask : 1
	});
}

function shuffleCard(){
	$.post(
		'/table/shuffle-card',
		{
			type: BLOCK_LIBRARY
		},
		function(ret){
			if (ret.code != 0) {
				alert(ret.msg);
				return;
			}
			EventManage.trigger("refresh_chat_box");
		},
		'json'
	)
}

function resetCard(){
	$.post(
		'/table/reset',
		{},
		function(ret){
			if (ret.code != 0) {
				alert(ret.msg);
				return;
			}
			EventManage.trigger("refresh_cards");
			EventManage.trigger("refresh_chat_box");
		},
		'json'
	)
}

function surrender(){
	$.post(
		'/table/surrender',
		{
		},
		function(ret){
			if (ret.code != 0) {
				alert(ret.msg);
				return;
			}
			EventManage.trigger("refresh_chat_box");
			showMessage({
				message: "你已投降，该局游戏结束。",
				close: function(){
					location.href = "/site/vtables";
				}
			});
		},	
		'json'
	)
}


export default Board

