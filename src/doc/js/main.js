var $ = require('jquery');
var api = require('ls-api');
var API = null;

$(document).ready(() => {
	API = new api.API(null, null);
});
