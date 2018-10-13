var $ = require('jquery');
var uic = require('ls-uicontrol');
var popup = require('ls-popup');
var val = require('ls-validator');

module.exports.AssetUploader = class AssetUploader {
	constructor(api) {
		this.API = api;
		this.flag_ready = false;
		this.defer_ready = () => { return !this.flag_ready; }

		this.UI = new uic.UIController({
			'POPUP': new uic.UIStatic(
				elem = new popup.Popup(
					$("#asset-uploader").get(0),
					() => {
						/*
						*  Remove upload button event listener and
						*  reset the file selector label on close.
						*/
						this.UI.get(
							'UPLOAD_BUTTON'
						).get_elem().off('click');
						this.UI.get('FILESEL_LABEL').set('Choose a file');
					}
				),
				perm = (d) => { return true; },
				enabler = (elem, s) => { elem.visible(s); },
				attach = null,
				defer = null,
				getter = null,
				setter = null
			),
			'FILESEL': new uic.UIInput(
				elem = $("#asset-uploader-filesel"),
				perm = (d) => { return d['s']; },
				enabler = null,
				attach = {
					'change': (e) => {
						var label = '';
						var files = e.target.files;
						for (let i = 0; i < files.length; i++) {
							if (label.length !== 0) { label += ', '; }
							label += files.item(i).name;
						}
						this.UI.get('FILESEL_LABEL').set(label);
					}
				},
				defer = this.defer_ready,
				mod = null,
				getter = (elem) => { return elem.prop('files'); },
				setter = (elem, s) => { elem.prop('files', s); },
				clearer = (elem) => { elem.prop('files', null); }
			),
			'FILESEL_LABEL': new uic.UIStatic(
				elem = $("#asset-uploader-filesel-label"),
				perm = (d) => { return true; },
				enabler = null,
				attach = null,
				defer = null,
				getter = (elem) => { return elem.html(); },
				setter = (elem, val) => { elem.html(val); }
			),
			'UPLOAD_BUTTON': new uic.UIButton(
				elem = $("#asset-uploader-upload-btn"),
				perm = (d) => { return d['s']; },
				enabler = null,
				attach = null,
				defer = null
			),
			'CANT_UPLOAD_LABEL': new uic.UIStatic(
				elem = $("#asset-uploader-cant-upload"),
				perm = (d) => { return !d['s']; },
				enabler = (elem, s) => {
					if (s) {
						elem.show();
					} else {
						elem.hide();
					}
				},
				attach = null,
				defer = null
			)
		});

		/*
		*  Create validators and triggers for the file selector.
		*/
		this.fileval_sel = new val.ValidatorSelector(
			$("#asset-uploader-filesel"),
			$("#asset-uploader-filesel-cont"),
			[new val.FileSelectorValidator({
				mimes: ['image/png', 'image/jpeg', 'image/gif'],
				name_len: null
			}, 'Invalid file type.'),
			new val.FileSelectorValidator({
				mimes: null,
				name_len: 64
			}, 'Filename too long.')]
		);
		this.fileval_trig = new val.ValidatorTrigger(
			[ this.fileval_sel ],
			(valid) => {
				this.UI.get('UPLOAD_BUTTON').enabled(false);
			}
		);

		this.flag_ready = true;
	}

	upload(for_slide_id, ready) {
		/*
		*  Upload the selected files for the slide 'for_slide_id'.
		*  Ready is called afterwards with the returned API response.
		*/
		let data = new FormData();
		let files = this.UI.get('FILESEL').get();
		if (files.length) {
			for (let i = 0; i < files.length; i++) {
				data.append(i, files.item(i));
			}
			data.append('body', JSON.stringify({ 'id': for_slide_id }))
			this.API.call(this.API.ENDP.SLIDE_UPLOAD_ASSET, data, ready);
		}
	}

	show(for_slide_id, ready) {
		if (for_slide_id) {
			/*
			*  Attach an event handler to the upload button.
			*  This is removed by the close_callback of Popup
			*  when the popup is closed.
			*/
			this.UI.get('UPLOAD_BUTTON').get_elem().on(
				'click',
				() => { this.upload(for_slide_id, ready); }
			);
		}
		this.UI.all(
			function(d) { this.state(d); },
			{ 's': for_slide_id !== null }
		)
		this.UI.get('POPUP').enabled(true);
	}
}
