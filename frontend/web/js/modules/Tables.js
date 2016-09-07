import React  from 'react'
import $  from 'jquery'
import showMessage  from './Dialog'

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
		return <div onClick={this.handleJoinIn.bind(this)}>{name}</div>
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

	handleJoinIn() {
			showMessage("该位置已有玩家");
		if (this.props.user_id) {
			showMessage("该位置已有玩家");
			return;
		}

		$.post(
			'/table/ready',
			{
				id	 :  this.props.t_id,
				side :  this.props.side,
				deck_id	: 3
			},
			function(ret){
				console.log(ret);
			},
			'json'
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
			<div className="ctable-top"><Player {...side0} t_id = {this.props.id} side={0} /></div>
			<div className="ctable-name">{this.props.name}</div>
			<div className="ctable-bottom"><Player {...side1}  t_id = {this.props.id} side={1}/></div>
		</div>)
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
					tables.push(<Table key={i} {...ret.data[i]} />);
				}

				self.setState({tables: tables});

			}
		)
	}
}



export default Tables