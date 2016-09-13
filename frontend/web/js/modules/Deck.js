import React  from 'react'
import $  from 'jquery'

const Component = React.Component;

class Player extends Component {

	constructor(props) {
		super(props);
        this.state = {
            username: ""
        }
    }

	render() {
		let name = this.state.username || this.props.user_id || "-";
		return <div>{name}</div>
	}

	componentDidMount() {
		let self = this;
		$.getJSON(
			'/user/get-user-info',
			{
				uid : self.props.user_id
			},
			function(ret){
				if (ret.code != 0) {
					return;
				}
				self.setState({username: ret.data.name});
			}
		)
	}

}

class Deck extends Component {

	render() {
		let name = this.props.name;
		return (<div className={"cdeck " + (this.props.active ? "cactive" : "")} onClick={this.handleClick.bind(this)}>
			{name}
		</div>)
	}

	handleClick() {
		console.log(this.props.id);
		this.props.handleClick(this.props.id);
	}


}

class Decks extends Component {

	constructor(props) {
		super(props);
        this.state = {
            decks: [],
            selected: -1
        }
    }

	render() {
		let decks = [];
		for(let i in this.state.decks){
			let active = i == this.state.selected;
			decks.push(<Deck key={i} {...this.state.decks[i]} handleClick={function(deck_id){
				this.setState({selected : i});
				typeof this.props.selected === "function" && this.props.selected(deck_id);
			}.bind(this)} active={active} />);
		}

		return <div className="cdecks">{decks}</div>
	}

	componentDidMount() {
		let self = this;
		$.getJSON(
			"/deck/decks",
			function(ret){
				console.log(ret)
				if (ret.code != 0) {
					alert(ret.msg);
					return;
				}
				// "data": [{
			 //        "id": "2",
			 //        "user_id": "3",
			 //        "name": "tttadsf",
			 //        "house": "3",
			 //        "agenda": "3",
			 //        "game_id": "0",
			 //        "type": "0",
			 //        "status": "0",
			 //        "create_time": "2016-09-02 21:33:53",
			 //        "update_time": "2016-09-02 21:41:23"
			 //    },
			 //    {
			 //        "id": "3",
			 //        "user_id": "3",
			 //        "name": "3333333333",
			 //        "house": "3",
			 //        "agenda": "3",
			 //        "game_id": "0",
			 //        "type": "0",
			 //        "status": "0",
			 //        "create_time": "2016-09-05 16:01:01",
			 //        "update_time": "2016-09-05 16:01:01"
			 //    }]
				

				self.setState({decks: ret.data});

			}
		)
	}
}



export default Decks