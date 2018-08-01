var $ = require('jquery');
var api = require('ls-api');
var user = require('ls-user');
var uic = require('ls-uicontrol');
var dialog = require('ls-dialog');
var multiselect = require('ls-multiselect');
var val = require('ls-validator');
var bootstrap = require('bootstrap');

var flag_usermgr_ready = false;
var defer_usermgr_ready = () => { return !flag_usermgr_ready; }

const USER_NAME_QUERY = (name) => `#usr-name-input-${name}`;
const USER_GROUPS_QUERY = (name) => `#usr-groups-input-${name}`;
const USER_SAVE_QUERY = (name) => `#btn-user-${name}-save`;
const USER_REMOVE_QUERY = (name) => `#btn-user-${name}-remove`;

const USER_CREATE = $('#btn-create-user');
const USERS_TABLE = $('#users-table');

const USERMGR_UI_DEFS = new uic.UIController({
	'USER_CREATE': new uic.UIButton(
		_elem = USER_CREATE,
		_perm = () => { return true; },
		_enabler = null,
		_attach = {
			'click': usermgr_create
		},
		_defer = defer_usermgr_ready
	)
});
var USERMGR_LIST_UI_DEFS = new uic.UIController({});
var USERMGR_MULTISELECTS = {};

// Dialog messages.
const DIALOG_USER_SAVED = new dialog.Dialog(
	dialog.TYPE.ALERT,
	'User saved',
	'User information was successfully saved!',
	null
);

const DIALOG_TOO_MANY_USERS = new dialog.Dialog(
	dialog.TYPE.ALERT,
	'Too many users',
	`The maximum number of users on the server has been reached.
	No more users can be created.`,
	null
);

const DIALOG_USER_REMOVE_FAILED = new dialog.Dialog(
	dialog.TYPE.ALERT,
	'User removal failed',
	'Failed to remove user.',
	null
);

// User table row template.
const usr_table_row = (index, name, groups, pass) => `
	<div class="row usr-table-row" id="usr-row-${name}">
		<div id="usr-index-${name}" class="usr-table-col col-1">
			${index}
		</div>
		<div id="usr-name-${name}" class="usr-table-col col-2">
			${name}
		</div>
		<div id="usr-groups-${name}" class="usr-table-col col-3">
			${groups}
		</div>
		<div id="usr-info-${name}" class="usr-table-col col-3">
			${pass}
		</div>
		<div class="usr-table-col col-3">
			<button type="button"
				role="button"
				class="btn btn-success usr-edit-btn"
				id="btn-user-${name}-save">
				<i class="fas fa-save"></i>
			</button>
			<button type="buttton"
				role="button"
				class="btn btn-danger usr-edit-btn"
				id="btn-user-${name}-remove">
				<i class="fas fa-trash-alt"></i>
			</button>
			<button type="button"
				role="button"
				class="btn btn-primary"
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
				<label class="col-3 col-form-label"
					for="usr-name-input-${name}">
					User
				</label>
				<div class="col-9">
					<input id="usr-name-input-${name}"
						type="text"
						class="form-control"
						readonly>
					</input>
				</div>
			</div>
			<div class="row usr-edit-input-row">
				<label class="col-3 col-form-label" for="usr-groups-input-${name}">
					Groups
				</label>
				<div id="usr-groups-input-${name}-cont" class="col-9">
				</div>
			</div>
		</div>
	</div>
`;

function usermgr_assign_userdata(name) {
	/*
	*  Assign the edited user data to 'user' from
	*  the user manager UI.
	*/
	var tmp = '';
	var users = user.users_get();
	if (!user.user_exists(name)) {
		throw new Error("User doesn't exist!");
	}

	for (var u in users) {
		if (users[u].get_name() == name) {
			users[u].groups = USERMGR_LIST_UI_DEFS.get(
				`${users[u].get_name()}_groups`
			).get();

			// Check that the number of groups is valid.
			if (users[u].groups.length >
				API.SERVER_LIMITS.MAX_GROUPS_PER_USER) {
				DIALOG_TOO_MANY_GROUPS(
					API.SERVER_LIMITS.MAX_GROUPS_PER_USER
				).show();
				return false;
			}
			return true;
		}
	}
}

function usermgr_save(name) {
	/*
	*  Save a user.
	*/
	var users = user.users_get();
	for (var u in users) {
		if (users[u].get_name() == name) {
			if (!usermgr_assign_userdata(name)) {
				console.error('Failed to save userdata.');
				return;
			}
			users[u].save((err) => {
				if (API.handle_disp_error(err)) { return; }
				// Update UI.
				usermgr_make_ui();
				DIALOG_USER_SAVED.show();
			});
			break;
		}
	}
}

function usermgr_remove(name) {
	/*
	*  Remove a user.
	*/
	var users = user.users_get();
	dialog.dialog(dialog.TYPE.CONFIRM,
		`Remove user ${name}?`,
		`Are you sure you want to remove the user ${name}? ` +
		`All user data for ${name} will be lost and won't be ` +
		`recoverable.`,
		(status, val) => {
			if (!status) { return; }
			for (var u in users) {
				if (users[u].get_name() != name) { continue; }
				users[u].remove((resp) => {
					if (API.handle_disp_error(resp)) { return; }
					user.users_load(API, usermgr_make_ui);
				});
				return;
			}
			DIALOG_USER_REMOVE_FAILED.show();
		}
	);
}

function usermgr_create() {
	dialog.dialog(
		dialog.TYPE.PROMPT,
		'Create a user',
		'Enter a name for the new user.', (status, val) => {
			if (!status) { return; }
			API.call(API.ENDP.USER_CREATE, {'user': val}, (resp) => {
				if (resp.error == API.ERR.LIMITED) {
					DIALOG_TOO_MANY_USERS.show();
					return;
				} else if (API.handle_disp_error(resp.error)) {
					return;
				}

				var tmp = new user.User(API);
				tmp.set(
					resp.user.name,
					resp.user.groups,
					null
				);
				tmp.set_info('Password: ' + resp.user.pass);
				user.users_add(tmp);
				usermgr_make_ui();
			});
		},
		[new val.StrValidator({
			min: 1,
			max: null,
			regex: null
		}, "The username is too short."),
		new val.StrValidator({
			min: null,
			max: API.SERVER_LIMITS.USERNAME_MAX_LEN,
			regex: null
		}, "The username is too long."),
		new val.StrValidator({
			min: null,
			max: null,
			regex: /^[A-Za-z0-9_]*$/
		}, "The username contains invalid characters.")]
	);
}

function usermgr_make_ui() {
	/*
	*  Render the user manager UI.
	*/
	var users = user.users_get();
	var i = 0;

	USERMGR_LIST_UI_DEFS.rm_all();
	USERMGR_MULTISELECTS = {};
	USERS_TABLE.empty();

	for (let u in users) {
		let name = users[u].get_name();
		let grps = users[u].get_groups();
		let info = users[u].get_info();

		// Add the HTML DOM elements to the document.
		USERS_TABLE.append(usr_table_row(
			i,
			name,
			!grps || !grps.length ? '' : grps.join(', '),
			!info ? '' : info,
		));

		// Create the UI element instances for the inputs & buttons.
		USERMGR_LIST_UI_DEFS.add(`${name}_save`, new uic.UIButton(
				_elem = $(USER_SAVE_QUERY(name)),
				_perm = () => { return true; },
				_enabler = null,
				_attach = {
					'click': () => { usermgr_save(name); }
				},
				_defer = null
			)
		);
		USERMGR_LIST_UI_DEFS.add(`${name}_remove`, new uic.UIButton(
				_elem = $(USER_REMOVE_QUERY(name)),
				_perm = () => { return name != API.CONFIG.user; },
				_enabler = null,
				_attach = {
					'click': () => { usermgr_remove(name); }
				},
				_defer = null
			)
		);
		USERMGR_LIST_UI_DEFS.add(`${name}_name`, new uic.UIInput(
				_elem = $(USER_NAME_QUERY(name)),
				_perm = () => { return false; },
				_enabler = null,
				_attach = null,
				_defer = null,
				_mod = null,
				_getter = null,
				_setter = (elem, usr) => { elem.val(usr.get_name()); },
				_clear = (elem) => { elem.val(''); }
			)
		);
		USERMGR_LIST_UI_DEFS.add(`${name}_groups`, new uic.UIInput(
				_elem = new multiselect.MultiSelect(
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
						'maxopts': API.SERVER_LIMITS.MAX_USER_GROUPS
					}
				),
				_pass = () => { return true; },
				_enabler = (elem, state) => {
					if (state) {
						elem.enable();
					} else {
						elem.disable();
					}
				},
				_attach = null,
				_defer = null,
				_mod = null,
				_getter = (elem) => { return elem.selected; },
				_setter = (elem, usr) => { elem.set(usr.get_groups()); },
				_clear = (elem) => { elem.remove_all(); }
			)
		);
		// Add the existing user data to the inputs.
		USERMGR_LIST_UI_DEFS.get(`${name}_name`).set(users[u]);
		USERMGR_LIST_UI_DEFS.get(`${name}_groups`).set(users[u]);
		i++;
	}
	// Conditionally enable all the inputs.
	USERMGR_LIST_UI_DEFS.all(function() { this.state(); });
}

$(document).ready(() => {
	API = new api.API(
		null,
		() => {
			user.users_load(API, usermgr_make_ui);
			flag_usermgr_ready = true;
		}
	);
});
