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
		let side = this.props.name;
		let side0 = (side && side[0]) || {};
		let side1 = (side && side[1]) || {};
		console.log(side0);
		return (<div className="cdeck">
			
		</div>)
	}


}

class Decks extends Component {

	constructor(props) {
		super(props);
        this.state = {
            decks: []
        }
    }

	render() {
		return <div className="cdecks">{this.state.decks}</div>
	}

	componentDidMount() {
		var self = this;
		$.getJSON(
			"/deck/decks",
			function(ret){
				console.log(ret)
				if (ret.code != 0) {
					alert(ret.msg);
					return;
				}
				let decks = ret.data;
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
				console.log(decks);

			}
		)
	}
}



export default Decks