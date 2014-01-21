// -------------------------------------------------------------------
// markItUp!
// -------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// -------------------------------------------------------------------

mySettings = {
	previewParserPath:	'/' + base_url + 'admin/auxiliary/markdown_preview/',
	onShiftEnter:		{keepDefault:false, openWith:'\n\n'},
	markupSet: [
		{name:'Heading 3', key:'3', openWith:'### ', placeHolder:'Your title here...' },
		{name:'Heading 4', key:'4', openWith:'#### ', placeHolder:'Your title here...' },
		{name:'Heading 5', key:'5', openWith:'##### ', placeHolder:'Your title here...' },
		{name:'Heading 6', key:'6', openWith:'###### ', placeHolder:'Your title here...' },
		{separator:'---------------' },
		{name:'Bold', key:'B', openWith:'**', closeWith:'**'},
		{name:'Italic', key:'I', openWith:'_', closeWith:'_'},
		{separator:'---------------' },
		{name:'Bulleted List', openWith:'- ' },
		{name:'Numeric List', openWith:function(markItUp) {
			return markItUp.line+'. ';
		}},
		{name:'Preview', call:'preview', className:"hidden"}
	]
};

// mIu nameSpace to avoid conflict.
miu = {
	markdownTitle: function(markItUp, c) {
		heading = '';
		n = $.trim(markItUp.selection || markItUp.placeHolder).length;
		for(i = 0; i < n; i++) {
			heading += c;
		}
		return '\n' + heading;
	}
};