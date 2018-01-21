/*
*  A simple and lightweight markup parser for LibreSignage.
*
*  Styling classes:
*    ##TEXT\n      ==> Header. The number of # chars is the header size.
*    --TEXT--      ==> Strikethrough text.
*    __TEXT__      ==> Underlined text.
*    !!TEXT!!      ==> Bold text.
*    **TEXT**      ==> Italics text.
*    [img ADDRESS] ==> Embed image from ADDRESS.
*    [size SIZE]   ==> Begin text size block. SIZE = the text size in pt.
*    [/size]       ==> Close the most recent text size block.
*/

const MARKUP = {
	'header': {
		'regex': /^(#{1,6})(.*(?=\n))(?:\n)/,
		'make': function(match) {
			var weight = 0;
			if (match[1].length <= 6) {
				weight = match[1].length;
			} else {
				weight = 6;
			}
			return  '<h' + weight + ' class="display-' + weight + '">'
				+ match[2] + '</h' + weight +'>';
		}
	},
	'strikethrough': {
		'regex': /^--(.*)--/,
		'make': function(match) {
			var ret = "";
			ret += '<span style="text-decoration: line-through;">';
			ret += match[1] + '</span>';
			return ret;
		}
	},
	'underline': {
		'regex': /^__(.*)__/,
		'make': function(match) {
			var ret = "";
			ret += '<span style="text-decoration: underline;">';
			ret += match[1] + '</span>';
			return ret;
		}
	},
	'bold': {
		'regex': /^!!(.*)!!/,
		'make': function(match) {
			var ret = '';
			ret += '<span style="font-weight: bold;">';
			ret += match[1] + '</span>';
			return ret;
		}
	},
	'italic': {
		'regex': /^\*\*(.*)\*\*/,
		'make': function(match) {
			var ret = '';
			ret += '<span style="font-style: italic;">';
			ret += match[1] + '</span>';
			return ret;
		}
	},
	'image': {
		'regex': /^\[img \"([A-Za-z0-9\-\.\_\~\:\/\?\#\[\]\@\!\$\&\'\(\)\*\+\,\;\=\`\.]*)\"\]/,
		'make': function(match) {
			return '<img src="' + match[1] + '"></img>';
		}
	},
	'size_begin': {
		'regex': /^\[size ([0-9]*)\]/,
		'make': function(match) {
			return '<span style="font-size: ' + match[1] + 'pt;">';
		}
	},
	'size_close': {
		'regex': /^\[\/size\]/,
		'make': function(match) {
			return '</span>';
		}
	}
};

function markup_parse(str) {
	var tmp = str;
	var ret = "";
	var match = null;
	var flag_text = false;

	tmp = tmp.replace(/(\n)+/g, '\n');
	tmp = tmp.replace(/(\r\n)+/g, '\r\n');
	tmp = tmp.replace(/[\t ]+/g, ' ');

	while (tmp.length) {
		for (var k in MARKUP) {
			match = tmp.match(MARKUP[k].regex);
			if (match) {
				if (flag_text) {
					flag_text = false;
					ret += "</p>";
				}
				ret += MARKUP[k].make(match);
				tmp = tmp.replace(MARKUP[k].regex, '');
				break;
			}
		}
		if (!match) {
			if (!flag_text) {
				flag_text = true;
				ret += "<p>";
			}
			ret += tmp.substr(0, 1);
			tmp = tmp.substr(1);
		}
	}
	return ret;
}
