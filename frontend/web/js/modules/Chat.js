import React from 'react'
import { render } from 'react-dom'
import { Socket } from 'react-socket'

export default React.createClass({
  	render() {
    	return (
            <div>
                <Socket url="your-socket-endpoint:port?"/>
            </div>
        );
  	}
})