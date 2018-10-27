/*
*  LibreSignage editor dialogs.
*/

var dialog = require('ls-dialog');

module.exports.DIALOG_MARKUP_TOO_LONG = (max) => {
	return new dialog.Dialog(
		dialog.TYPE.ALERT,
		'Too long slide markup',
		`The slide markup is too long. The maximum length is
		${max} characters.`,
		null
	);
}

module.exports.DIALOG_SLIDE_UNSAVED = (callback) => {
	return new dialog.Dialog(
		dialog.TYPE.CONFIRM,
		'Slide not saved',
		'The selected slide has unsaved changes. All changes ' +
		'will be lost if you continue. Are you sure you want ' +
		'to continue?',
		callback
	);
}
