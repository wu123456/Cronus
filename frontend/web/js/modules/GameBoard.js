import React  from 'react'
import $  from 'jquery'
import showMessage  from './Dialog' 
import Util from './Util'

const Component = React.Component;
const EventManage = $("<div></div>");
let moveElement;

let board_width = 0;
let board_high = 700;
let card_width = 100;
let card_high = 100;

let discard_x = -20;
let library_x = -350;
let plot_x = -240;
let dead_x = -130;
let my_y = 580; 
let op_y = 20;


class GameBoard extends Component {

	constructor(props) {
		super(props);
        this.state = {
            // cards: [<Card key="0"/>],
            playground: [{id: 102, x: 1, y: 100, card_id: 2}],
            hands: [{id: 101, card_id: 1}],
            discard: [{id: 103, card_id: 3}],
            library: 0,
            plot: [{id: 112, card_id: 5}],
            dead: [{id: 104, card_id: 6}],

            op_hands: 0,
            op_plot: 0,
            op_library: 0,
            op_discard: [{id: 105, card_id: 3}],
            op_dead: [{id: 108, card_id: 6},{id: 109, card_id: 6}],
            side: 0,

        };
    }

    showCards(name, cards) {
    	let message = [];
    	for(let i in cards){
    		message.push(<Card margin="5" key={cards[i]['id']} id={cards[i]['id']} card_id={cards[i]['card_id']}/>);
    	}
    	showMessage({
    		title : name,
			message: <div>{message}<div className="clearfix"></div></div>,
		});
    }

	render() {
		console.log(this.state);
		let cards = [];
		

		let blocks = [
			<Block onClick={this.showCards.bind(this, "我方战略牌", this.state.plot)} key="my_plot" x={plot_x} y={my_y} height={100} width={100}/>,
			<Block key="my_library" x={library_x} y={my_y} height={100} width={100}/>,
			<Block onClick={this.showCards.bind(this, "我方弃牌堆", this.state.discard)} key="my_discard" x={discard_x} y={my_y} height={100} width={100}/>,
			<Block onClick={this.showCards.bind(this, "我方死亡牌堆", this.state.dead)} key="my_dead" x={dead_x} y={my_y} height={100} width={100}/>,
			<Block key="op_plot" x={plot_x} y={op_y} height={100} width={100}/>,
			<Block key="op_library" x={library_x} y={op_y} height={100} width={100}/>,
			<Block onClick={this.showCards.bind(this, "对方弃牌堆", this.state.op_discard)} key="op_discard" x={discard_x} y={op_y} height={100} width={100}/>,
			<Block onClick={this.showCards.bind(this, "对方死亡牌堆", this.state.op_dead)} key="op_dead" x={dead_x} y={op_y} height={100} width={100}/>,
			];
			// blocks=[];
		let hands = this.state.hands;
		let playground = this.state.playground;
		let discard = this.state.discard;
		let library = this.state.library;
		let plot = this.state.plot;
		let dead = this.state.dead;
		let op_hands = this.state.op_hands;
		let side = this.state.side;

		let j = 0;
		for(let i in hands){
			let x = 20 + 110 * j;
			let y = 580;
			cards.push(<Card x={x} y={y} key={hands[i]['id']} id={hands[i]['id']} card_id={hands[i]['card_id']} />);
			j++;
		}

		for(let i in playground){
			let y = playground[i]['y'];
			if(side == 1){
				y = board_high - card_high - y;
			}

			cards.push(<Card x={playground[i]['x']} y={y} key={playground[i]['id']} id={playground[i]['id']} card_id={playground[i]['card_id']} />);
		}

		for(let i = 0; i < op_hands; i++){
			let x = 20 + 110 * i;
			let y = 20;
			cards.push(<Card unmovable={true} x={x} y={y} key={"op_hands" + i} id={"unknown"} card_id={"back"}/>);
		}

		if(!Util.empty(discard)){
			let c;
			for(let i in discard){
				c = <Card key={"discard" + i} x={discard_x} y={my_y} id={discard[i]['id']} card_id={discard[i]['card_id']}/>;
			}
			cards.push(c);
		}



		return (<div className="game-board" ref="t"
					onDrop = {this.handleDrop.bind(this)}
					onDragOver={this.handDragover.bind(this)}
					>
					{cards}
					{blocks}
				</div>);
	}

	componentDidMount() {
		board_width = this.refs.t.clientWidth;
		this.bindEvent();
		this.getCards();
	}

	bindEvent(){

		let self = this;
		EventManage.on('card_move', function(event, params){
			console.log(event, params);
			if(inHand(params['from']) && inPlayground(params['to'])){
				params['to'] = opPosition(params['to'], self.state.side);
				$.post(
					'/table/play-onto-board',
					{
						id : params['id'],
						from : 0,
						to : params['to']
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
						self.getCards();
					},
					'json'
				)
			}else{
				self.getCards();
			}
		})

		function inHand(p){
			if(p.y >= my_y){
				return true;
			}
			return false;
		}

		function inPlayground(p){
			if(p.y >= op_y + card_high && p.y <= my_y){
				return true;
			}
			return false;
		}

		function inLibrary(p){
			if(p.y >= my_y && p.x <= board_width + library_x && p.x >= board_width + library_x - card_width){
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
		let my_side = 0;
		let self = this;
		$.getJSON(
			'/table/table',
			{},
			function(ret){
				if(ret.code != 0){
					showMessage(ret.msg);
				}
				let my_hand = ret.data['self_side']['hands'];
				let my_discard = ret.data['self_side']['discard'];
				let my_dead = ret.data['self_side']['dead'];
				let my_library = ret.data['self_side']['library'];
				let my_plot = ret.data['self_side']['plot'];
				let op_hand = ret.data['other_side']['hands'];
				let op_discard = ret.data['other_side']['discard'];
				let op_dead = ret.data['other_side']['dead'];
				let op_library = ret.data['other_side']['library'];
				let op_plot = ret.data['other_side']['plot'];
				let playground = ret.data['playground'];
				let side = ret.data['side'];
				self.setState({
					hands: my_hand, 
					discard: my_discard,
					dead: my_dead,
					library: my_library,
					plot: my_plot,
					playground: playground,
					op_hands: op_hand,
					op_discard: op_discard,
					op_dead: op_dead,
					op_library: op_library,
					op_plot: op_plot,
					side: side
				});
			}
		)
	}



	handleDrop(event) {

		if (moveElement.props.fatherName == this.props.name) {
			EventManage.trigger("card_move", {id : moveElement._id, from : {x : moveElement.x, y : moveElement.y}, to : {x : event.pageX - moveElement._x, y : event.pageY - moveElement._y}});
			moveElement.setState({x: event.pageX - moveElement._x, y: event.pageY - moveElement._y});
			return;
		}

		let a = <Card key={new Date() - 0} {...moveElement.props} fatherName={this.props.name} x={event.pageX - this.refs.t.offsetLeft - moveElement._x} y={event.pageY - this.refs.t.offsetTop - moveElement._y}/>;
		
		this.setState(function(oldState){
			oldState.cards.push(a);
			return oldState;
		});

	}

	handDragover(event) {
		event.preventDefault()
	}
}


class Card extends Component{


	constructor(props) {
		super(props);
        this.state = {
            opacity: 1,
        }
    }

	render() {
		let x = this.state.x || this.props.x || 0;
		let y = this.state.y || this.props.y || 0;
		let margin = this.state.margin || this.props.margin || 0;
		let name = this.state.card_id || this.props.card_id || this.state.id || this.props.id ||"";
		let unmovable = this.props.unmovable;
		let class_name = 'card';
		let style = {opacity:this.state.opacity, margin:margin + "px", background:this.props.color};

		if(x || y){
			class_name = 'card2';
			style.top = y + "px";
			if(x >= 0){
				style.left = x + "px";
			}else{
				style.right = x + "px";
			}
		}

		if(!unmovable){
			return (<div className={class_name} draggable="true" ref="s"
						onDragStart = {this.handleDragStart.bind(this)}
						onDragEnd = {this.handleDragEnd.bind(this)}
						onDrag = {this.handleDrag.bind(this)}
						style={style}
						> 
						{name}
				</div>);
		}

		return (<div className={class_name} ref="s"
						style={style}
						> 
						{name}
				</div>);

		
		
	}

	handleClick() {
		console.log(5)
	}


	handleDragLeave() {
		console.log(2)
	}

	handleDragStart(event) {
	    this.setState({opacity:0.2});
	    this._x = event.pageX - this.refs.s.offsetLeft;
	    this._y = event.pageY - this.refs.s.offsetTop;
	    this.x = this.state.x || this.props.x || 0;
		this.y = this.state.y || this.props.y || 0;
	    this._id = this.state.id || this.props.id || "";
	    moveElement = this;
	}

	handleDragEnd() {
	    this.setState({opacity:1})
	}

	handleDrag() {

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
		return <div onClick={this.props.onClick || function(){}} className="block" style={style}></div>
	}
}

export default Board
