var $ = require('jquery');
var markup = require('ls-markup');
var util = require('ls-util');

exports.Preview = class Preview {
	constructor(preview, editor, getter) {
		this.preview = $(preview);
		this.editor = $(editor);
		this.getter = getter;

		// Setup preview styling and metadata.
		this.preview.contents().find('head').append(
			`<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<link rel="stylesheet" href="/libs/bootstrap/dist/css/bootstrap.min.css"></link>
			<link rel="stylesheet" href="/common/css/default.css"></link>
			<link rel="stylesheet" href="/app/css/animations.css"></link>
			<link rel="stylesheet" href="/app/css/display.css"></link>
			<link rel="stylesheet" href="/common/css/markup.css"></link>`
		);
		this.editor.on('keyup', () => { this.update(); })
	}

	update() {
		var html = null;
		try {
			html = markup.parse(
				util.sanitize_html(
					this.getter()
				)
			);
		} catch (e) {
			if (!(e instanceof markup.MarkupParseError)) {
				throw e;
			}
		}

		if (html) {
			this.preview.contents().find('body').html(html);
		}
	}
}
