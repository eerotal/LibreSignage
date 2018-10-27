The APIEndpoint class
#####################

The APIEndpoint class is the class used to define LibreSignage
API endpoints. Using APIEnpoint objects makes it easy to handle
getting API parameters, setting the correct HTTP headers and
sending the correctly formatted API response. The APIEndpoint
system automatically supports normal GET and POST requests as
well as CORS requests from any domain.

This document is a detailed explanation of how the APIEndpoint
class works and how it is used. However, only a small subset of
the APIEndpoint functions is described. The best way to check out
the other ones is to just read the source code. Most functions
contain comments describing what they do and how they can be used.

Constructing the APIEndpoint object
+++++++++++++++++++++++++++++++++++

The APIEndpoint constructor function is defined as follows.

``public function __construct(array $config)``

The only argument ``$config`` is an associative array of config
options for the APIEndpoint object. Some of the config options
are optional and some are required. The following keys can be
used to configure the APIEndpoint. Optional and required options
are marked respectively

``APIEndpoint::MEHOD`` - *Required*

* One of the values in ``API_METHOD``. The possible values are
  ``API_METHOD['GET']`` and ``API_METHOD['POST']``. This value
  sets the HTTP method the endpoint uses.

``APIEndpoint::RESPONSE_TYPE`` - *Required*

* One of the values in ``API_RESPONSE``. The possible values
  are ``API_RESPONSE['JSON']`` and ``API_RESPONSE['TEXT']``.
  This sets the response type of the endpoint. The response
  data is automatically converted to the proper response type
  when APIEndpoint::send() is called.

``APIEndpoint::FORMAT`` - *Optional* - Default: array()

* This config option sets the format of the required HTTP
  GET or POST parameters. The value must be an array of the
  following form.

  ::

    array(
        'P1' => FLAGS1,
               .
               .
               .
        'PN' => FLAGSN
    );

  where *P1, ..., PN* are the parameter names and
  *FLAGS1, ..., FLAGSN* are the defined API parameter flags.
  The API parameter flags are constructed by OR'ing together
  a subset of the following constants.

    | ``API_P_STR``:          String type.
    | ``API_P_INT``:          Integer type.
    | ``API_P_FLOAT``:        Floating point type.
    | ``API_P_ARR``:          Array type.
    | ``API_P_NULL``:         NULL type.
    | ``API_P_OPT``:          Optional parameter.
    | ``API_P_EMPTY_STR_OK``: Allow empty strings when
                              ``API_P_STR`` is also set.
    | ``API_P_ANY``:          Accept any type.
    | ``API_P_UNUSED``:       Indicates an unused parameter. Defined as
                              ``API_P_ANY|API_P_EMPTY_STR_OK|API_P_OPT``.

  The format specified using this config option is used to
  automatically validate the received GET or POST parameters
  and an error is automatically sent as a response if the
  received arguments are invalid. If the FORMAT array is empty
  no validation is done.

``APIEndpoint::STRICT_FORMAT`` - *Optional* - Default: TRUE

* Select whether to consider HTTP parameters not in the
  ``APIEndpoint::FORMAT``` array invalid or not. If this config
  option is ``TRUE``, extra parameters are considered invalid.
  Otherwise extra parameters are accepted.

``APIEndpoint::REQ_QUOTA`` - *Optional* - Default: TRUE

* Check the calling user's API rate quota before proceeding
  with the API call. If the user has quota, accept the API call.
  Otherwise reject it. This also selects whether the API rate
  quota is used when the API endpoint is called.

``APIEndpoint::REQ_AUTH`` - *Optional* - Default: TRUE

* Make sure the caller is authenticated before granting access
  to the API endpoint. If this configuration option is TRUE,
  the user needs to authenticate using the auth_login.php
  endpoint. This endpoint returns a session token that should
  be passed to successive API endpoints in the 'Auth-Token'
  HTTP header. An error is thrown if the header doesn't exist
  or the token is invalid.

Using the APIEndpoint object
++++++++++++++++++++++++++++

Initialization
--------------

Before any APIEndpoint functionality can be used, the API
endpoint must be initialized by calling the function
``api_endpoint_init()``. The function is defined as follows.

``function api_endpoint_init(APIEndpoint $endpoint)``

The ``$endpoint`` argument is the APIEndpoint object created
earlier.

The first thing the initialization function does is setup
a special API exception handler function that reports all
uncaught exceptions back to the caller via the API.

After this the API system handles the call based on whether
it is a GET, POST or OPTIONS request. The OPTIONS method is
used by CORS preflight requests and is not normally used
manually. If any other request methods are used or if the
request method doesn't match the one required by the endpoint,
an exception is thrown.

The first thing the API does for all responses is to set
the proper HTTP response headers depending on how the
endpoint is configured.

In case the request is a GET or POST request, the system checks
the authentication status of the caller. If the 'Auth-Token'
HTTP header exists and contains a valid session token, the
caller is granted access. Otherwise an error is thrown.

After this the endpoint request data is loaded and parsed
into the APIEndpoint object.

Next the API rate quota is checked. If there is no quota left
and the APIEndpoint config requires quota, the function throws
an error.

If all the steps above succeed, the function returns and the
APIEndpoint object is ready to be used.

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
  the caller should check whether it exists first with
  ``APIEndpoint::has()``.

Creating and sending the API response
-------------------------------------

Creating the API response is quite simple with the functions defined
in the APIEndpoint class. Only two functions are needed for handling
the response. The functions are defined as follows.

``public function resp_set($resp)``

* Set the response data of the APIEndpoint object. ``$resp`` is the
  object with the data. Note that ``$resp`` should be the proper
  type corresponding to the selected ``APIEndpoint::RESPONSE_TYPE``.
  Ie. API endpoints with a ``TEXT`` response should set a string
  as the response data. ``JSON`` endpoints can use all the standard
  data types like arrays, integers, strings etc. These are automatically
  JSON encoded when sending the response.

``public function send()``

* Send the response data set with ``APIEndpoint::resp_set()``. Since
  all API responses are *guaranteed* to have the ``error`` value set,
  this function automatically sets it to zero if it isn't already set.
  It is, however, possible to set the error value in the API endpoint
  code if needed. This function also exits the API endpoint meaning
  that no code is executed after this function is finished.
