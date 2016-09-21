{
	"parse":function(doc){
		doc= new function(doc){
			var i= 0,
			    m= doc.length-1
			;
			this.current= function(){
				return doc[i];
			};
			this.next= function(){
				if( i>=m ){
					return false;
				}
				return doc[++i];
			};
			this.nextExists= function(){
				return i<m;
			};
			this.prevExists= function(){
				return i>0;
			};
		}(doc);

		var result= []
		;

		result.push(this.parseFirstLine(doc.current()) || this.parseLine(doc.current()))

		while(doc.nextExists()){
			result.push(this.parseLine(doc.next()));
		}

		return result.join('');
	},
	"parseFirstLine":function(line){
		if( line.match(/^[A-Z\.\d]+$/) ){
			return '<code class="doctype">'+line+'</code>';
		}else{
			return false;
		}
	},
	"parseLine":function(line){
		var indent= line.search(/[^\t]/);
		var content= new function(content){
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
		}(line);
		switch( line.match(/[^\t]/)[0] ){
			case '-': return this.parseTagNode(content);
			case '~': return this.parseControlNode(content);
			case '!': return this.parseCommentNode(content);
			case '@': return this.parseDocNode(content);
			default: return this.parseStringNode(content);
		}
	},
	"parseTagNode":function(content){
		var wrap=this.wrap
		;
		return wrap(
		                 content.cut(/\t*/).split('').map(function(item){
			                 return wrap(item,'indent');
		                 }).join('')
		               + wrap(content.cut(/-/),'tag-sign')
		               + wrap(content.cut(/[\w-:]+/),'tag-name')
		               + function(result){
			                 if( !result ){ return ''; }
			                 return wrap('(','tag-param-sign')
			                      + function(result){
				                        return result.split('|').map(function(item){
					                        return wrap(item,'tag-param');
				                        }).join(wrap('|','tag-param-separator'));
			                        }(result.slice(1,-1))
			                      + wrap(')','tag-param-sign');
		                 }(content.cut(/\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)/))
		               + content.groupWork(
		                 [
			                 {
				                 // id
				                 "pattern":/ #(?:[^ ]+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\))+/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('#','id-sign')
					                      + wrap(result.slice(2),'id')
					                 ;
				                 }
			                 },
			                 {
				                 // title
				                 "pattern":/ \^(?:(?:[^ ]| (?=[a-zA-Z0-9]))+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\))+/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('^','title-sign')
					                      + wrap(result.slice(2),'title')
					                 ;
				                 }
			                 },
			                 {
				                 // link
				                 "pattern":/ @(?:(?:[^ ]| (?=[a-zA-Z0-9]))+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\))+/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('@','link-sign')
					                      + wrap(result.slice(2),'link')
					                 ;
				                 }
			                 },
			                 {
				                 // target
				                 "pattern":/ (?:>|&gt;)(?:(?:[^ ]| (?=[a-zA-Z0-9]))+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\))+/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('>','target-sign')
					                      + wrap(result.replace(/^ (?:>|&gt;)/,''),'target')
					                 ;
				                 }
			                 },
			                 {
				                 // alt
				                 "pattern":/ _(?:(?:[^ ]| (?=[a-zA-Z0-9]))+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\))+/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('_','alt-sign')
					                      + wrap(result.slice(2),'alt')
					                 ;
				                 }
			                 },
			                 {
				                 // event
				                 "pattern":/ %[\w-]+\{(?:>|&gt;).*?(?:<|&lt;)\}/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('%','event-sign')
					                      + wrap(result.match(/[\w-:]+/),'event-name')
					                      + wrap('{&gt;','event-listener-wrapper')
					                      + wrap(result.match(/\{(?:>|&gt;).*?(?:<|&lt;)\}/)[0].replace(/^\{&gt;|^\{>|<\}$|&lt;\}$/g,''),'event-listener')
					                      + wrap('&lt;}','event-listener-wrapper')
					                 ;
				                 }
			                 },
			                 {
				                 // name-value
				                 "pattern":/ (?:<|&lt;).*?(?:>|&gt;)/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('&lt;','name-value-wrapper')
					                      + function(result){
						                        return result.split('|').map(function(item){
							                        return wrap(item,'name-value-item');
						                        }).join(wrap('|','name-value-separator'));
					                        }(result.replace(/^ &lt;|^ <|>$|&gt;$/g,''))
					                      + wrap('&gt;','name-value-wrapper')
					                 ;
				                 }
			                 },
			                 {
				                 // class-name
				                 "pattern":/ (?:\.(?:[\w-]+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)))+/,
				                 //        / (?:\.(?:[\w-]+|\(<--X-->\)))+/ ; <--X-->= /(?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)/ ;
				                 "callback":function(result){
					                 return ' '
					                      + result.match(/\.[^\.]*/g).map(function(item){
						                        return wrap(item.slice(0,1),'class-name-sign')
						                             + wrap(item.slice(1),'class-name')
						                        ;
					                        }).join('')
					                 ;
					                 return wrap(result,'class-name');
				                 }
			                 },
			                 {
				                 // style
				                 "pattern":/ \[.*?\]/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('[','style-wrapper')
					                      + function(result){
						                        return result.match(/ ?[\w-]+:.*?;/g).map(function(item){
							                        return wrap(item.match(/[\w-]+/),'style-name')
							                             + wrap(':','style-delimiter')
							                             + wrap(item.match(/\:.*?;/)[0].slice(1,-1).trim(),'style-value')
							                             + wrap(';','style-separator')
							                        ;
						                        }).join('');
					                        }(result.slice(2,-1))
					                      + wrap(']','style-wrapper')
					                 ;
				                 }
			                 },
			                 {
				                 // attributes
				                 "pattern":/ \{.*?\}/,
				                 "callback":function(result){
					                 return ' '
					                      + wrap('{','attribute-wrapper')
					                      + function(result){
						                        return result.match(/(?:[^\(\)=;]+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\))(?:=(?:[^\(\);]+|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)))?;/g).map(function(item){
							                        var attributeName= item.match(/^([^\(\)]+?|\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\((?:[^\(\)]*?(\(<--X-->\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\)[^\(\)]*?)*)\))[=;]/)[0].slice(0,-1)
							                        ;
							                        item= item.slice(attributeName.length);
							                        return wrap(attributeName,'attribute-name')
							                             + (
							                                   ';'==item
							                                ?  ''
							                                :  wrap('=','attribute-delimiter')
							                                 + wrap(item.slice(1,-1),'attribute-value')
							                               )
							                             + wrap(';','attribute-separator')
							                        ;
						                        }).join('');
					                        }(result.slice(2,-1))
					                      + wrap('}','attribute-wrapper')
					                 ;
				                 }
			                 },
		                 ])
		                 ,'tag-line');
	},
	"parseControlNode":function(content){
		var wrap= this.wrap;
		return wrap(
			(
				content.cut(/\t*/).split('').map(function(item){
					return wrap(item,'indent');
				}).join('')
				+
				content
			)
			,
			'control-line'
		);
	},
	"parseCommentNode":function(content){
		var wrap= this.wrap;
		return wrap((
			content.cut(/\t*/).split('').map(function(item){
				return wrap(item,'indent');
			}).join('')
			+
			content
		),'comment-line');
	},
	"parseDocNode":function(content){
		var wrap= this.wrap;
		return wrap((
			content.cut(/\t*/).split('').map(function(item){
				return wrap(item,'indent');
			}).join('')
			+
			content
		),'doc-line');
	},
	"parseStringNode":function(content){
		var wrap= this.wrap;
		return wrap((
			content.cut(/\t*/).split('').map(function(item){
				return wrap(item,'indent');
			}).join('')
			+
			content
		),'string-line');
	},
	"wrap":function(content,className){
		if( content ){
			return '<code class="'+className+'">'+content+'</code>';
		}else{
			return '';
		}
	},
}
