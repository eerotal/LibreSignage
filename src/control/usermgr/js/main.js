var $ = require('jquery');
var User = require('ls-user').User;
var APIInterface = require('ls-api').APIInterface;
var APIEndpoints = require('ls-api').APIEndpoints;
var APIUI = require('ls-api-ui');

var uic = require('ls-uicontrol');
var dialog = require('ls-dialog');
var multiselect = require('ls-multiselect');
var val = require('ls-validator');
var bootstrap = require('bootstrap');

var flag_ready = false;
var defer_ready = () => { return !flag_ready; }

const USER_NAME_QUERY   = (name) => `#usr-name-input-${name}`;
const USER_GROUPS_QUERY = (name) => `#usr-groups-input-${name}`;
const USER_SAVE_QUERY   = (name) => `#btn-user-${name}-save`;
const USER_REMOVE_QUERY = (name) => `#btn-user-${name}-remove`;

const USER_CREATE = $('#btn-create-user');
const USERS_TABLE = $('#users-table');

const MAIN_CONTROLLER = new uic.UIController({
	'USER_CREATE': new uic.UIButton(
		elem = USER_CREATE,
		perm = () => { return true; },
		enabler = null,
		attach = {
			'click': create_user
		},
		defer = defer_ready
	)
});
var TABLE_CONTROLLER = new uic.UIController({});

// Dialog messages.
const DIALOG_TOO_MANY_USERS = new dialog.Dialog(
	dialog.TYPE.ALERT,
	'Too many users',
	`The maximum number of users on the server has been reached.
	No more users can be created.`,
	null
);

// User table row template.
const usr_table_row = (name, groups, pass) => `
	<div class="row usr-table-row" id="usr-row-${name}">
		<div id="usr-name-col-${name}" class="usr-table-col col">
			${name}
		</div>
		<div id="usr-groups-col-${name}" class="usr-table-col col">
			${groups}
		</div>
		<div id="usr-comment-col-${name}" class="usr-table-col col">
			${pass}
		</div>
		<div class="usr-table-col col-auto">
			<button type="button"
				role="button"
				class="btn btn-success small-btn usr-edit-btn"
				id="btn-user-${name}-save">
				<i class="fas fa-save"></i>
			</button>
			<button type="buttton"
				role="button"
				class="btn btn-danger small-btn usr-edit-btn"
				id="btn-user-${name}-remove">
				<i class="fas fa-trash-alt"></i>
			</button>
			<button type="button"
				role="button"
				class="btn btn-primary small-btn usr-edit-btn"
				data-toggle="collapse"
				data-target="#usr-edit-${name}"
				aria-expanded="false"
				aria-controls="usr-collapse-${name}">
				<i class="fas fa-edit"></i>
			</button>
		</div>
	</div>
	<div class="collapse usr-edit-row" id="usr-edit-${name}">
		<div class="usr-edit-row-container">
			<div class="row usr-edit-input-row">
				<label class="usr-data-label col-auto col-form-label""
					for="usr-name-input-${name}">
					User
				</label>
				<div class="col px-1">
					<input id="usr-name-input-${name}"
						type="text"
						class="form-control"
						readonly>
					</input>
				</div>
			</div>
			<div class="row usr-edit-input-row">
				<label class="usr-data-label col-auto col-form-label""
						for="usr-groups-input-${name}">
					Groups
				</label>
				<div id="usr-groups-input-${name}-cont"
						class="col px-1">
				</div>
			</div>
			<div class="row usr-edit-input-row">
				<label class="usr-data-label col-auto col-form-label"
						for="usr-comment-label-${name}">
					Comment
				</label>
				<div id="usr-comment-label-${name}"
					class="col px-1 text-left my-auto">
					${pass}
				</div>
			</div>
		</div>
	</div>
`;

async function save_user(name) {
	/*
	*  Save a user.
	*/
	let user = new User(API);
	try {
		await user.load(name);
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}

	user.data.groups = TABLE_CONTROLLER.get(`${name}_groups`).get();
	if (user.data.groups.length > API.limits.MAX_GROUPS_PER_USER) {
		DIALOG_TOO_MANY_GROUPS(API.limits.MAX_GROUPS_PER_USER).show();
		return;
	}

	try {
		await user.save();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	await update();
}

async function remove_user(name) {
	/*
	*  Remove a user.
	*/
	dialog.dialog(
		dialog.TYPE.CONFIRM,
		`Remove user ${name}?`,
		`Are you sure you want to remove the user ${name}? ` +
		`All user data for ${name} will be lost and won't be ` +
		`recoverable.`,
		async (status, val) => {
			if (!status) { return; }
			let user = new User(API);
			try {
				await user.load(name);
				await user.remove();
			} catch (e) {
				APIUI.handle_error(e);
				return;			
			}
			await update();
		}
	);
}

function create_user() {
	/*
	*  Create a new user.
	*/
	dialog.dialog(
		dialog.TYPE.PROMPT,
		'Create a user',
		'Enter a name for the new user.',
		async (status, val) => {
			if (!status) { return; }
			let info = {};
			let usr = new User(API);
			try {
				await usr.create(val);
			} catch (e) {
				if (e.response.error == APIError.codes.LIMITED) {
					DIALOG_TOO_MANY_USERS.show();
				} else {
					APIUI.handle_error(e);
				}
				return;
			}
			info[usr.data.user] = `Password: ${usr.data.pass}`;
			await update(info);
		},
		[new val.StrValidator({
			min: 1,
			max: null,
			regex: null
		}, "The username is too short."),
		new val.StrValidator({
			min: null,
			max: API.limits.USERNAME_MAX_LEN,
			regex: null
		}, "The username is too long."),
		new val.StrValidator({
			min: null,
			max: null,
			regex: /^[A-Za-z0-9_]*$/
		}, "The username contains invalid characters.")]
	);
}

async function update(add_info) {
	/*
	*  Render the user manager UI. Additional text for the
	*  'Comments' column can be supplied in the associative
	*  array 'add_info' as username-info pairs.
	*/
	let users = null;
	try {
		users = await User.get_all(API);
	} catch (e) {
		console.log(e);
		APIUI.handle_error(e);
		return;
	}

	TABLE_CONTROLLER.rm_all();
	USERS_TABLE.empty();

	for (let u in users) {
		let name = users[u].data.user;
		let groups = users[u].data.groups;
		let info = (add_info != null && u in add_info) ? add_info[u] : '';

		// Add the HTML DOM elements to the document.
		USERS_TABLE.append(usr_table_row(
			name,
			!groups || !groups.length ? '' : groups.join(', '),
			info
		));

		// Setup the UIController for the input elements.
		TABLE_CONTROLLER.add(`${name}_save`, new uic.UIButton(
				elem = $(USER_SAVE_QUERY(name)),
				perm = () => { return true; },
				enabler = null,
				attach = {
					'click': () => { save_user(name); }
				},
				defer = null
			)
		);
		TABLE_CONTROLLER.add(`${name}_remove`, new uic.UIButton(
				elem = $(USER_REMOVE_QUERY(name)),
				perm = () => {
					return name != API.config.session.data.user;
				},
				enabler = null,
				attach = {
					'click': () => { remove_user(name); }
				},
				defer = null
			)
		);
		TABLE_CONTROLLER.add(`${name}_name`, new uic.UIInput(
				elem = $(USER_NAME_QUERY(name)),
				perm = () => { return false; },
				enabler = null,
				attach = null,
				defer = null,
				mod = null,
				getter = null,
				setter = (elem, usr) => { elem.val(usr.data.user); },
				clearer = (elem) => { elem.val(''); }
			)
		);
		TABLE_CONTROLLER.add(`${name}_groups`, new uic.UIInput(
				elem = new multiselect.MultiSelect(
					`usr-groups-input-${name}-cont`,
					`usr-groups-input-${name}`,
					[new val.StrValidator({
						min: 1,
						max: null,
						regex: null
					}, "The group name is too short."),
					new val.StrValidator({
						min: null,
						max: null,
						regex: /^[A-Za-z0-9_]*$/
					}, "The group name has invalid characters.")],
					{
						'nodups': true,
						'maxopts': API.limits.MAX_USER_GROUPS
					}
				),
				perm = () => { return true; },
				enabler = (elem, state) => {
					if (state) {
						elem.enable();
					} else {
						elem.disable();
					}
				},
				attach = null,
				defer = null,
				mod = null,
				getter = (elem) => { return elem.selected; },
				setter = (elem, usr) => { elem.set(usr.data.groups); },
				clearer = (elem) => { elem.remove_all(); }
			)
		);
		// Add the existing user data to the inputs.
		TABLE_CONTROLLER.get(`${name}_name`).set(users[u]);
		TABLE_CONTROLLER.get(`${name}_groups`).set(users[u]);
	}
	// Conditionally enable all the inputs.
	TABLE_CONTROLLER.all(function() { this.state(); });
}

$(document).ready(async () => {
	API = new APIInterface({standalone: false});
	try {
		await API.init();
	} catch (e) {
		APIUI.handle_error(e);
		return;
	}
	flag_ready = true;
	await update();
});
