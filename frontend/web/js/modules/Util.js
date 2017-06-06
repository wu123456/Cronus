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

Util.getPoint = function(obj){ //获取某元素以浏览器左上角为原点的坐标
    var t = obj.offsetTop; //获取该元素对应父容器的上边距
    var l = obj.offsetLeft; //对应父容器的上边距
    //判断是否有父容器，如果存在则累加其边距
    while (obj = obj.offsetParent) {//等效 obj = obj.offsetParent;while (obj != undefined)
        t += obj.offsetTop; //叠加父容器的上边距
        l += obj.offsetLeft; //叠加父容器的左边距
    }
    return {top: t, left: l};
}

export default Util