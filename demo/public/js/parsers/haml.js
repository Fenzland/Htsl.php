{
	"parse":function(doc){
		var _= this
		;
		return doc.map(function(line){
			return '<code>'
			     + _.parseLine(line)
			     + '</code>'
			;
		}).join('');
	},
	"parseLine":function(line){
		switch( line.match(/[^\t]/)[0] ){
			case '!': return
			case '/': return this.wrap(line,'comment-line');
			default: return this.wrap(line,'string-line');
			case '%':
			case '.':
			case '#':
		}
		var wrap= this.wrap,
		    content= new function(content){
			    this.content= content;
			    this.cut= function(pattern){
				    if( 0!==this.content.search(pattern) ){ return false; }
				    var result= this.content.match(pattern)[0]
				    ;
				    this.content= this.content.slice(result.length);
				    return result;
			    }
			    this.groupWork= function(group){
				    var result= ''
				    ;
				    while(1){
					    var l= this.content.length
					    ;
					    group.forEach(function(item){
						    var current_result= this.cut(item.pattern)
						    ;
						    if( current_result ){
							    result+= item.callback(current_result);
						    }
					    },this);
					    if( l==this.content.length ){ return result; }
				    }
			    }
			    this.toString= function(){ return this.content; }
		    }(line)
		;
		return wrap(
		               content.cut(/\t*/)
		             + content.groupWork([
			               {
				               // tag
				               "pattern":/%[\w-:]+/,
				               "callback":function(result){
					               return ' '
					                    + wrap('%','tag-sign')
					                    + wrap(result.slice(1),'tag-name')
					               ;
				               }
			               },
			               {
				               // id
				               "pattern":/#\w+/,
				               "callback":function(result){
					               return ' '
					                    + wrap('#','id-sign')
					                    + wrap(result.slice(1),'id')
					               ;
				               }
			               },
			               {
				               // class-name
				               "pattern":/\.\w+/,
				               "callback":function(result){
					               return ' '
					                    + wrap('#','id-sign')
					                    + wrap(result.slice(1),'id')
					               ;
				               }
			               },
			               {
				               // ruby-attribute
				               "pattern":/\{.*?\}/,
				               "callback":function(result){
					               return wrap('{','ruby-attribute-wrapper')
					                    + function(result){
						                      return result.match(/:[\w:-]+=(>|&gt;)("[^"]*"|'[^']*'|[^,]*?),?/g).map(function(result){
							                      return wrap(':','ruby-attribute-name-sign')
							                           + wrap(result.match(/:[\w:-]+/)[0].slice(1),'ruby-attribute-name')
							                           + wrap('=>','ruby-attribute-delimiter')
							                           + wrap(result.match(/=(>|&gt;).+/)[0].replace(/^=(>|&gt;)|,$/g,''),'ruby-attribute-value')
						                      }).join(wrap(',','ruby-attribute-separator'));
					                      }(result.slice(1,-1))
					                    + wrap('}','ruby-attribute-wrapper')
					               ;
				               }
			               },
			               {
				               // html-attribute
				               "pattern":/\(.*?\)/,
				               "callback":function(result){
					               return wrap('(','html-attribute-wrapper')
					                    + function(result){
						                      return result.match(/[\w:-]+="[^"]*" ?/g).map(function(result){
							                      return wrap(result.match(/[\w:-]+/)[0],'html-attribute-name')
							                           + wrap('=','html-attribute-delimiter')
							                           + wrap(result.match(/=.+/)[0].slice(1),'html-attribute-value')
						                      }).join('');
					                      }(result.slice(1,-1))
					                    + wrap(')','html-attribute-wrapper')
					               ;
				               }
			               },
			               {
				               // string
				               "pattern":/[ =].*/,
				               "callback":function(result){
					               return wrap(result,'string')
					               ;
				               }
			               },
		               ])
		            ,  'tag-line');
	},
	"wrap":function(content,className){
		if( content ){
			return '<code class="'+className+'">'+content+'</code>';
		}else{
			return '';
		}
	},
}
