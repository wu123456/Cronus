import React  from 'react'
import $  from 'jquery'

const Component = React.Component;

class Table extends Component {

	render() {
		return (<div className="table">
			<div className="table-top"></div>
			<div className="table-name">{this.props.name}</div>
			<div className="table-bottom"></div>
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
		return <div className="tables">{this.state.tables}</div>
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
					tables.push(<Table key={i} name={ret.data[i]['name']} />);
				}

				self.setState({tables: tables});

			}
		)
	}
}



export default Tables