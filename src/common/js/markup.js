/*
*  A simple and lightweight custom markup parser for LibreSignage.
*
*  Inline classes are classes that can't have other classes inside
*  them. Block classes like [p] or [container] on the other hand
*  can have nested classes.
*
*  Markup classes:
*    [h WEIGHT]TEXT[/h]  ==> Heading.
*    [lead]TEXT[/lead]   ==> Lead paragraph.
*    [b]TEXT[/b]         ==> Bold text.
*    [i]TEXT[/i]         ==> Italics text.
*    [img ADDRESS]       ==> Embed image from ADDRESS.
*    [p]                 ==> Open paragraph.
*    [/p]                ==> Close paragraph.
*    [color COLOR]       ==> Open a color block.
*    [/color]            ==> Close a color block.
*    [container T R B L] ==> Open a container.
*    [/container]        ==> Close a container.
*    [size SIZE]         ==> Open text size block.
*    [/size]             ==> Close the most recent text size block.
*/

var _parser_ln_num = 0;

const MARKUPCLASS_TYPES = {
	BLOCK: 'block',
	META: 'meta'
}

class MarkupClass {
	constructor(type, name, args, subst_open, subst_close, callback) {
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

		if (subst_open != null) {
			this.subst_open = subst_open;
		} else {
			throw new Error('MarkupClass opening ' +
					'substitution undefined!');
		}

		this.subst_close = subst_close;
		this.callback = callback;
		this._make_regexes();
	}

	_make_arg_rstr(arg_type) {
		/*
		*  Return a variable regex string based on arg_type.
		*/
		switch(arg_type) {
			case 'int':
				return '([0-9]+)';
			case 'str':
				return '(\\w+)';
			case 'url':
				return '([A-Za-z0-9\\-\\.\\_\\~' +
					'\\:\\/\\?\\#\\[\\]\\@\\!' +
					'\\$\\&\\\'\\(\\)\\*\\+\\,' +
					'\\;\\=\\`\\.]+)';
			default:
				throw new Error('Unknown class ' +
						'variable type: ' +
						arg_type);
		}
	}

	_make_regexes() {
		// Pre-create the regexes.
		var regex_str = '^\\[' + this.name;
		for (var a in this.args) {
			regex_str += ' ' + this._make_arg_rstr(this.args[a].arg_type);
		}
		regex_str += '\\]';
		this.reg_open = new RegExp(regex_str);

		if (this.subst_close) {
			this.reg_close = new RegExp('^\\[\\/' + this.name +
							'\\]');
		} else {
			this.reg_close = null;
		}
	}

	_make_open_subst(match) {
		/*
		*  Make the opening substitution string based on the match array.
		*/
		var tmp = this.subst_open;
		var n = 0;
		if (this.args) {
			n = Object.keys(this.args).length;
			for (var i = 0; i < n; i++) {
				tmp = tmp.replace('%' + i, match[i + 1]);
			}
		}
		return tmp;
	}

	_make_close_subst() {
		// Return the closing substitution string.
		return this.subst_close;
	}

	match(str) {
		/*
		*  Match with str. Returns an array with the whole match
		*  as the first item and the substitution string as the second
		*  one. Null is returned if no match was found.
		*/
		var match = null;

		match = str.match(this.reg_open);
		if (match) {
			if (this.callback) {
				this.callback();
			}
			return [match[0], this._make_open_subst(match)];
		}

		if (this.reg_close) {
			match = str.match(this.reg_close);
			if (match)  {
				if (this.callback) {
					this.callback();
				}
				return [match[0], this._make_close_subst(match)];
			}
		}

		return null;
	}
}

const MARKUP = {
	'heading': new MarkupClass(
		'block',
		'h',
		{'weight': { 'arg_type': 'int' }},
		'<h class="display-%0">',
		'</h>',
		null
	),
	'lead': new MarkupClass(
		'block',
		'lead',
		null,
		'<p class="lead">',
		'</p>',
		null
	),
	'bold': new MarkupClass(
		'block',
		'b',
		null,
		'<span style="font-weight: bold;">',
		'</span>',
		null
	),
	'italic': new MarkupClass(
		'block',
		'i',
		null,
		'<span style="font-style: italic;">',
		'</span>',
		null
	),
	'image': new MarkupClass(
		'block',
		'img',
		{'url': {'arg_type': 'url'}},
		'<img src="%0">',
		'</span>',
		null
	),
	'paragraph': new MarkupClass(
		'block',
		'p',
		null,
		'<p>',
		'</p>',
		null
	),
	'size': new MarkupClass(
		'block',
		'p',
		{'font_size': {'arg_type': 'int'}},
		'<span style="font-size: %0pt;">',
		'</span>',
		null
	),
	'color': new MarkupClass(
		'block',
		'color',
		{'font_size': {'arg_type': 'str'}},
		'<span style="color: %0;">',
		'</span>',
		null
	),
	'container': new MarkupClass(
		'block',
		'container',
		{
			'top': {'arg_type': 'int'},
			'right': {'arg_type': 'int'},
			'bottom': {'arg_type': 'int'},
			'left': {'arg_type': 'int'},
		},
		'<div style="padding: %0vh %1vw %2vh %3vw;">',
		'</div>',
		null
	),
	'meta': new MarkupClass(
		'meta',
		'meta',
		{'type': {'arg_type': 'str'}},
		'',
		null,
		function() {
			_parser_ln_num++;
		}
	),
}

function markup_preprocess(str) {
	var tmp = str;

	if (!tmp) {
		return null;
	}

	// Check for reserved classes in the input str.
	if (tmp.match(/\[meta.*\]/g)) {
		throw new Error("LibreSignage: markup.js: Reserved " +
				"meta class used in input.");
	}

	// Replace newlines with '[meta newline]'.
	tmp = tmp.replace(/(\r\n|\n)/g, '[meta newline]');

	// Replace whitespaces with ' '.
	tmp = tmp.replace(/\s+/g, ' ');

	return tmp;
}

function markup_parse(str) {
	var tmp = str;
	var ret = "";
	var match = null;

	tmp = markup_preprocess(str);

	while (tmp.length) {
		for (var k in MARKUP) {
			match = MARKUP[k].match(tmp);
			if (!match) {
				continue;
			}

			ret += match[1];
			tmp = tmp.replace(match[0], '');
		}
		if (!match) {
			ret += tmp.substr(0, 1);
			tmp = tmp.substr(1);
		}
	}
	return ret;
}

