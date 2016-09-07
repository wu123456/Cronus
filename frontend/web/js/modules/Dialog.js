import { render } from 'react-dom'
import React  from 'react'

const Component = React.Component;

class Dialog extends Component {
	constructor(props) {
		super(props);
        this.state = {
            height: 0
        }
    }

	render() {
		let left = (document.documentElement.clientWidth - 400) / 2 + "px";
		let top = Math.min(300, (document.documentElement.clientHeight - this.state.height) / 2) + "px";
		let title = this.props.title || "系统提示";
		let message = this.props.message || "";
		let visibility = this.state.height ? "" : "hidden";
		return (
			<div>
				<div ref="dialog" className="dialog" style={{width: '400px', left: left, top: top, zIndex : 999, visibility: visibility}}>
					<h3 className="title">{title}</h3>
					<div className="content">
						<div className="message">{message}</div>
						<div className="buttons">
							<button className="btn bg-color-red-dark text-color-white clickable" onClick={this.close.bind(this)}>确认</button>
						</div>
					</div>
				</div>
				<div className="mask" onClick={this.close.bind(this)}></div>
			</div>
		)
	}

	componentDidMount() {
		this.setState({height : this.refs.dialog.offsetHeight});
	}

	close() {
		this.props.close();
	}
}

function showMessage(config){
	let p=document.createElement("div");
	document.body.appendChild(p);
	render(<Dialog close={function(){
		document.body.removeChild(p);
	}} {...config}/>, p)
}

export default showMessage