import React  from 'react'
import $  from 'jquery'
import showMessage  from './Dialog'
import Decks  from './Deck'

const Component = React.Component;

const EventManage = $("<div></div>");

// 检查比赛是否开始
setInterval(function(){
	$.getJSON(
		'/table/is-start',
		function(ret){
			if(ret.code == 0 && ret.data){
				location.href = "/site/vgame";
			}
		})
}, 5000);

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

		let self = this;

		if (this.props.user_id) {
			return showMessage("该位置已有玩家");
		}

		let select_deck_id = 0;

		showMessage({
			title : "请选择牌组",
			message: <Decks selected={function(deck_id){
				select_deck_id = deck_id;
			}}/>,
			close : function(){
				return select_deck_id;
			}
		}).then(function(deck_id){
			$.post(
				'/table/ready',
				{
					id	 :  self.props.t_id,
					side :  self.props.side,
					deck_id	: deck_id
				},
				function(ret){
					console.log(ret);
					if (ret.code != 0) {
						return showMessage(ret.msg);
					}

					return showMessage("加入座位成功").then(function(){
						EventManage.trigger("tables_change");
					});
				},
				'json'
			)
		})
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
		this.refreshTables();
		EventManage.on("tables_change", this.refreshTables.bind(this));
	}

	refreshTables() {
		let self = this;
		$.getJSON(
			"/table/tables",
			function(ret){
				console.log(ret)
				if (ret.code != 0) {
					showMessage(ret.msg);
					return;
				}
				let tables = [];
				for(let i in ret.data){
					tables.push(<Table key={i+":"+new Date()} {...ret.data[i]} />);
				}

				self.setState({tables: tables});

			}
		)
	}
}



export default Tables