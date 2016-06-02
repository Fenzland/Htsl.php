{
	"parse":function(doc){
		var _= this
		_
		return doc.map(function(line){
			return '<code>'
			     + _.parseString(line)
			     + '</code>'
			;
		}).join('');
	},
	"parseString":function(string){
		var wrap= this.wrap
		;
		return string.split(/>|&gt;/).map(function(result){
			       result= result.split(/<|&lt;/);
			       return result.length<=1
			           ?  result[0]
			           :  result[0]
			            + function(result){
				              return '!--'==result.slice(0,3)
				                  // Comment
				                  ?  wrap(
				                          wrap('&lt;','tag-wrapper')
				                        + wrap(
				                                  wrap('!--','comment-wrapper')
				                                + ' '
				                                + wrap(result.replace(/^!--|--$/g,'').trim(),'comment-content')
				                                + ' '
				                                + wrap('--','comment-wrapper')
				                               ,  'tag-content'
				                              )
				                        + wrap('&gt;','tag-wrapper')
				                      ,   'comment tag'
				                     )
				                  // Close Tag
				                  :  '/'===result.slice(0,1)
				                  ?  wrap(
				                          wrap('&lt;','tag-wrapper')
				                        + wrap(
				                                  wrap('/','tag-closer')
				                                + wrap(result.slice(1),'tag-name')
				                               ,  'tag-content'
				                              )
				                        + wrap('&gt;','tag-wrapper')
				                      ,   'element tag'
				                     )
				                  // Tag
				                  :  wrap(
				                          wrap('&lt;','tag-wrapper')
				                        + wrap(
				                               function(result){
					                               var items= result.match(/[\w-:]+(?:=".*?")?/g)
					                               ;
					                               return (
					                                          '!'==result.slice(0,1)
					                                       ?  wrap('!','definition-sign')
					                                       :  ''
					                                      )
					                                    + wrap(items.shift(),'tag-name')
					                                    + items.map(function(item){
						                                    var i=    item.indexOf('='),
						                                        name= item.slice(0,i)
						                                    ;
						                                    return ' '
						                                         + wrap(name,'attribute-name')
						                                         + wrap('=','attribute-delimiter')
						                                         + wrap('"','attribute-quotation')
						                                         + (
						                                               'style'==name
						                                            ?  function(result){
							                                               return result.match(/ ?[\w-]+:.*?(?:;|$)/g).map(function(item){
								                                               return wrap(item.match(/[\w-]+/),'style-name')
								                                                    + wrap(':','style-delimiter')
								                                                    + wrap(String(item.match(/\:.*?;/)||(item.match(/\:.*?$/)+';')).slice(1,-1).trim(),'style-value')
								                                                    + wrap(';','style-separator')
								                                               ;
							                                               }).join('');
						                                               }(item.slice(i+2,-1))
						                                            :  'on'==name.slice(0,2)
						                                            ?  wrap(item.slice(i+2,-1),'event-listener')
						                                            :  wrap(item.slice(i+2,-1),'attribute-value')
						                                           )
						                                         + wrap('"','attribute-quotation')
						                                    ;
					                                    }).join('')
				                               }(result)
				                             + (
				                                   '/'==result.slice(-1)
				                                ?  ' '
				                                 + wrap('/','tag-closer')
				                                :  ''
				                               )
				                           ,   'tag-content'
				                          )
				                        + wrap('&gt;','tag-wrapper')
				                      ,   '!'==result.slice(0,1)
				                       ?  'definition tag'
				                       :  'element tag'
				                     )
				              ;
			              }(result[1])
			       ;
		       }).join('')
		;
	},
	"wrap":function(content,className){
		if( content ){
			return '<code class="'+className+'">'+content+'</code>';
		}else{
			return '';
		}
	},
}
