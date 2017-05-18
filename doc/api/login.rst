Login API
=============

These endpoints will allow you to login as a customer (aka Contacts in the backend). Base URI is `/api/v2/login`.

When your login is successful, you the structure of `/api/v2/customers` is exposed. Please refer to this
documentation for further informations.

Login Process
--------------

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/login

+--------------------------+----------------+-------------------------------------------+
| Parameter                | Parameter type | Description                               |
+==========================+================+===========================================+
| Authorization            | header         | Token received during authentication      |
+--------------------------+----------------+-------------------------------------------+
| email                    | request        | Customer's email **required**             |
+--------------------------+----------------+-------------------------------------------+
| password                 | request        | Customer's password **required**          |
+--------------------------+----------------+-------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/login \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "email": "john.diggle@yahoo.com",
                "password": "secret"
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 Success

.. code-block:: json

    {
        "code": 200,
        "message": "Verification successful",
        "success": {
            "customer": {
                "id":399,
                "email":"john.diggle@yahoo.com",
                "firstName":"John",
                "lastName":"Diggle",
                "address": "55, Sunrise St.",
                "zip": "F-29000",
                "city": "Quimper",
                "country": "France",
                "phoneNumber": "+987654321",
                "subscribedToNewsletter": true
            }
        }
    }

If you try to login without giving a password or an email address, you will receive a ``400 Bad Request`` error.

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/login \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "password": "secret"
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 400 Bad Request

.. code-block:: json

    {
        "code": 400,
        "message": "Validation Failed",
        "errors": {
            "children": {
                "email": {
                    "errors": [
                        "Please enter your email."
                    ]
                },
                "password": {}
            }
        }
    }

If you try to login without giving a correct password or email, you will receive a ``401 Unauthorized`` error.

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/login \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "email": "john.diggle@yahoo.com",
                "password": "false-secret"
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 401 Unauthorized

.. code-block:: json

      {
          "code": 401,
          "message": "Verification failed"
      }
      
Logout Process
--------------
      
Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/logout

+--------------------------+----------------+-------------------------------------------+
| Parameter                | Parameter type | Description                               |
+==========================+================+===========================================+
| Authorization            | header         | Token received during authentication      |
+--------------------------+----------------+-------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/logout \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '{}'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 Success

.. code-block:: json

    {
        "code": 200,
        "message": "Loggout successful"
    }
