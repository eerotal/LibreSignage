The APIEndpoint class
#####################

The APIEndpoint class is the class used to define LibreSignage
API endpoints. Using APIEnpoint objects makes it easy to handle
getting API parameters, setting the correct HTTP headers and
sending the correctly formatted API response. The APIEndpoint
system supports GET and POST requests.

This document is a detailed explanation of how the APIEndpoint
class works and how it is used. However, only a small subset of
the APIEndpoint functions are described. The best way to check out
the other ones is to just read the source code. Most functions
contain comments describing what they do and how they can be used.

Constructing the APIEndpoint object
+++++++++++++++++++++++++++++++++++

The APIEndpoint constructor function is defined as follows.

``public function __construct(array $config)``

You should construct the API endpoint at the very beginning of
each endpoint file. That initializes the endpoint object and
sets up some global configuration. The only argument ``$config``
is an associative array of config options for the APIEndpoint object.
Some of the config options are optional and some are required.
The following keys can be used to configure the APIEndpoint.
Optional and required options are marked respectively

``APIEndpoint::METHOD`` - *Required*

* Default: ``<None>``
* One of the values in the ``API_METHOD`` array that's defined in
  *src/api/defs.php*. This value sets the HTTP request method the
  endpoint uses. The available values are ``API_METHOD['GET']``
  and ``API_METHOD['POST']``.

``APIEndpoint::REQUEST_TYPE`` - *Optional*

* Default: ``API_MIME['application/json']``
* This config option sets the expected request type of the endpoint.
  The value should be one of the values in the ``API_MIME`` array
  defined in *src/api/defs.php*. Some of the valid values are
  ``API_MIME['application/json']`` and ``API_MIME['text/plain']``.
  If a user calls an endpoint with a different Content-Type header
  than what's configured using this option, the server automatically
  returns an invalid request error. This option only has an effect
  if the endpoint expects POST requests.

``APIEndpoint::RESPONSE_TYPE`` - *Required*

* Default: ``API_MIME['application/json']``
* This config option sets the response type of the endpoint. The
  value should be one of the values in the ``API_MIME`` array defined
  in *src/api/defs.php*. Some of the available values are
  ``API_MIME['application/json']`` and ``API_MIME['text/plain']``.
  The API system automatically converts the response data according
  to this config option. For example, if the response type is set to
  *application/json*, the server automatically converts the response
  data set with ``APIEndpoint::resp_set()`` to JSON before sending it.

``APIEndpoint::FORMAT_[URL/BODY]`` - *Optional*

* Default: ``[]``
* This option is used to configure the expected HTTP request data.
  *FORMAT_URL* controls the URL parameters and *FORMAT_BODY* controls
  the HTTP body parameters. An array of the following form should
  be passed to these config options.

  ::

    [
        'P1' => FLAGS1,
               .
               .
               .
        'PN' => FLAGSN
    ];

  where *P1, ..., PN* are the parameter names and
  *FLAGS1, ..., FLAGSN* are the defined API parameter flags.
  The API parameter flags are constructed by OR'ing together
  a subset of the following constants.

  Normal type flags:

    | ``API_P_STR``:            String type.
    | ``API_P_INT``:            Integer type.
    | ``API_P_FLOAT``:          Floating point type.
    | ``API_P_OPT``:            Optional parameter.
    | ``API_P_NULL``:           NULL type.
    | ``API_P_BOOL``:           Boolean type.
    | ``API_P_ANY``:            Any of the types above.

  Array flags:

    | ``API_P_ARR_INT``:        Array with all integer values.
    | ``API_P_ARR_STR``:        Array with all string values.
    | ``API_P_ARR_FLOAT``:      Array with all float values.
    | ``API_P_ARR_BOOL``:       Array with all boolean values.
    | ``API_P_ARR_MIXED``:      Array with mixed type values.
    | ``API_P_ARR_ANY``:        Any of the array types above.

  Special flags.

    | ``API_P_EMPTY_STR_OK``:   Allow empty strings.
    | ``API_P_UNUSED``:         Specify an unused parameter.


  The format can also contain nested arrays if needed.

  The format specified using this config option is used to
  automatically validate the received HTTP data and an error
  is automatically sent as a response if the received arguments
  are invalid.

``APIEndpoint::STRICT_FORMAT`` - *Optional*

* Default: ``TRUE``
* Select whether to consider extra HTTP parameters that aren't
  included in the ``APIEndpoint::FORMAT_[URL/BODY]`` arrays
  invalid or not. If this config option is ``TRUE``, extra
  parameters are considered invalid. Otherwise extra parameters
  are accepted.

``APIEndpoint::REQ_QUOTA`` - *Optional*

* Default: ``TRUE``
* This options selects whether to enable API rate limiting.

``APIEndpoint::REQ_AUTH`` - *Optional*

* Default: ``TRUE``
* Require authentication for this API endpoint. The caller may
  either authenticate using the *Auth-Token* HTTP header or by
  sending a *session_token* cookie. These should contain a valid
  server generated session token for authentication to succeed.
  Note that cookie authentication is only allowed if the
  ``APIEndpoint::ALLOW_COOKIE_AUTH`` config option is TRUE.

``APIEndpoint::ALLOW_COOKIE_AUTH`` - *Optional*

* Default: ``FALSE``
* Select whether to allow authentication via the *session_token*
  cookie that contains a session token. **You should only set this
  option to TRUE when actually needed. NEVER set this option to TRUE
  on endpoints that alter data on the server. That would basically
  enable CSRF attacks on those endpoints.**

Using the APIEndpoint object
++++++++++++++++++++++++++++

Getting POST or GET parameters
------------------------------

There are two main functions used for handling the POST and
GET parameters of API requests. The functions are defined
as follows.

``public function has(string $key, bool $null_check = FALSE)``

* The ``APIEndpoint::has()`` function can be used for checking
  whether the APIEndpoint object has a specific parameter. 
  ``$key`` is the name of the request parameter. This function
  returns ``TRUE`` if the parameter exists and ``FALSE`` otherwise.
  If ``$null_check`` is ``TRUE``, ``NULL`` parameters are considered
  empty and ``FALSE`` is returned for them.

``public function get(string $key)``

* The ``APIEndpoint::get()`` function can be used to get the
  value of an API request parameter. If the parameter is optional,
  the caller should check whether it exists with ``APIEndpoint::has()``
  first.

Creating and sending the API response
-------------------------------------

Creating the API response is quite simple with the functions defined
in the APIEndpoint class. Only two functions are needed for handling
the response. The functions are defined as follows.

``public function resp_set($resp)``

* Set the response data of the APIEndpoint object. ``$resp`` is the
  object with the data. Note that ``$resp`` should be of the proper
  type corresponding to the selected ``APIEndpoint::RESPONSE_TYPE``.

    * A *string* for ``text/plain``.
    * An *array* for ``application/json``.
    * An *open file handle* for ``libresignage/passthrough``.

* The ``libresignage/passthrough`` mimetype is a special one. If it's
  used, the server reads the contents of the open file handle starting
  from the current position and sends them to the caller. This is useful
  for sending binary assets to the caller.

``public function send()``

* Convert the response data to the configured format and send it.
