import React  from 'react'
import $  from 'jquery'
import showMessage  from './Dialog'

const Component = React.Component;
const EventManage = $("<div></div>");
let moveElement;

class GameBoard extends Component {

	constructor(props) {
		super(props);
        this.state = {
            // cards: [<Card key="0"/>],
            hands: [{id: 101, card_id: 1}],
            playground: [{id: 102, x: 1, y: 100, card_id: 2}],
            discard: [{id: 103, card_id: 3}],
            library: [{id: 111, card_id: 4}],
            plot: [{id: 112, card_id: 5}],
            dead: [{id: 104, card_id: 6}]
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

		for(let i in hands){
			let x = 20 + 110 * i;
			let y = 580;
			cards.push(<Card x={x} y={y} key={hands[i]['id']} id={hands[i]['card_id']} name={hands[i]['card_id']} />);
		}

		for(let i in playground){
			cards.push(<Card x={playground[i]['x']} y={playground[i]['y']} key={playground[i]['id']} id={playground[i]['card_id']} name={playground[i]['card_id']} />);
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
		EventManage.on('card_move', function(event, params){
			console.log(event, params);
		})
	}

	// getCards() {
	// 	let my_side = 0;
	// 	let self = this;
	// 	$.getJSON(
	// 		'/table/table',
	// 		{},
	// 		function(ret){
	// 			if(ret.code != 0){
	// 				showMessage(ret.msg);
	// 			}
	// 			let my_hand = ret.data.side[my_side]['hands'];
	// 			let my_hand_cards = [];
	// 			for(let i in my_hand){
	// 				let x = 20 + 110 * i;
	// 				let y = 580;
	// 				my_hand_cards.push(<Card x={x} y={y} key={i} id={my_hand[i]['card_id']} name={my_hand[i]['card_id']} />);
	// 			}
	// 			self.setState({cards: my_hand_cards});
	// 		}
	// 	)
	// }

	handleDrop(event) {

		if (moveElement.props.fatherName == this.props.name) {
			EventManage.trigger("card_move", {id : moveElement._id, from : {x : moveElement._x, y : moveElement._y}, to : {x : event.pageX - moveElement._x, y : event.pageY - moveElement._y}});
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
		let name = this.state.name || this.props.name || this.state.id || this.props.id ||"";
		if (x || y) {
			return (<div className="card2" draggable="true" ref="s"
						onClick = {this.handleClick}
						onDragStart = {this.handleDragStart.bind(this)}
						onDragEnd = {this.handleDragEnd.bind(this)}
						onDrag = {this.handleDrag.bind(this)}
						style={{opacity:this.state.opacity, background:this.props.color, top: y , left: x }}
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
