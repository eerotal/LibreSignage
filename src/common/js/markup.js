/*
*  A simple and lightweight custom markup parser for LibreSignage.
*
*  Inline classes are classes that can't have other classes inside
*  them. Block classes like [p] or [container] on the other hand
*  can have nested classes.
*
*  Markup classes:
*    [h SIZE][/h]                     ==> Heading.
*    [lead][/lead]                    ==> Lead paragraph.
*    [b][/b]                          ==> Bold text.
*    [i][/i]                          ==> Italics text.
*    [img ADDRESS w h]                ==> Embed an image from ADDRESS.
*    [p][/p]                          ==> Close paragraph.
*    [color COLOR][/color]            ==> Color class.
*    [container T R B L][/container]  ==> Container with padding.
*    [xcenter][/xcenter]              ==> Horizontal centering container.
*    [columns][/columns]              ==> Column layout container.
*    [size SIZE][/size]               ==> Text size class.
*/

var _parser_ln_num = 1;

const MARKUPCLASS_TYPES = {
	BLOCK: 'block',
	META: 'meta'
}

class MarkupClass {
	constructor(type, name, args, subst_open,
			subst_close, callback, self_closing) {
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
		this.self_closing = self_closing;
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
		*  one. The third item is 'open' if the match opens a block and
		*  'close' otherwise. Null is returned if no match was found.
		*/
		var match = null;

		match = str.match(this.reg_open);
		if (match) {
			if (this.callback) {
				this.callback();
			}
			return [match[0], this._make_open_subst(match),
				'open'];
		}

		if (this.reg_close) {
			match = str.match(this.reg_close);
			if (match)  {
				if (this.callback) {
					this.callback();
				}
				return [match[0], this._make_close_subst(match),
					'close'];
			}
		}

		return null;
	}
}

class MarkupParseError extends Error {
	constructor(msg, ln, ...params) {
		super(...params)
		super.stack = "";
		this.ln = ln;
		this.msg = msg;
		if (ln != null) {
			super.message = this.msg + ' (@ln: ' +
					this.ln + ')';
		} else {
			super.message = this.msg;
		}
	}
}

const MARKUP = {
	'heading': new MarkupClass(
		'block',
		'h',
		{'size': { 'arg_type': 'int' }},
		'<h1 style="font-size: %0vh !important;">',
		'</h1>',
		null,
		false
	),
	'lead': new MarkupClass(
		'block',
		'lead',
		null,
		'<p class="lead">',
		'</p>',
		null,
		false
	),
	'bold': new MarkupClass(
		'block',
		'b',
		null,
		'<span style="font-weight: bold;">',
		'</span>',
		null,
		false
	),
	'italic': new MarkupClass(
		'block',
		'i',
		null,
		'<span style="font-style: italic;">',
		'</span>',
		null,
		false
	),
	'image': new MarkupClass(
		'block',
		'img',
		{
			'url': {'arg_type': 'url'},
			'width': {'arg_type': 'int'},
			'height': {'arg_type': 'int'}
		},
		'<img src="%0" style="width: %1vw; height: %2vh">',
		'</img>',
		null,
		true
	),
	'paragraph': new MarkupClass(
		'block',
		'p',
		null,
		'<p>',
		'</p>',
		null,
		false
	),
	'size': new MarkupClass(
		'block',
		'size',
		{'font_size': {'arg_type': 'int'}},
		'<span style="font-size: %0vh;">',
		'</span>',
		null,
		false
	),
	'color': new MarkupClass(
		'block',
		'color',
		{'font_size': {'arg_type': 'str'}},
		'<span style="color: %0;">',
		'</span>',
		null,
		false
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
		null,
		false
	),
	'xcenter': new MarkupClass(
		'block',
		'xcenter',
		null,
		`<div style="margin-left: auto;
				margin-right: auto;
				text-align: center;
				width: auto;
				height: auto;">`,
		'</div>',
		null,
		false
	),
	'columns': new MarkupClass(
		'block',
		'columns',
		null,
		`<div style="display: flex;
				flex-direction: row;
				width: auto;
				height: auto;">`,
		'</div>',
		null,
		false
	),
	'meta': new MarkupClass(
		'meta',
		'meta',
		{'type': {'arg_type': 'str'}},
		'',
		null,
		function() {
			_parser_ln_num++;
		},
		true
	),
}

function markup_preprocess(str) {
	var tmp = str;

	if (tmp == null) {
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
	var open = [];

	_parser_ln_num = 1;
	tmp = markup_preprocess(str);

	while (tmp.length) {
		for (var k in MARKUP) {
			match = MARKUP[k].match(tmp);
			if (!match) {
				continue;
			}
			ret += match[1];
			tmp = tmp.replace(match[0], '');

			if (match[2] == 'open' &&
				!MARKUP[k].self_closing) {
				open.push(k);
			} else if (match[2] == 'close' &&
				!MARKUP[k].self_closing) {
				if (open[open.length - 1] == k) {
					open.pop();
					continue;
				}

				throw new MarkupParseError(
					'Unexpected block "' + k +
					'" closed. Expected to ' +
					'close "' +
					 open[open.length - 1] +
					'".', _parser_ln_num
				);
			}
		}
		if (!match) {
			ret += tmp.substr(0, 1);
			tmp = tmp.substr(1);
		}
	}
	return ret;
}

