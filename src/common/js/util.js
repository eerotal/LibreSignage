function resource_get(url, ready_callback) {
	var resource_req = new XMLHttpRequest();
	resource_req.addEventListener('load', ready_callback);
	resource_req.open('GET', url);
	resource_req.send();
}
