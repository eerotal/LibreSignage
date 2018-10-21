var $ = require('jquery');
var uic = require('ls-uicontrol');
var popup = require('ls-popup');
var val = require('ls-validator');
var slide = require('ls-slide');

/*
*  Asset URL template string. 'origin' is the origin URL,
*  ie. the protocol and hostname. 'slide_id' is the slide id
*  and 'name' is the original asset name.
*/
const asset_url_template = (origin, slide_id, name) => `
${origin}/api/endpoint/slide/asset/slide_get_asset.php
?${$.param({ 'id': slide_id, 'name': name })}
`;

/*
*  Asset uploader thumbnail template literal.
*  'slide_id' is the slide id to use, 'name' is
*  the original asset name and 'index' is a unique
*  index number for each thumbnail.
*/
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

const VALID_MIMES = {
	jpeg: 'image/jpeg',
	png: 'image/png',
	gif: 'image/gif'
};
const FILENAME_MAXLEN = 64;
const FILENAME_REGEX = /^[A-Za-z0-9_.-]*$/;

module.exports.AssetUploader = class AssetUploader {
	constructor(api) {
		this.API = api;

		this.state = {
			visible: false,
			uploading: false,
			ready: false
		}
		this.slide = new slide.Slide(this.API);

		this.FILELIST_UI = null;
		this.UI = new uic.UIController({
			'POPUP': new uic.UIStatic(
				elem = new popup.Popup(
					$("#asset-uploader").get(0),
					() => {
						// Reset the asset uploader data on close.
						this.UI.get('UPLOAD_BUTTON').get_elem().off(
							'click'
						);
						this.UI.get('FILESEL_LABEL').set('Choose a file');
						this.UI.get('FILELINK').clear();
					}
				),
				perm = (d) => { return d['v']; },
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

						// Remove the failed upload indicator.
						this.UI.get(
							'UPLOAD_BUTTON'
						).get_elem().removeClass('upload-failed');
					}
				},
				defer = () => { this.defer_ready(); },
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
				perm = (d) => { return d['s'] && !d['u'] && d['f']; },
				enabler = null,
				attach = null,
				defer = null,
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
				setter = (elem, val) => { elem.val(val); },
				clearer = (elem) => { elem.val(''); }
			)
		});

		/*
		*  Create validators and triggers for the file selector.
		*/
		this.fileval_sel = new val.ValidatorSelector(
			$("#asset-uploader-filesel"),
			$("#asset-uploader-filesel-cont"),
			[new val.FileSelectorValidator(
				{
					mimes: Object.values(VALID_MIMES),
					name_len: null,
					regex: null,
					minfiles: null
				},
				`Invalid file type. The allowed types are: ` +
				`${Object.keys(VALID_MIMES).join(', ')}.`
			),
			new val.FileSelectorValidator(
				{
					mimes: null,
					name_len: FILENAME_MAXLEN,
					regex: null,
					minfiles: null
				},
				`Filename too long. The maximum length ` +
				`is ${FILENAME_MAXLEN}`
			),
			new val.FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: FILENAME_REGEX,
					minfiles: null
				},
				"Invalid characters in filename. " + 
				"A-Z, a-z, 0-9, ., _ and - are allowed."
			),
			new val.FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: null,
					minfiles: 1
				}, '', true
			)]
		);

		(this.fileval_trig = new val.ValidatorTrigger(
			[ this.fileval_sel ],
			(valid) => { this.update_controls(); }
		)).trigger();

		this.state.ready = true;
	}

	defer_ready() {
		return !this.state.ready;
	}

	update_controls() {
		this.UI.all(
			function(d) { this.state(d); },
			{
				's': this.slide != null,
				'u': this.state.uploading,
				'v': this.state.visible,
				'f': this.fileval_trig.is_valid()
			}
		);
	}

	upload(callback) {
		/*
		*  Upload the selected files to the slide that's loaded.
		*  'callback' is passed straight to the API.call() function
		*  as the callback argument.
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

		// Generate HTML.
		for (let i = 0; i < this.slide.get('assets').length; i++) {
			html += asset_thumb_template(
				this.slide.get('id'),
				this.slide.get('assets')[i].filename,
				i
			);
		}
		this.UI.get('FILELIST').set(html);

		/*
		*  Create UIElem objects for the asset 'buttons' and attach
		*  event handlers to them. The UIController is stored in
		*  this.FILELIST_UI.
		*/
		let tmp = {};
		for (let i = 0; i < this.slide.get('assets').length; i++) {
			let a = this.slide.get('assets')[i];
			tmp[i] = new uic.UIButton(
				elem = $(`#asset-uploader-thumb-${i}`),
				perm = null,
				enabler = null,
				attach = {
					'click': (e) => {
						this.UI.get('FILELINK').set(asset_url_template(
							window.location.origin,
							this.slide.get('id'),
							a.filename
						));
					}
				},
				defer = () => { this.defer_ready(); }
			);
		}
		this.FILELIST_UI = new uic.UIController(tmp);
	}

	load_slide(slide_id, callback) {
		/*
		*  Load slide data. 'slide_id' is the slide id to use.
		*  'callback' is called afterwards with the returned
		*  API error code as the first argument.
		*/
		this.slide.load(slide_id, true, false, (err) => {
			if (callback) { callback(err); }
		});
	}

	update_slide(callback) {
		/*
		*  Update slide data. 'callback' is called afterwards
		*  with the returned API error code as the first argument.
		*/
		this.slide.fetch((err) => {
			if (callback) { callback(err); }
		});
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
						this.state.uploading = true;
						this.update_controls();

						// Add a spinner to the upload button.
						this.UI.get(
							'UPLOAD_BUTTON'
						).get_elem().removeClass('upload-failed');
						this.UI.get(
							'UPLOAD_BUTTON'
						).get_elem().addClass('uploading');

						this.upload((resp) => {
							// Remove the upload button spinner.
							this.UI.get(
								'UPLOAD_BUTTON'
							).get_elem().removeClass('uploading');

							if (!resp.error) {
								// Update asset list after upload.
								this.update_slide((err) => {
									if (!err) { this.populate(); }
								});
							} else {
								// Indicate a failed upload.
								this.UI.get(
									'UPLOAD_BUTTON'
								).get_elem().addClass('upload-failed');
							}
							this.state.uploading = false;
							this.update_controls();
						});
					}
				);

				this.populate();
				this.state.visible = true;
				this.update_controls();

				if (callback) { callback(err); }
			});
		} else {
			this.state.visible = false;
			this.update_controls();

			if (callback) { callback(this.API.ERR.API_E_OK); }
		}
	}
}
