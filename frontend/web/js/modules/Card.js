import React  from 'react'

const Component = React.Component;

let moveElement;

class Outer extends Component {

	render() {
		return (<div className="panel">
			<GameBoard name="123"/>
			<Card fatherName="" color="#666666"/>
		</div>)
	}

}

class GameBoard extends Component{

	constructor(props) {
		super(props);
        this.state = {
            cards: []
        }
    }


	render() {
		let cards = this.state.cards;
		return (<div className="game-board" ref="t"
					onDrop = {this.handleDrop.bind(this)}
					onDragOver={this.handDragover.bind(this)}
					>
					{cards}
				</div>)
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
		})

		// this.getDOMNode().appendChild
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
		if (x || y) {
			return (<div className="card2" draggable="true" ref="s"
						onClick = {this.handleClick}
						onDragStart = {this.handleDragStart.bind(this)}
						onDragEnd = {this.handleDragEnd.bind(this)}
						onDrag = {this.handleDrag.bind(this)}
						style={{opacity:this.state.opacity, background:this.props.color, top: y , left: x }}
						> 
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

	handleDrag(event) {
		// console.log(event.pageY)
	}

	handleDragLeave() {
		console.log(2)
	}

	handleDragStart(event) {
	    this.setState({opacity:0.2})
	    console.log("start", event.pageX , this.refs.s.offsetLeft, event.pageY , this.refs.s.pageY);
	    this._x = event.pageX - this.refs.s.offsetLeft;
	    this._y = event.pageY - this.refs.s.offsetTop;
	    moveElement = this;
	}

	handleDragEnd() {
	    this.setState({opacity:1})
		console.log(3)
		// this.setState({x: event.pageX, y: event.pageY});
	}


}

export default Outer