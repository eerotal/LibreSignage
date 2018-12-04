var $ = require('jquery');
var uic = require('ls-uicontrol');
var popup = require('ls-popup');
var val = require('ls-validator');
var slide = require('ls-slide');
var dialog = require('ls-dialog');

var DIALOG_CONFIRM_REMOVE = (name, callback) => {
	return new dialog.Dialog(
		dialog.TYPE.CONFIRM,
		`Remove ${name}?`,
		`Are you sure you want to remove '${name}'?`,
		callback
	);
}

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
			<div class="asset-uploader-thumb-rm-wrapper">
				<button id="asset-uploader-thumb-rm-${index}"
						class="btn btn-danger small-btn"
						type="button">
					<i class="fas fa-times"></i>
				</button>
			</div>
			<div class="asset-uploader-thumb-label">
				${name}
			</div>
		</div>
	</div>
</div>
`;

const FILENAME_REGEX = /^[A-Za-z0-9_.-]*$/;

module.exports.AssetUploader = class AssetUploader {
	constructor(api, id) {
		/*
		*  Initialize the AssetUploader. 'id' is the ID of the main
		*  container div for the asset uploader HTML. 'api' is an
		*  initialized API interface object. Call AssetUploader.show()
		*  to actually display the popup. Note that you can call
		*  AssetUploader.show() multiple times but you shouldn't
		*  construct new AssetUploader objects for the same container
		*  ID since that causes the popup HTML to be wrapped with extra
		*  HTML multiple times.
		*/
		this.div_id = id;
		this.API = api;

		this.state = {
			uploading: false,
			ready: false
		}
		this.slide = new slide.Slide(this.API);

		this.VALID_MIMES = {};
		for (let v of this.API.SERVER_LIMITS.SLIDE_ASSET_VALID_MIMES) {
			this.VALID_MIMES[v.split('/')[1]] = v;
		}
		this.FILENAME_MAXLEN
			= this.API.SERVER_LIMITS.SLIDE_ASSET_NAME_MAX_LEN;

		this.FILELIST_UI = null;
		this.UI = new uic.UIController({
			'POPUP': new uic.UIStatic(
				elem = new popup.Popup(
					$(`#${id}`).get(0),
					() => {
						// Reset the asset uploader data on close.
						this.UI.get('FILESEL').clear();
						this.UI.get('FILELINK').clear();
						this.UI.get('FILELIST').set('');
					}
				),
				perm = () => false,
				enabler = () => {},
				attach = null,
				defer = null,
				getter = null,
				setter = null
			),
			'FILESEL': new uic.UIInput(
				elem = $(`#${id}-filesel`),
				perm = (d) => { return d['s'] && d['c']; },
				enabler = (elem, s) => {
					elem.prop('disabled', !s);
				},
				attach = {
					'input': (e) => {
						/*
						*  Update the file selector label when
						*  the selection is changed.
						*/
						var label = '';
						var files = e.target.files;
						if (files.length !== 0) {
							for (let i = 0; i < files.length; i++) {
								if (label.length !== 0) { label += ', '; }
								label += files.item(i).name;
							}
						} else {
							label = 'Choose a file';
						}
						this.UI.get('FILESEL_LABEL').set(label);

						/*
						*  Remove possible error styling from the
						*  upload button.
						*/
						this.indicate('upload-success');
					}
				},
				defer = () => { this.defer_ready(); },
				mod = null,
				getter = (elem) => { return elem.prop('files'); },
				setter = null,
				clearer = (elem) => {
					/*
					*  Clear the file selector. This function also fires
					*  the 'input' events to execute the attached event
					*  handlers and to update any validators.
					*/
					elem.val('');
					elem.trigger('input');
				}
			),
			'FILESEL_LABEL': new uic.UIStatic(
				elem = $(`#${id}-filesel-label`),
				perm = (d) => { return true; },
				enabler = null,
				attach = null,
				defer = null,
				getter = (elem) => { return elem.html(); },
				setter = (elem, val) => { elem.html(val); }
			),
			'UPLOAD_BUTTON': new uic.UIButton(
				elem = $(`#${id}-upload-btn`),
				perm = (d) => {
					return d['s'] && !d['u'] && d['f'] && d['c'];
				},
				enabler = null,
				attach = {
					'click': () => {
						/*
						*  Handle upload button clicks.
						*/
						this.state.uploading = true;
						this.update_controls();

						this.indicate('upload-uploading');
						this.upload((resp) => {
							if (resp.error) {
								this.indicate('upload-error');
							} else {
								this.indicate('upload-success');
								this.UI.get('FILESEL').clear();
							}
							this.update_and_populate();
							this.state.uploading = false;
							this.update_controls();
						});
					}
				},
				defer = () => { this.defer_ready(); },
			),
			'CANT_UPLOAD_LABEL': new uic.UIStatic(
				elem = $(`#${id}-cant-upload-row`),
				perm = (d) => { return !d['s']; },
				enabler = (elem, s) => {
					s ? elem.show() : elem.hide();
				},
				attach = null,
				defer = null,
				getter = null,
				setter = null
			),
			'NO_MORE_ASSETS_LABEL': new uic.UIStatic(
				elem = $(`#${id}-no-more-assets-row`),
				perm = (d) => { return d['s'] && !d['c']; },
				enabler = (elem, s) => {
					s ? elem.show() : elem.hide();
				},
				attach = null,
				defer = null,
				getter = null,
				setter = null
			),
			'FILELIST': new uic.UIStatic(
				elem = $(`#${id}-filelist`),
				perm = (d) => { return true; },
				enabler = null,
				attach = null,
				defer = null,
				getter = null,
				setter = (elem, val) => { elem.html(val); }
			),
			'FILELINK': new uic.UIInput(
				elem = $(`#${id}-file-link-input`),
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
			$(`#${id}-filesel`),
			$(`#${id}-filesel-cont`),
			[new val.FileSelectorValidator(
				{
					mimes: Object.values(this.VALID_MIMES),
					name_len: null,
					regex: null,
					minfiles: null,
					bl: null
				},
				`Invalid file type. The allowed types are: ` +
				`${Object.keys(this.VALID_MIMES).join(', ')}.`
			),
			new val.FileSelectorValidator(
				{
					mimes: null,
					name_len: this.FILENAME_MAXLEN,
					regex: null,
					minfiles: null,
					bl: null
				},
				`Filename too long. The maximum length ` +
				`is ${this.FILENAME_MAXLEN} characters.`
			),
			new val.FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: FILENAME_REGEX,
					minfiles: null,
					bl: null
				},
				"Invalid characters in filename. " + 
				"A-Z, a-z, 0-9, ., _ and - are allowed."
			),
			new val.FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: null,
					minfiles: null,
					bl: () => {
						let tmp = [];
						if (this.slide && this.slide.get('assets')) {
							for (let a of this.slide.get('assets')) {
								tmp.push(a['filename']);
							}
						}
						return tmp;
					}
				}, 'At least one of the selected files already exists.'
			),
			new val.FileSelectorValidator(
				{
					mimes: null,
					name_len: null,
					regex: null,
					minfiles: 1,
					bl: null
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

	indicate(status) {
		/*
		*  Indicate information by setting or removing CSS
		*  classes.
		*/
		switch (status) {
			// Filelist indicators.
			case 'filelist-error':
				this.UI.get(
					'FILELIST'
				).get_elem().parent().addClass(
					'error'
				);
				break;
			case 'filelist-success':
				this.UI.get(
					'FILELIST'
				).get_elem().parent().removeClass(
					'error'
				);
				break;

			// Upload button indicators.
			case 'upload-uploading':
				this.UI.get('UPLOAD_BUTTON').get_elem().removeClass(
					'error'
				);
				this.UI.get('UPLOAD_BUTTON').get_elem().addClass(
					'uploading'
				);
				break;
			case 'upload-error':
				this.UI.get('UPLOAD_BUTTON').get_elem().removeClass(
					'uploading'
				);
				this.UI.get('UPLOAD_BUTTON').get_elem().addClass(
					'error'
				);
				break;
			case 'upload-success':
				this.UI.get('UPLOAD_BUTTON').get_elem().removeClass(
					'uploading error'
				);
				break;
			default:
				break;
		}
	}

	update_controls() {
		/*
		*  Update the controls state.
		*    s  = Is this.slide null?
		*    u  = Is uploading in progress?
		*    v  = Is the popup visible?
		*    f  = Is the file selector input validated?
		*    c  = Is uploading more assets allowed?
		*/
		this.UI.all(
			function(d) { this.state(d); },
			{
				's': this.slide != null,
				'u': this.state.uploading,
				'f': this.fileval_trig.is_valid(),
				'c': (
					this.slide != null
					&& this.slide.get('assets') != null
					&& this.slide.get('assets').length
						< this.API.SERVER_LIMITS.SLIDE_MAX_ASSETS
				)
			}
		);
	}

	upload(callback) {
		/*
		*  Upload the selected files to the loaded slide.
		*  'callback' is passed straight to API.call() as
		*  the callback argument.
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

	remove(name, callback) {
		/*
		*  Remove the slide asset named 'name' from the
		*  loaded slide. 'callback' is passed straight
		*  to API.call().
		*/
		this.API.call(
			this.API.ENDP.SLIDE_REMOVE_ASSET,
			{
				'id': this.slide.get('id'),
				'name': name
			},
			callback
		);
	}

	update_and_populate() {
		/*
		*  Load new slide data and call this.populate(). This
		*  function handles all errors.
		*/
		this.update_slide((err) => {
			if (!err) {
				this.indicate('filelist-success');
				this.populate();
			} else {
				this.indicate('filelist-error');
			}
			this.update_controls();
		});
	}

	populate() {
		/*
		*  Populate the existing asset list with data from 'this.slide'.
		*/
		let html = '';

		if (!this.slide.get('assets')) { return; }

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

			// Asset select "button" handling.
			tmp[i] = new uic.UIButton(
				elem = $(`#${this.div_id}-thumb-${i}`),
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

			// Asset remove button handling.
			tmp[`${i}_rm`] = new uic.UIButton(
				elem = $(`#${this.div_id}-thumb-rm-${i}`),
				perm = null,
				enabler = null,
				attach = {
					'click': (e) => {
						DIALOG_CONFIRM_REMOVE(
							a.filename,
							(status, val) => {
								if (!status) { return; }
								this.remove(
									a.filename,
									(resp) => {
										this.API.handle_disp_error(
											resp.error
										);
										this.UI.get('FILELINK').set('');
										this.update_and_populate();
									}
								);
							}
						).show();
						e.stopPropagation();
					}
				}
			)
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
		*  If slide_id == null, the asset uploader is opened
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
				this.populate();
				this.UI.get('POPUP').get_elem().visible(true);

				this.update_controls();
				if (callback) { callback(err); }
			});
		} else {
			this.UI.get('POPUP').get_elem().visible(false);
			this.update_controls();
			if (callback) { callback(this.API.ERR.API_E_OK); }
		}
	}
}
