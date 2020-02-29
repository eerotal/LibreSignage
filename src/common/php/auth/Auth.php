<?php

namespace libresignage\common\php\auth;

use libresignage\common\php\Log;
use libresignage\common\php\Config;
use libresignage\common\php\ErrorHandler;
use libresignage\common\php\auth\User;

/**
* Class with functions for authenticating users.
*/
final class Auth {
	const TOKEN_HEADER = 'Auth-Token';
	const TOKEN_COOKIE = 'session_token';

	/**
	* Verify the supplied login credentials and return the corresponding
	* User object if the credentials are valid. NULL is returned otherwise.
	*
	* @param string $user The username.
	* @param string $pass The password.
	*
	* @return User|NULL   A User object or NULL if the creds were invalid.
	*/
	public static function verify_creds(string $user, string $pass) {
		/*
		* Make sure the supplied user exists. $pass can also be empty
		* if the user is a "no login" user.
		*/
		if (empty($user) || !User::exists($user)) {
			return NULL;
		}

		$u = new User();
		$u->load($user);
		if ($u->verify_password($pass)) { return $u; }
		return NULL;
	}

	/**
	* Verify an authentication token.
	*
	* @param string $tok The token to verify.
	*
	* @return array|NULL An array with the keys 'session' and 'user'.
	*                    'session' is the matching Session object and
	*                    'user' is the corresponding User object. NULL
	*                    is returned if not session matched $tok.
	*/
	public static function verify_token(string $tok) {
		$session = NULL;
		if (empty($tok)) { return NULL; }

		foreach (User::all() as $k => $u) {
			$session = $u->session_token_verify($tok);
			if ($session !== NULL) {
				return [
					'user' => $u,
					'session' => $session
				];
			}
		}
	}

	/**
	* Verify an authentication token supplied in a cookie.
	*
	* @see Auth::verify_token() for return values.
	*/
	public static function verify_cookie() {
		if (empty($_COOKIE[Auth::TOKEN_COOKIE])) {
			return NULL;
		} else {
			return Auth::verify_token($_COOKIE[Auth::TOKEN_COOKIE]);
		}
	}

	/**
	* Ensure a user is authenticated and has permission to access
	* a resource. This function is intended to be called at the
	* beginning of web interface pages to only grant access for
	* specific users and/or groups.
	*
	* @param array $user_wl  An array of whitelisted users.
	* @param array $group_wl An array of whitelisted groups.
	* @param bool $redir     If TRUE, clients are redirected to error
	*                        pages if they don't have permission to
	*                        access a page.
	* @param string $token   An authentication token to use. If left
	*                        unspecified, the token is read from
	*                        the Auth::TOKEN_COOKIE cookie.
	*/
	public static function web_auth(
		array $user_wl = NULL,
		array $group_wl = NULL,
		bool $redir = FALSE,
		string $token = NULL
	) {
		$d = NULL;
		if (empty($token)) {
			$d = Auth::verify_cookie();
		} else {
			$d = Auth::verify_token($token);
		}

		if ($d === NULL) {
			if ($redir) {
				header(
					'Location: '.Config::config('LOGIN_PAGE').
					'?redir='.urlencode($_SERVER['REQUEST_URI'])
				);
				exit(0);
			}
			return NULL;
		}
		if ($user_wl !== NULL) {
			if (!$d['user']->is_user($user_wl)) {
				if ($redir) { ErrorHandler::handle(ErrorHandler::FORBIDDEN); }
				return NULL;
			}
		}
		if ($group_wl !== NULL) {
			if (!$d['user']->is_in_group($group_wl)) {
				if ($redir) { ErrorHandler::handle(ErrorHandler::FORBIDDEN); }
				return NULL;
			}
		}
		return $d;
	}
}
