let Util = {};

Util.empty = function(el){
	if(Util.isArray(el)){
		return el.length == 0;
	}else if(typeof el == 'object'){
		for(let i in el){
			return false;
		}
		return true;
	}
	return el == '';
}

Util.isArray = function(value){
	return Object.prototype.toString.call(value) == '[object Array]';
}





export default Util