The APIEndpoint class
#####################

The APIEndpoint class is the class used to define LibreSignage
API endpoints. Using APIEnpoint objects makes it easy to handle
getting API parameters, setting the correct HTTP headers and
sending the correctly formatted API response. 

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

    | ``API_P_STR``: String type.
    | ``API_P_INT``: Integer type.
    | ``API_P_FLOAT``: Floating point type.
    | ``API_P_ARR``: Array type.
    | ``API_P_OPT``: Optional parameter.
    | ``API_P_STR_ALLOW_EMPTY``: Allow empty strings when
                                 ``API_P_STR`` is also set.
    | ``API_P_NULL``: NULL type. This can be used along with
                      one of the other type flags if needed.

  Note that multiple type flags can't be specified at the same
  time. The ``API_P_NULL`` flag can, however, be specified along
  with one of the other type flags.

  The format specified using this config option is used to
  automatically validate the received GET or POST parameters
  and an error is automatically sent as a response if the
  received arguments are invalid. If the FORMAT array is empty,
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

Using the APIEndpoint
+++++++++++++++++++++

Before any APIEndpoint functionality can be used, the API
endpoint must be initialized by calling the function
``api_endpoint_init()``. The function is defined as follows.

``function api_endpoint_init(APIEndpoint $endpoint, $user)``

The ``$endpoint`` argument is the APIEndpoint object of the API
endpoint. The ``$user`` argument is the User object of the
currently logged in user. The easiest way to get the User
object is to call the authentication system function
``auth_session_user()``. This function returns the User object
that can be passed straight to the ``api_endpoint_init()``
function. Note that the result of requiring a User object is
that API endpoints can only be accessed by logged in users.

The first thing the initialization function does is setup
a special API exception handler function that reports all
uncaught exceptions back to the calling user.

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
type. After this the function returns.
