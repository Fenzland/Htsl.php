'use strict';
window.Code= function(){
	var Code=     function(){},
	    parsers=  {},
	    base_dir= window.document.currentScript.src.replace(/[^\\/]*$/,'')
	;

	//**************************//
	//* Static Methods of Code *//
	//**************************//

	Code.loadFromElement=function(element){
		var _= new this
		;
		_.originElement= element
		_.document=element.innerHTML.split('\n');
		return _;
	};

	Code.loadFromElementSet=function(element){
		var _= new this
		;
		_.originElement= element;
		_.document=[];
		Array.prototype.slice.call(element.children).forEach(function(line){
			this.document.push(line.innerHTML);
		},_);
		return _;
	};

	Code.loadParser=function(type){
		if( !parsers[type] ){
			parsers[type]= load('parsers/'+type+'.js');;
		};
		return parsers[type];
	};


	//**************************//
	//* Object Methods of Code *//
	//**************************//

	Code.prototype.setType=function(type){
		this.type= type;
		this.parser= Code.loadParser(type);
		return this;
	};

	Code.prototype.renderBack=function(){
		return this.setRenderTarget(this.originElement).render();
	};

	Code.prototype.setRenderTarget=function(renderTarget){
		this.renderTarget= renderTarget;
		return this;
	};

	Code.prototype.createRenderTarget=function(renderTarget){
		this.renderTarget= document.createElement('code');
		return this.renderTarget;
	};

	Code.prototype.render=function(){
		var renderTarget=this.renderTarget || this.createRenderTarget()
		;
		renderTarget.outerHTML= '<code codeset="codeset" type="'+this.type+'">'+this.parser.parse(this.document)+'</code>';
		return this;
	};

	return Code;

	function load(url){
		var _= new XMLHttpRequest()
		;
		_.open("GET",base_dir+url,false);
		_.send(null);
		return eval('('+_.responseText+')');
	}
}();

function z(data){
	console.log(data);
	return data;
}
