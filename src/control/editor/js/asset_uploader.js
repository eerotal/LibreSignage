var $ = require('jquery');
var uic = require('ls-uicontrol');
var popup = require('ls-popup');
var val = require('ls-validator');
var slide = require('ls-slide');

const asset_uri_template = (origin, slide_id, name) => `
${origin}/api/endpoint/slide/asset/slide_get_asset.php
?${$.param({ 'id': slide_id, 'name': name })}
`;

const asset_thumb_template = (slide_id, name, index) => `
<div id="asset-uploader-thumb-${index}" class="asset-uploader-thumb">
	<div class="asset-uploader-thumb-inner default-border">
		<div class="asset-uploader-thumb-img-wrapper">
			<img src="/api/endpoint/slide/asset/slide_get_asset_thumb.php
					?${$.param({ 'id': slide_id, 'name': name })}">
			</img>
		</div>
		<div class="asset-uploader-thumb-label-wrapper">
			<div class="asset-uploader-thumb-label">
				${name}
			</div>
		</div>
	</div>
</div>
`;

module.exports.AssetUploader = class AssetUploader {
	constructor(api) {
		this.API = api;
		this.slide = null;
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
				defer = null,
				getter = null,
				setter = null
			),
			'CANT_UPLOAD_LABEL': new uic.UIStatic(
				elem = $("#asset-uploader-cant-upload-row"),
				perm = (d) => { return !d['s']; },
				enabler = (elem, s) => {
					if (s) {
						elem.show();
					} else {
						elem.hide();
					}
				},
				attach = null,
				defer = null,
				getter = null,
				setter = null
			),
			'FILELIST': new uic.UIStatic(
				elem = $("#asset-uploader-filelist"),
				perm = (d) => { return true; },
				enabler = null,
				attach = null,
				defer = null,
				getter = null,
				setter = (elem, val) => { elem.html(val); }
			),
			'FILELINK': new uic.UIInput(
				elem = $("#asset-uploader-file-link-input"),
				perm = (d) => { return d['s']; },
				enabler = (elem, s) => { elem.prop('disabled', !s); },
				attach = null,
				defer = null,
				mod = null,
				getter = (elem) => { return elem.val(); },
				setter = (elem, val) => {
					console.log(val);
					elem.val(val);
				},
				clearer = (elem) => { elem.val(''); }
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

		this.slide = new slide.Slide(this.API);
		this.FILELIST_UI = null;

		this.flag_ready = true;
	}

	upload(callback) {
		/*
		*  Upload the selected files for the slide attached
		*  to this AssetUploader object. 'callback' is called
		*  afterwards with the API response data.
		*/
		let data = new FormData();
		let files = this.UI.get('FILESEL').get();
		if (files.length) {
			for (let i = 0; i < files.length; i++) {
				data.append(i, files.item(i));
			}
			data.append('body', JSON.stringify({
				'id': this.slide.get('id')
			}));
			this.API.call(
				this.API.ENDP.SLIDE_UPLOAD_ASSET,
				data,
				callback
			);
		}
	}

	populate() {
		/*
		*  Populate the existing asset list with data from this.slide.
		*/
		let html = '';
		for (let i = 0; i < this.slide.get('assets').length; i++) {
			html += asset_thumb_template(
				this.slide.get('id'),
				this.slide.get('assets')[i].filename,
				i
			);
		}
		this.UI.get('FILELIST').set(html);

		let tmp = {};
		for (let i = 0; i < this.slide.get('assets').length; i++) {
			let a = this.slide.get('assets')[i];
			tmp[i] = new uic.UIButton(
				elem = $(`#asset-uploader-thumb-${i}`),
				perm = null,
				enabler = null,
				attach = {
					'click': (e) => {
						console.log(a.filename);
						console.log(this.UI);
						this.UI.get('FILELINK').set(asset_uri_template(
							window.location.origin,
							this.slide.get('id'),
							a.filename
						));
					}
				},
				defer = null
			);
		}
		this.FILELIST_UI = new uic.UIController(tmp);
	}

	load_slide(slide_id, callback) {
		/*
		*  Fetch slide data. 'slide_id' is the slide id to use
		*  and 'callback' is a callback function that's called
		*  after the data has been fetched. The returned API error
		*  code is passed to 'callback'.
		*/
		this.slide.load(slide_id, true, false, (err) => {
			if (callback) { callback(err); }
		});
	}

	fetch_slide(callback) {
		/*
		*  Fetch new slide data. 'callback' is called afterwards
		*  with the returned API error code as the first argument.
		*/
		this.slide.fetch((err) => { if (callback) { callback(err); } });
	}

	show(slide_id, callback) {
		/*
		*  Show the asset uploader for the slide 'slide_id'.
		*  If slide_id === null, the asset uploader is opened
		*  but all the upload features are disabled. Note that
		*  you should load the slide before calling this
		*  function, lock it *and* enable lock renewal. This
		*  makes sure that a) this function can modify the slide
		*  and b) this function doesn't have to take care of
		*  renewing slide locks. An error is thrown if this
		*  function can't lock the slide. 'callback' is called
		*  after the asset uploader is ready. The resulting API
		*  error code is passed as the first argument.
		*/
		if (slide_id) {
			this.load_slide(slide_id, (err) => {
				if (err) {
					if (callback) { callback(err); }
					return;
				}

				// Enable and setup controls.
				this.UI.get('UPLOAD_BUTTON').get_elem().on(
					'click',
					() => {
						this.upload((resp) => {
							if (!resp.error) {
								// Update list after upload.
								this.fetch_slide((err) => {
									if (!err) { this.populate(); }
								});
							}
						});
					}
				);

				this.UI.all(
					function(d) { this.state(d); },
					{ 's': true }
				);

				this.populate();
				this.UI.get('POPUP').enabled(true);

				if (callback) { callback(err); }
			});
		} else {
			this.UI.all(
				function(d) { this.state(d); },
				{ 's': false }
			)
			this.UI.get('POPUP').enabled(true);
			if (callback) { callback(this.API.ERR.API_E_OK); }
		}
	}
}
