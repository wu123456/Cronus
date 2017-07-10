var Redis = require('ioredis');
var redis = new Redis();

var SocketServer = require("../service/SocketServer.js");
var sockerServer = new SocketServer(8005, '/agot-chat');

sockerServer.start();

sockerServer.addModule({
	name : 'game',
	actions : {
		refresh : function(server, client, data){

			setInterval(function(){
				redis.get("need_refresh_" + data.user_id, function(err, result){
					console.log(err, result);
					if (!result) {
						return;
					}
					server.emit("message", {type: 'refresh', data: { code : 0, data : {need_refresh : 1}}});
				})
			}, 1000)
		}
	}
})