import React  from 'react'
import $  from 'jquery'
import showMessage  from './Dialog'

const Component = React.Component;
const EventManage = $("<div></div>");

class GameBoard extends Component {

	constructor(props) {
		super(props);
        this.state = {
            cards: []
        };
    }

	render() {
		let cards = this.state.cards;
		return (<div className="game-board" ref="t"
					onDrop = {this.handleDrop.bind(this)}
					onDragOver={this.handDragover.bind(this)}
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
