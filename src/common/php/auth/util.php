<?php

/*
*  Authentication utility functions.
*/

require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth/user.php');

function perm_req_chk(string $buf, User $u) {
	$p = explode(':', $buf);
	if (count($p) != 2) {
		throw new ArgException(
			"Invalid permission syntax '$buf'. Must ".
			"have 'grp:' or 'usr:' and value."
		);
	}
	switch($p[0]) {
		case 'grp':
			return $u->is_in_group($p[1]);
		case 'usr':
			return $u->get_name() == $p[1];
		default:
			throw new ArgException(
				"Invalid permission syntax ${p[0]}. ".
				"Expected 'grp' or 'usr'."
			);
	}
}

function check_perm(string $req, User $u) {
	/*
	*  A function for checking user permissions. Required
	*  groups and usernames are specified using a string
	*  such as the one below.
	*
	*    grp:editor&user:admin|user:user;
	*
	*  The 'grp:' keyword can be used to specify required
	*  groups and the 'usr:' keyword can be used to specify
	*  required usernames. These parts can be stringed together
	*  with & and | where & is the logical AND operation and
	*  | is the logical OR operator. $req must always end with
	*  a semicolon (;). This function returns TRUE if the user
	*  object $u matches the requirements and FALSE otherwise.
	*/
	$op = '';
	$buf = '';
	$r = FALSE;

	for ($i = 0; $i < strlen($req); $i++) {
		$c = substr($req, $i, 1);
		if ($c == '&' || $c == '|' || $c == ';') {
			// Handle current $buf w/ $op.
			switch($op) {
				case '&':
					$r = $r && perm_req_chk(
						$buf, $u
					);
					break;
				case '|':
					$r = $r || perm_req_chk(
						$buf, $u
					);
					break;
				default:
					$r = perm_req_chk($buf, $u);
					break;
			}
			if ($c == ';') { return $r; }

			// Clear $buf, store new $op.
			$buf = '';
			$op = $c;
		} else {
			$buf .= $c;
		}
	}
	throw new ArgException('Unexpected EOL.');
}

