/*
*  A simple and lightweight custom markup parser for LibreSignage.
*
*  Inline classes are classes that can't have other classes inside
*  them. Block classes like [p] or [container] on the other hand
*  can have nested classes.
*
*  Inline classes:
*    [h<n>]TEXT[/h]      ==> Heading.
*    [lead]TEXT[/lead]   ==> Lead paragraph.
*    **TEXT**            ==> Bold text.
*    __TEXT__            ==> Italics text.
*    [img ADDRESS]       ==> Embed image from ADDRESS.

*  Block classes:
*    [p]                 ==> Open paragraph.
*    [/p]                ==> Close paragraph.
*    [color COLOR]       ==> Open a color block.
*    [/color]            ==> Close a color block.
*    [container T R B L] ==> Open a container.
*    [/container]        ==> Close a container.
*    [size SIZE]         ==> Open text size block.
*    [/size]             ==> Close the most recent text size block.
*/

MARKUPCLASS_TYPES = {
	BLOCK: 'block',
	META: 'meta'
}

class MarkupClass {
	constructor(type, name, args, subst_open, subst_close) {
		if (Object.values(MARKUPCLASS_TYPES).indexOf(type) != -1) {
			this.type = type;
		} else {
			throw new Error('Invalid MarkupClass type.');
		}

		if (name) {
			this.name = name;
		} else {
			throw new Error('MarkupClass name undefined!');
		}

		this.args = args;

		if (subst_open) {
			this.subst_open = subst_open;
		} else {
			throw new Error('MarkupClass opening ' +
					'substitution undefined!');
		}

		if (subst_close) {
			this.subst_close = subst_close;
		} else {
			throw new Error('MarkupClass closing ' +
					'substitution undefined!');
		}

		this._make_regexes();
	}

	_make_regexes() {
		// Pre-create the regexes.
		var regexp_str = '^\\[' + this.name;
		for (var a in this.args) {
			switch(this.args[a].var_type) {
				case 'int':
					regexp_str += ' ([0-9]+)';
					break;
				case 'str':
					regexp_str += ' (\\w+)';
					break;
				default:
					throw new Error('Unknown class ' +
							'argument type!')
			}
		}
		regexp_str += '\\]';

		this.reg_open = new RegExp(regexp_str);
		this.reg_close = new RegExp('^\\[\\/' + this.name + '\\]');
	}

	_make_open_subst(match) {
		/*
		*  Make the opening substitution string
		*  based on the match array.
		*/
		var tmp = this.subst_open;
		for (var i = 0; i < Object.keys(this.args).length; i++) {
			tmp = tmp.replace('%' + i, match[i + 1]);
		}
		return tmp;
	}

	_make_close_subst() {
		// Return the closing substitution string.
		return this.subst_close;
	}

	match(str) {
		/*
		*  Match with str. Returns the substitution string
		*  if the string matches and null otherwise.
		*/
		var match = null;

		match = str.match(this.reg_open);
		if (match) {
			console.log(this.name + ' ==> open');
			return this._make_open_subst(match);
		}

		match = str.match(this.reg_close);
		if (match)  {
			console.log(this.name + ' ==> close');
			return this._make_close_subst();
		}

		return null;
	}
}

const MARKUP = {
	'header': {
		'type': 'inline',
		'regex': /^\[h([0-9])\](.*?)\[\/h\]/,
		'make': function(match) {
			var weight = 0;
			if (match[1].length <= 6) {
				weight = match[1].length;
			} else {
				weight = 6;
			}
			return  '<h' + weight + ' class="display-' +
				weight + '">' + match[2] + '</h' +
				weight +'>';
		}
	},
	'lead': {
		'type': 'inline',
		'regex': /^\[lead\](.*?)\[\/lead\]/,
		'make': function(match) {
			return '<p class="lead">' +
				match[1] + '</p>';
		}
	},
	'bold': {
		'type': 'inline',
		'regex': /^\*\*(.*)\*\*/,
		'make': function(match) {
			var ret = '';
			ret += '<span style="font-weight: bold;">';
			ret += match[1] + '</span>';
			return ret;
		}
	},
	'italic': {
		'type': 'inline',
		'regex': /^__(.*)__/,
		'make': function(match) {
			var ret = '';
			ret += '<span style="font-style: italic;">';
			ret += match[1] + '</span>';
			return ret;
		}
	},
	'image': {
		'type': 'inline',
		'regex': /^\[img \"([A-Za-z0-9\-\.\_\~\:\/\?\#\[\]\@\!\$\&\'\(\)\*\+\,\;\=\`\.]*)\"\]/,
		'make': function(match) {
			return '<img src="' + match[1] + '"></img>';
		}
	},
	'paragraph_open': {
		'type': 'block_open',
		'regex': /^\[p\]/,
		'make': function(match) {
			return '<p>';
		}
	},
	'paragraph_close': {
		'type': 'block_close',
		'regex': /^\[\/p\]/,
		'make': function(match) {
			return '</p>';
		}
	},
	'size_open': {
		'type': 'block_open',
		'regex': /^\[size ([0-9]*)\]/,
		'make': function(match) {
			return '<span style="font-size: ' + match[1] + 'pt;">';
		}
	},
	'size_close': {
		'type': 'block_close',
		'regex': /^\[\/size\]/,
		'make': function(match) {
			return '</span>';
		}
	},
	'color_open': {
		'type': 'block_open',
		'regex': /^\[color ([a-z]*|#[0-9]{6})\]/,
		'make': function(match) {
			return '<span style="color: ' + match[1] + ';">';
		}
	},
	'color_close': {
		'type': 'block_close',
		'regex': /^\[\/color\]/,
		'make': function(match) {
			return '</span>';
		}
	},
	'container_open': {
		'type': 'block_open',
		'regex': /^\[container ([0-9]*) ([0-9]*) ([0-9]*) ([0-9]*)\]/,
		'make': function(match) {
			var ret = '<div style="padding: '
			ret += match[1] + 'vh ';
			ret += match[2] + 'vw ';
			ret += match[3] + 'vh ';
			ret += match[4] + 'vw;">';
			return ret;
		}
	},
	'container_close': {
		'type': 'block_close',
		'regex': /^\[\/container\]/,
		'make': function(match) {
			return '</div>';
		}
	},
	'meta_newline': {
		'type': 'meta',
		'regex': /^\[_meta newline\]/,
		'make': function() { return ''; }
	}
};

function markup_preprocess(str) {
	var tmp = str;

	if (!tmp) {
		return null;
	}

	// Check for reserved classes in the input str.
	if (tmp.match(/\[_meta newline\]/g)) {
		console.error("LibreSignage: markup.js: Reserved " +
				"class used in input.");
		return null;
	}

	// Replace newlines with '[_meta newline]'.
	tmp = tmp.replace(/(\r\n|\n)/g, '[_meta newline]');

	// Replace whitespaces with ' '.
	tmp = tmp.replace(/\s+/g, ' ');

	return tmp;
}

function markup_parse(str) {
	var tmp = str;
	var ret = "";
	var match = null;

	var open = [];
	var ln = 0;

	tmp = markup_preprocess(str);

	while (tmp.length) {
		for (var k in MARKUP) {
			match = tmp.match(MARKUP[k].regex);
			if (!match) { continue; }

			if (k == 'meta_newline') {
				ln++;
			}

			ret += MARKUP[k].make(match);
			tmp = tmp.replace(MARKUP[k].regex, '');

			// Some simple syntax error detection.
			if (MARKUP[k].type == 'block_open') {
				open.push(k);
			} else if (MARKUP[k].type == 'block_close') {
				if (open[open.length - 1] ==
					k.replace('_close', '_open')) {
					open.pop();
				} else {
					console.warn('LibreSignage: ' +
						'markup.js: Unexpected ' +
						'block closed (@ ' +
						ln + ')');
				}
			}
		}
		if (!match) {
			ret += tmp.substr(0, 1);
			tmp = tmp.substr(1);
		}
	}
	if (open.length) {
		console.warn('LibreSignage: markup.js: Unclosed blocks ' +
				'after parsing: ' + open.join(', '));
		return '';
	}
	return ret;
}

