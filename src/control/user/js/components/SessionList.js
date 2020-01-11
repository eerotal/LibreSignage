var User = require('libresignage/user/User');

/**
* Session list component for the User Settings page.
*/
class SessionList {
	/**
	* Create a new Session List object.
	*
	* @param {APIInterface} api       An APIInterface object.
	* @param {HTMLElement}  container The DOM node where the list is created.
	*/
	constructor(api, container) {
		this.api = api;
		this.container = container;
		this.user = new User(api);
	}

	/**
	* Create a HTML tr node for a row in a SessionList.
	*
	* @param {string}  who     Session description.
	* @param {string}  from    Session IP.
	* @param {number}  created Session creation unix timestamp in ms.
	* @param {boolean} cur     True for current session, false otherwise.
	*/
	static make_row_node(who, from, created, cur) {
		let tr = document.createElement('TR');
		tr.innerHTML = `
			<td>
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
				</table>
			</td>
		`;
		return tr;
	}

	/**
	* Fetch the userdata for the current user.
	*/
	async fetch() {
		await this.user.load(null);
	}

	/**
	* Render the SessionList.
	*/
	render() {
		this.container.innerHTML = "";
		for (let s of Object.values(this.user.get_sessions())) {
			this.container.appendChild(SessionList.make_row_node(
				s.who,
				s.from,
				s.created*1000,
				s.id === this.api.get_session().get_id()
			));
		}
	}
}
module.exports = SessionList;
