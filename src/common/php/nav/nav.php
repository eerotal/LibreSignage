<?php
	/*
	*  Navigation bar generation code for the
	*  LibreSignage web interface. $NAV_PAGE_LINKS
	*  should have all the pages to show on the bar
	*  listed.
	*/

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
		<div class="nav-container col-2 d-inline-flex justify-content-start">
		<p class="nav-item my-auto text-muted lead">LibreSignage</p>
		</div>
		<div class="nav-container col-10 d-inline-flex justify-content-end">
		<?php
		foreach (array_keys($NAV_PAGE_LINKS) as $k) {
			echo '<a class="nav-item nav-link';
			if (_is_page_active($k)) {
				echo ' active';
			}
			echo '" href="'.
				$NAV_PAGE_LINKS[$k]['uri'].'">';
			echo $k.'</a>';
		}
		?>
		</div>
	</div>
</nav>
