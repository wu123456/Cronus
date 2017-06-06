import { render } from 'react-dom'
import React  from 'react'

const Component = React.Component;

class Item extends Component {
	render() {
		let content = this.props.name;
		return (
			<div className="item" onClick={this.handleClick.bind(this)}>{content}</div>
		);
	}

	handleClick() {
		typeof this.props.event === "function" && this.props.event();
		this.props.close();
	}
}

class Menu extends Component {

	render() {
		let event = this.props.event;
		let left = event.pageX + "px";
		let top = event.pageY + "px";
		let items = [];
		for(let i = 0; i < this.props.items.length; i++){
			items.push(<Item key={new Date() - 0 + ":" + i} close={this.close.bind(this)} {...this.props.items[i]}/>)
		}
		return (
				<div>
					<div className="popup-menus" style={{left: left, top: top, zIndex : 999}}>
						<div className="content">
							<div className="message">{items}</div>
						</div>
					</div>
					<div className="mask" onClick={this.close.bind(this)}></div>
				</div>
			)
	}

	close() {
		return this.props.close();
	}
}

// items 说明
// 为按钮组,主要包括按钮名以及按钮事件
// [{name:"test", event: function(){}}, {name:"test2", event: function(){}}]
// 列表展示的一个角会顶着鼠标，具体根据鼠标在屏幕中的位置决定
// event 为触发的事件，用于获取鼠标位置
function showMenuWithMouse(items, event){
	let p = document.createElement("div");
	document.body.appendChild(p);

  	render(<Menu items={items} event={event} close={
  		function(){
  			document.body.removeChild(p);
  		}
	} />, p)
}

export default showMenuWithMouse