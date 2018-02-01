<?php
	/*
	*  Navigation bar generation code for the
	*  LibreSignage web interface. $NAV_PAGE_LINKS
	*  should have all the pages to show on the bar
	*  listed.
	*/

	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/auth.php');

	$NAV_PAGE_LINKS = array(
		'Display' => array(
			'uri' => APP_PAGE,
			'active' => FALSE
		),
		'Editor' => array(
			'uri' => EDITOR_PAGE,
			'active' => FALSE
		),
		'User Manager' => array(
			'uri' => USER_MGR_PAGE,
			'active' => FALSE
		),
		'Control Panel' => array(
			'uri' => CONTROL_PANEL_PAGE,
			'active' => FALSE
		)
	);

	$req = $_SERVER['REQUEST_URI'];
	foreach ($NAV_PAGE_LINKS as &$pg) {
		if (substr($req, 0, strlen($pg['uri'])) == $pg['uri']) {
			$pg['active'] = TRUE;
			break;
		}
	}

	function _is_page_active(string $name) {
		global $NAV_PAGE_LINKS;
		if (!array_key_exists($name, $NAV_PAGE_LINKS)) {
			return FALSE;
		}
		return $NAV_PAGE_LINKS[$name]['active'];
	}
?>

<nav class="nav nav-pills">
	<div class="row container-fluid mx-auto">
		<div class="col-md-2 my-auto m-0 p-0">
			<p class="text-muted lead my-auto">
				LibreSignage
			</p>
		</div>
		<div class="col-md-10 p-0 m-0">
			<div class="row container-fluid d-flex justify-content-end m-0 p-0">
				<?php
				foreach (array_keys($NAV_PAGE_LINKS) as $k) {
					echo '<div class="col-md-auto m-0 p-0 pt-2 pb-2">';
					echo '<a class="nav-item nav-link';
					if (_is_page_active($k)) {
						echo ' active';
					}
					echo '" href="'.
						$NAV_PAGE_LINKS[$k]['uri'].'">';
					echo $k.'</a>';
					echo '</div>';
				}
				?>
				<div class="col-md-auto nav-item my-auto pt-2 pb-2">
					<a class="my-auto" href="/control/usermgr">[
						<?php
							echo auth_get_logged_in_user();
						?>
					]</a>
					<a class="d-inline text-danger nav-link p-0 pl-2" href="/logout">
						Logout
					</a>
				</div>
			</div>
		</div>
	</div>
</nav>
