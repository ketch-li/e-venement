Customers API
=============

These endpoints will allow you to easily manage customers (aka Contacts in the backend). Base URI is `/api/v2/customers`.

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+----------------+------------------------------------------+
| Field          | Description                              |
+================+==========================================+
| id             | Id of customer                           |
+----------------+------------------------------------------+
| email          | Customers email                          |
+----------------+------------------------------------------+
| firstName      | Customers first name                     |
+----------------+------------------------------------------+
| lastName       | Customers last name                      |
+----------------+------------------------------------------+

If you request for a more detailed data, you will receive an object with following fields:

+-------------------------+-------------------------------------------------------------------------------------+
| Field                   | Description                                                                         |
+=========================+=====================================================================================+
| id                      | Id of customer                                                                      |
+-------------------------+-------------------------------------------------------------------------------------+
| email                   | Customers email                                                                     |
+-------------------------+-------------------------------------------------------------------------------------+
| firstName               | Customers first name                                                                |
+-------------------------+-------------------------------------------------------------------------------------+
| lastName                | Customers last name                                                                 |
+-------------------------+-------------------------------------------------------------------------------------+
| shortName               | Customers short name                                                                |
+-------------------------+-------------------------------------------------------------------------------------+
| address                 | Customers postal address                                                            |
+-------------------------+-------------------------------------------------------------------------------------+
| zip                     | Customers ZIP                                                                       |
+-------------------------+-------------------------------------------------------------------------------------+
| city                    | Customers city                                                                      |
+-------------------------+-------------------------------------------------------------------------------------+
| country                 | Customers country                                                                   |
+-------------------------+-------------------------------------------------------------------------------------+
| phoneNumber             | Customers phone number                                                              |
+-------------------------+-------------------------------------------------------------------------------------+
| datesOfBirth            | Dates of birth (`ISO 8601 Extended Format <https://fr.wikipedia.org/wiki/ISO_8601>`)|
+-------------------------+-------------------------------------------------------------------------------------+
| locale                  | Spoken language                                                                     |
+-------------------------+-------------------------------------------------------------------------------------+
| uid                     | Unique Identifier                                                                   |
+-------------------------+-------------------------------------------------------------------------------------+
| subscribedToNewsletter  | Whether or not the customer is subscribed to newsletter                             |
+-------------------------+-------------------------------------------------------------------------------------+

Creating a Customer
-------------------

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/customers/

+--------------------------+----------------+-------------------------------------------+
| Parameter                | Parameter type | Description                               |
+==========================+================+===========================================+
| Authorization            | header         | Token received during authentication      |
+--------------------------+----------------+-------------------------------------------+
| email                    | request        | Customer's email **required**             |
+--------------------------+----------------+-------------------------------------------+
| firstName                | request        | Customer's first name                     |
+--------------------------+----------------+-------------------------------------------+
| lastName                 | request        | Customer's last name                      |
+--------------------------+------------------------------------------------------------+
| address                  | request        | Customers postal address                  |
+--------------------------+------------------------------------------------------------+
| zip                      | request        | Customers ZIP                             |
+--------------------------+------------------------------------------------------------+
| city                     | request        | Customers city                            |
+--------------------------+------------------------------------------------------------+
| country                  | request        | Customers country                         |
+--------------------------+------------------------------------------------------------+
| phoneNumber              | request        | Customers phone number                    |
+--------------------------+------------------------------------------------------------+
| subscribedToNewsletter   | request        | Empty if not subscribed, else fulfilled by anything |
+--------------------------+------------------------------------------------------------+
| password                 | request        | Customers new password                    |
+--------------------------+------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/ \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "firstName": "John",
                "lastName": "Diggle",
                "email": "john.diggle@yahoo.com",
                "address": "7b, Sunset St.",
                "zip": "F-29000",
                "city": "Quimper",
                "country": "France",
                "phoneNumber": "+123456789",
                "subscribedToNewsletter": "",
                "password": "secret"
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 201 Created

.. code-block:: json

    {
        "id":409,
        "email":"john.diggle@yahoo.com",
        "firstName":"John",
        "lastName":"Diggle",
        "address": "7b, Sunset St.",
        "zip": "F-29000",
        "city": "Quimper",
        "country": "France",
        "phoneNumber": "+123456789",
        "subscribedToNewsletter": ""
    }

If you try to create a customer without email, you will receive a ``400 Bad Request`` error.

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/ \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST

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
                "firstName": {},
                "lastName": {},
                "email": {
                    "errors": [
                        "Please enter your email."
                    ]
                },
                "phoneNumber": {},
                "address": {},
                "zip": {},
                "city": {},
                "country": {},
                "phoneNumber": {},
                "subscribedToNewsletter": {},
                "password": {}
            }
        }
    }

Getting a Single Customer
-------------------------

You can request detailed customer information by executing the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/customers/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | url attribute  | Id of the requested resource                                      |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/399 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "id":399,
        "email":"jean.martin@linux.fr",
        "firstName":"Jean",
        "lastName":"Martin",
        "address": "1a, Sunrise St.",
        "zip": "F-29000",
        "city": "Quimper",
        "country": "France",
        "phoneNumber": "+987654321",
        "subscribedToNewsletter": "yes"
    }

Collection of Customers
-----------------------

You can retrieve the full customers list by making the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/customers/

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| page          | query          | *(optional)* Number of the page, by default = 1                   |
+---------------+----------------+-------------------------------------------------------------------+
| limit         | query          | *(optional)* Number of items to display per page, by default = 10 |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/ \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "page":1,
        "limit":10,
        "pages":21,
        "total":205,
        "_links":{
            "self":{
                 "href":"\/api\/v2\/customers\/?page=1&limit=10"
            },
            "first":{
                 "href":"\/api\/v2\/customers\/?page=1&limit=10"
            },
            "last":{
                 "href":"\/api\/v2\/customers\/?page=21&limit=10"
            },
            "next":{
                 "href":"\/api\/v2\/customers\/?page=2&limit=10"
            }
        },
        "_embedded":{
            "items":[
                 {
                        "id":407,
                        "email":"random@gmail.com",
                        "firstName":"Random",
                        "lastName":"Doe"
                 },
                 {
                        "id":406,
                        "email":"customer@email.com",
                        "firstName":"Alexanne",
                        "lastName":"Blick"
                 },
                 {
                        "id":405,
                        "user":{
                             "id":404,
                             "username":"gaylord.bins@example.com",
                             "enabled":true
                        },
                        "email":"gaylord.bins@example.com",
                        "firstName":"Dereck",
                        "lastName":"McDermott"
                 },
                 {
                        "id":404,
                        "user":{
                             "id":403,
                             "username":"lehner.gerhard@example.com",
                             "enabled":false
                        },
                        "email":"lehner.gerhard@example.com",
                        "firstName":"Benton",
                        "lastName":"Satterfield"
                 },
                 {
                        "id":403,
                        "user":{
                             "id":402,
                             "username":"raheem.ratke@example.com",
                             "enabled":false
                        },
                        "email":"raheem.ratke@example.com",
                        "firstName":"Rusty",
                        "lastName":"Jerde"
                 },
                 {
                        "id":402,
                        "user":{
                             "id":401,
                             "username":"litzy.morissette@example.com",
                             "enabled":false
                        },
                        "email":"litzy.morissette@example.com",
                        "firstName":"Omer",
                        "lastName":"Schaden"
                 },
                 {
                        "id":401,
                        "user":{
                             "id":400,
                             "username":"bbeer@example.com",
                             "enabled":true
                        },
                        "email":"bbeer@example.com",
                        "firstName":"Willard",
                        "lastName":"Hand"
                 },
                 {
                        "id":400,
                        "user":{
                             "id":399,
                             "username":"qtrantow@example.com",
                             "enabled":false
                        },
                        "email":"qtrantow@example.com",
                        "firstName":"Caterina",
                        "lastName":"Koelpin"
                 },
                 {
                        "id":399,
                        "user":{
                             "id":398,
                             "username":"cgulgowski@example.com",
                             "enabled":false
                        },
                        "email":"cgulgowski@example.com",
                        "firstName":"Levi",
                        "lastName":"Friesen"
                 }
            ]
        }
    }

Updating a Customer
-------------------

You can request full or partial update of resource, using the POST method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/customers/{id}

+--------------------------+----------------+-------------------------------------------+
| Parameter                | Parameter type | Description                               |
+==========================+================+===========================================+
| Authorization            | header         | Token received during authentication      |
+--------------------------+----------------+-------------------------------------------+
| id                       | url attribute  | Id of the requested resource              |
+--------------------------+----------------+-------------------------------------------+
| email                    | request        | Customer's email **required**             |
+--------------------------+----------------+-------------------------------------------+
| firstName                | request        | Customer's first name                     |
+--------------------------+----------------+-------------------------------------------+
| lastName                 | request        | Customer's last name                      |
+--------------------------+------------------------------------------------------------+
| address                  | request        | Customers postal address                  |
+--------------------------+------------------------------------------------------------+
| zip                      | request        | Customers ZIP                             |
+--------------------------+------------------------------------------------------------+
| city                     | request        | Customers city                            |
+--------------------------+------------------------------------------------------------+
| country                  | request        | Customers country                         |
+--------------------------+------------------------------------------------------------+
| phoneNumber              | request        | Customers phone number                    |
+--------------------------+------------------------------------------------------------+
| subscribedToNewsletter   | request        | Empty if not subscribed, else fulfilled by anything |
+--------------------------+------------------------------------------------------------+
| password                 | request        | Customers new password                    |
+--------------------------+------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/399 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "firstName": "John",
                "lastName": "Diggle",
                "email": "john.diggle@yahoo.com",
                "address": "7b, Sunset St.",
                "zip": "F-29000",
                "city": "Quimper",
                "country": "France",
                "phoneNumber": "+123456789",
                "subscribedToNewsletter": "",
                "password": "secret"
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 No Content

In order to perform a partial update, you should use a POST method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/customers/{id}

+--------------------------+----------------+--------------------------------------------------+
| Parameter                | Parameter type | Description                                      |
+==========================+================+==================================================+
| Authorization            | header         | Token received during authentication             |
+--------------------------+----------------+--------------------------------------------------+
| id                       | url attribute  | Id of the requested resource                     |
+--------------------------+----------------+--------------------------------------------------+
| email                    | request        | *(optional)* **(unique)** Customers email        |
+--------------------------+----------------+--------------------------------------------------+
| firstName                | request        | *(optional)* Customers first name                |
+--------------------------+----------------+--------------------------------------------------+
| lastName                 | request        | *(optional)* Customers last name                 |
+--------------------------+----------------+--------------------------------------------------+
| groups                   | request        | *(optional)* Array of groups customer belongs to |
+--------------------------+----------------+--------------------------------------------------+
| gender                   | request        | *(optional)* Customers gender                    |
+--------------------------+----------------+--------------------------------------------------+
| birthday                 | request        | *(optional)* Customers birthday                  |
+--------------------------+----------------+--------------------------------------------------+
| user[plainPassword]      | request        | *(optional)* Users plain password.               |
+--------------------------+----------------+--------------------------------------------------+
| user[authorizationRoles] | request        | *(optional)* Array of users roles.               |
+--------------------------+----------------+--------------------------------------------------+
| user[enabled]            | request        | *(optional)* Flag set if user is enabled.        |
+--------------------------+----------------+--------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/399 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X PATCH \
        --data '{"firstName": "Joe"}'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 No Content

Deleting a Customer
-------------------

Definition
^^^^^^^^^^

.. code-block:: text

    DELETE /api/v2/customers/{id}

+---------------+----------------+-------------------------------------------+
| Parameter     | Parameter type | Description                               |
+===============+================+===========================================+
| Authorization | header         | Token received during authentication      |
+---------------+----------------+-------------------------------------------+
| id            | url attribute  | Id of the requested resource              |
+---------------+----------------+-------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/399 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json" \
        -X DELETE

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 No Content

Collection of all customer orders
---------------------------------

To browse all orders for specific customer, you can do the following call:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/customers/{id}/orders/

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| page          | query          | *(optional)* Number of the page, by default = 1                   |
+---------------+----------------+-------------------------------------------------------------------+
| paginate      | query          | *(optional)* Number of items to display per page, by default = 10 |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/7/orders/ \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

    {
        "page":1,
        "limit":10,
        "pages":1,
        "total":1,
        "_links":{
            "self":{
                "href":"\/api\/v2\/customers\/2\/orders\/?page=1&limit=10"
            },
            "first":{
                "href":"\/api\/v2\/customers\/2\/orders\/?page=1&limit=10"
            },
            "last":{
                "href":"\/api\/v2\/customers\/2\/orders\/?page=1&limit=10"
            }
        },
        "_embedded":{
            "items":[ /*...*/ ],
                    "itemsTotal":5668,
                    "adjustments":[
                        {
                            "id":27,
                            "type":"shipping",
                            "label":"FedEx",
                            "amount":1530
                        }
                    ],
                    "adjustmentsTotal":1530,
                    "total":7198,
                    "state":"new",
                    "customer":{
                        "id":2,
                        "email":"metz.ted@beer.com",
                        "firstName":"Dangelo",
                        "lastName":"Graham",
                        "_links":{
                            "self":{
                                "href":"\/api\/v2\/customers\/2"
                            }
                        }
                    },
                    "payments":[
                        {
                            "id":2,
                            "method":{
                                "id":1,
                                "code":"cash_on_delivery",
                                "_links":{
                                    "self":{
                                        "href":"\/api\/v2\/payment-methods\/cash_on_delivery"
                                    }
                                }
                            },
                            "amount":7198,
                            "state":"new",
                            "_links":{
                                "self":{
                                    "href":"\/api\/v2\/payments\/2"
                                },
                                "payment-method":{
                                    "href":"\/api\/v2\/payment-methods\/cash_on_delivery"
                                },
                                "order":{
                                    "href":"\/api\/v2\/orders\/2"
                                }
                            }
                        }
                    ],
                    "currencyCode":"EUR",
                    "localeCode":"en_US",
                    "checkoutState":"completed"
                }
            ]
        }
    }
