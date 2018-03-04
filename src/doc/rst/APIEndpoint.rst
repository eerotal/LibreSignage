The APIEndpoint class
#####################

The APIEndpoint class is the class used to define LibreSignage
API endpoints. Using APIEnpoint objects makes it easy to handle
getting API parameters, setting the correct HTTP headers and
sending the correctly formatted API response. This document is
a detailed explanation of how the APIEndpoint class works and
how it is used. 

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

Using the APIEndpoint object
++++++++++++++++++++++++++++

Initialization
--------------

Before any APIEndpoint functionality can be used, the API
endpoint must be initialized by calling the function
``api_endpoint_init()``. The function is defined as follows.

``function api_endpoint_init(APIEndpoint $endpoint, $user)``

The ``$endpoint`` argument is the APIEndpoint object created
earlier. The ``$user`` argument is the User object of the
currently logged in user. The easiest way to get the User
object is to call the authentication system function
``auth_session_user()``. This function returns the User object
of the current session which can be passed straight to
``api_endpoint_init()``. Note that the result of requiring
a User object is that API endpoints can only be accessed by
logged in users.

The first thing the initialization function does is setup
a special API exception handler function that reports all
uncaught exceptions back to the calling user via the API.

The initialization function then checks that the supplied User
object is not not ``NULL`` to confirm that a User is logged in.
After this the API rate quota is checked. If there is no quota
left and the APIEndpoint config requires quota, the function
throws an error. Catching this exception is not needed since
the default API exception handler is meant to report this
exception to the calling user.

If there is sufficient quota left, the initialization function
loads the GET or POST data for the API endpoint and sets the
correct HTTP ``Content-Type`` header for the configured response
type. After this the function returns successfully.

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
  that no code is executed after this function is finisehd.
