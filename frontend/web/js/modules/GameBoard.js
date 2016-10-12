import React  from 'react'
import $  from 'jquery'
import showMessage  from './Dialog'

const Component = React.Component;
const EventManage = $("<div></div>");
let moveElement;

let board_high = 700;
let card_high = 100;

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
            op_dead: [],
            side: 0,

        };
    }

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
			cards.push(<Card x={x} y={y} key={"op_hands" + i} id={"unknown"} card_id={"back"}/>);
		}



		return (<div className="game-board" ref="t"
					onDrop = {this.handleDrop.bind(this)}
					onDragOver={this.handDragover.bind(this)}
					>
					{cards}
				</div>);
	}

	componentDidMount() {
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
			if(p.y >= 580){
				return true;
			}
			return false;
		}

		function inPlayground(p){
			if(p.y >= 120 && p.y <= 580){
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
				let op_hand = ret.data['other_side']['hands'];
				let playground = ret.data['playground'];
				let side = ret.data['side'];
				self.setState({
					hands: my_hand, 
					playground: playground,
					op_hands: op_hand,
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
            opacity: 1
        }
    }

	render() {
		let x = this.state.x || this.props.x || 0;
		let y = this.state.y || this.props.y || 0;
		let name = this.state.card_id || this.props.card_id || this.state.id || this.props.id ||"";
		if (x || y) {
			return (<div className="card2" draggable="true" ref="s"
						onClick = {this.handleClick}
						onDragStart = {this.handleDragStart.bind(this)}
						onDragEnd = {this.handleDragEnd.bind(this)}
						onDrag = {this.handleDrag.bind(this)}
						style={{opacity:this.state.opacity, background:this.props.color, top: y + "px" , left: x + "px"}}
						> 
						{name}
				</div>)
		}
		return (<div className="card" draggable="true" ref="s"
						onClick = {this.handleClick}
						onDragStart = {this.handleDragStart.bind(this)}
						onDragEnd = {this.handleDragEnd.bind(this)}
						onDrag = {this.handleDrag.bind(this)}
						style={{opacity:this.state.opacity, background:this.props.color}}
						> 
				</div>)
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

class Hands extends Component {
	constructor(props) {
		super(props);
        this.state = {
            cards: []
        };
    }

    render() {
    	let cards = this.state.cards;
		return (<div className={"game-hands " + this.props.type} ref="t"
					>
					{cards}
				</div>);
    }

    handleDrop(event) {
		if (moveElement.props.fatherName == this.props.name) {
			moveElement.setState({x: event.pageX - moveElement._x, y: event.pageY - moveElement._y});
			return;
		}

		let a = <Card  key={new Date() - 0} {...moveElement.props} fatherName={this.props.name} x={event.pageX - this.refs.t.offsetLeft - moveElement._x} y={event.pageY - this.refs.t.offsetTop - moveElement._y}/>;
		
		this.setState(function(oldState){
			oldState.cards.push(a);
			return oldState;
		});

	}

	handDragover(event) {
		event.preventDefault()
	}
}

export default Board
