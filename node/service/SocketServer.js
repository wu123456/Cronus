var SocketServer = function(port, path, modules){
	this._modules = modules || {};
	this._port = port;
	this._path = path;
	this._server = require("socket.io")();
}

SocketServer.prototype.addModule = function(module){
	return this._modules[module['name']] = module['actions'];
}

SocketServer.prototype.start = function(){
	var self = this;
	self._server.attach(this._port, { "path" : this._path });
	self._server.on("connection", function(client) {
		// global.logger.info("connection");
		console.log("connection");
		client.on("message", function(e) {
			var modName = e.module;
			var actName = e.action;
			var module = self._modules[modName];
			var action;
			console.log(e);

			if (!module) {
				// throw new Error("Module " + modName + " not exist.");
				console.log("Module " + modName + " not exist.");
				return;
			}

			action = module[actName];

			if (!action) {
				// throw new Error("Module " + modName + " doesn't have " + actName + " action");
				console.log("Module " + modName + " doesn't have " + actName + " action");
				return;
			}

			action(self._server, client, e.data);

		}).on("disconnect", function() {
			
		});
	});
}

SocketServer.prototype.stop = function(){
	return this._server.close();
}

module.exports = SocketServer;