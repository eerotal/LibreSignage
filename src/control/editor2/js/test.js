var APIInterface = require('ls-api').APIInterface;
var EditorController = require('./editorcontroller.js').EditorController;

async function main() {
	let API = new APIInterface({standalone: true});
	await API.init();
	await API.login('admin', 'admin');

	let editor = new EditorController(API);
	await editor.open_slide('0x1');
	console.log(editor.get_state());
	await editor.close_slide();
	console.log(editor.get_state());

	await API.logout();
}
main();
