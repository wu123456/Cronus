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

class Table extends Component {

	render() {
		let side = this.props.side;
		let side0 = (side && side[0]) || {};
		let side1 = (side && side[1]) || {};
		console.log(side0);
		return (<div className="ctable">
			<div className="ctable-top" onClick={this.handleJoinIn}><Player {...side0}/></div>
			<div className="ctable-name">{this.props.name}</div>
			<div className="ctable-bottom"><Player {...side1}/></div>
		</div>)
	}

	handleJoinIn() {

	}

}

class Tables extends Component {

	constructor(props) {
		super(props);
        this.state = {
            tables: []
        }
    }

	render() {
		return <div className="ctables">{this.state.tables}</div>
	}

	componentDidMount() {
		var self = this;
		$.getJSON(
			"/table/tables",
			function(ret){
				console.log(ret)
				if (ret.code != 0) {
					alert(ret.msg);
					return;
				}
				var tables = [];
				for(var i in ret.data){
					tables.push(<Table key={i} name={ret.data[i]['name']} side={ret.data[i]['side']} />);
				}

				self.setState({tables: tables});

			}
		)
	}
}



export default Tables