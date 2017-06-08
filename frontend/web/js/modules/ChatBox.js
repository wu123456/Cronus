import { render } from 'react-dom'
import React  from 'react'
import $  from 'jquery'

const Component = React.Component;

class Line extends Component{
	render() {
		return (
				<div className="line"> {this.props.content} </div>
			)
	}
}

class ChatBox extends Component {

	constructor(props) {
		super(props);
        this.state = {
            left : 50,
            bottom : 0
        }

        this.refresh = function(){
			let self = this;
			$.getJSON(
				'/table/action-records',
				function(ret){
					if (ret.code != 0) {
						return;
					}
					self.setState({actions: ret.data});
				}
			)
		}
    }

	render() {
		let contents = [];
		let actions = (this.state && this.state.actions) || [];
		for(let i in actions){
			contents.push(<Line key={"l" + i} content={actions[i]} />);
		}
		return (
				<div className="chat-box" 
					style={{left: this.state.left + "px", bottom: this.state.bottom + "px"}}>
					<div onDrag={this.handleDrag.bind(this)} 
						onDragStart={this.handleDragStart.bind(this)}
						draggable={true}
						className="title">对话框</div>
					<div className="content">
						{contents}
					</div>
					<input ref={"input"} />
					<div onClick={this.speak.bind(this)} className="button">发送</div>
				</div>
		)
	}

	speak(){
		let self = this;
			$.post(
				'/table/speak',
				{
					content: self.refs.input.value
				},
				function(ret){
					if (ret.code != 0) {
						alert(ret.msg);
						return;
					}
					self.refresh();
				},
				'json'
			)
	}

	handleDrag(e){
		let moveX = e.pageX - this.startX;
		let moveY = e.pageY - this.startY;
	    let left = this.state.left + moveX;
		let bottom = this.state.bottom - moveY;
		this.startX = e.pageX;
		this.startY = e.pageY;
		this.setState({
			left: left,
			bottom: bottom
		});
	}

	handleDragStart(e){
		this.startX = e.pageX;
		this.startY = e.pageY;
	}

	componentDidMount() {
		this.refresh();
	}

	
}



export default ChatBox