{
	"parse":function(doc){
		return doc.map(function(line){
			return '<code>'
			     + line
			     + '</code>'
			;
		}).join('');
	},
}
