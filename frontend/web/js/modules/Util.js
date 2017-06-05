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

Util.count = function(obj){
    var objType = typeof obj;
    if(objType == "string"){
      	return obj.length;
    }else if(objType == "object"){
      	var objLen = 0;
      	for(var i in obj){
        	objLen++;
      	}
      	return objLen;
    }
    return false;
}

export default Util