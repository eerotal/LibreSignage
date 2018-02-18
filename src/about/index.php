<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/php/config.php');
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
		<link rel="stylesheet" href="/common/css/footer.css">
		<link rel="stylesheet" href="/common/css/default.css">
		<link rel="stylesheet" href="/about/css/about.css">
		<title>About LibreSignage</title>
	</head>
	<body>
		<main class="container-fluid h-100">
			<div class="container w-75 mx-auto text-justify">
				<h1 id="about-heading" class="display-3 text-center">
					LibreSignage
				</h1>
				<p class="lead text-center">
				An Open Source Digital Signage solution</p>

				<p>LibreSignage is a free and open source,
				lightweight and easy-to-use digital
				signage solution. LibreSignage runs on a
				HTTP web server serving content to
				normal web browsers that can be used to
				display the content on a variety of
				different devices. This makes it possible
				to use LibreSignage on basically any device
				that has acccess to the internet and that
				can display web pages.</p>

				<p>LibreSignage is made possible by the
				fantastic open source libraries
				<a href="https://getbootstrap.com/">Bootstrap</a>,
				<a href="https://jquery.com/">jQuery</a>,
				<a href="https://popper.js.org/">Popper.js</a> and
				<a href="https://ace.c9.io/">Ace</a>.
				The licensing information for these libraries can
				be found
				<a href="/doc/md/LIBRARY_LICENSES.md">here</a>.</p>

				<p>LibreSignage is licensed under the
				permissive BSD 3-clause license, which
				can be seen below.</p>

				<div class="container text-center">
					<pre><?php
						echo file_get_contents($_SERVER['DOCUMENT_ROOT'].
								LIBRESIGNAGE_LICENSE_MD);
					?></pre>
				</div>
			</div>
		</main>
		<?php
			require_once($_SERVER['DOCUMENT_ROOT'].FOOTER_PATH);
		?>

		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
	</body>
</html>
