var $ = require('jquery');
var User = require('ls-user').User;

const session_row = (who, from, created, cur) => `
<tr><td>
	<table class="user-session-row">
		<tr>
			<th class="text-right">Name:</th>
			<td>${who}</td>
		</tr>
		<tr>
			<th class="text-right">IP:</th>
			<td>${from}</td>
		</tr>
		<tr>
			<th class="text-right">Renewed:</th>
			<td>${new Date(created).toUTCString()}</td>
		</tr>
		<tr>
			<th class="text-right">Your session:</th>
			<td>
				<span style="color: green;">${cur ? "Yes" : ""}</span>
				<span style="color: red;">${cur ? "" : "No"}</span>
			</td>
		</tr>
</table></td>
`;

class SessionList {
	constructor(api, where) {
		this.api = api;
		this.where = where;
		this.user = new User(api);
	}

	async fetch() {
		/*
		*  Fetch the userdata for the current user.
		*/
		await this.user.load(null);
	}

	render() {
		/*
		*  Render the component in the HTML DOM
		*  element 'this.where'.
		*/
		$(this.where).html('');
		for (let s of Object.values(this.user.get_sessions())) {
			$(this.where).append(session_row(
				s.who,
				s.from,
				s.created,
				s.id === this.api.get_session().get_id()
			));
		}
	}
}
exports.SessionList = SessionList;
