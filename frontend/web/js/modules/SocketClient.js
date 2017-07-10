import io from 'socket.io-client'

class Client{

	constructor(url, options = {}){
		console.log(url, options);
		this.socket = io(url, options);
		this._listeners = [];

		this.socket.on("message", function(e){
			console.log(e);
			let path = e.type;
		    for(let i = this._listeners.length - 1; i >= 0; i--) {
		        let listener = this._listeners[i];
		        if(listener.path  == path) {
		            let ret = listener.handler(e.data);
		            if(ret !== false) {
		                return;
		            }
		        }
		    }
		}.bind(this));

	}

	addListener(path, func){
		this._listeners.push({
			path : path,
			handler : func
		})
	}

	send(path, data = {}){
		var m = path.match(/^(\w+)\/(\w+)\/?$/);
	    if(m == null) {
	        return;
	    }
	    var modName = m[1];
	    var actName = m[2];
	    this.socket.emit('message', { module : modName, action : actName, data : data });
	}
}

export default Client