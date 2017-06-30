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

+-------------------------+----------------------------------------------------------------------------------------------------------+
| Field                   | Description                                                                                              |
+=========================+==========================================================================================================+
| id                      | Id of customer                                                                                           |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| email                   | Customers email                                                                                          |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| otherEmail              | *Optional* Customers email                                                                               |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| firstName               | Customers first name                                                                                     |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| lastName                | Customers last name                                                                                      |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| shortName               | Customers short name                                                                                     |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| address                 | Customers postal address                                                                                 |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| zip                     | Customers ZIP                                                                                            |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| city                    | Customers city                                                                                           |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| country                 | Customers country                                                                                        |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| phoneNumber             | Customers phone number                                                                                   |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| datesOfBirth            | Dates of birth `ISO 8601 Extended Format <https://fr.wikipedia.org/wiki/ISO_8601>`_                      |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| locale                  | Spoken language                                                                                          |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| uid                     | Unique Identifier                                                                                        |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| subscribedToNewsletter  | Whether or not the customer is subscribed to newsletter                                                  |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| createdAt               | *Optional* Datetime of creation [ISO 8601 Extended Format](https://fr.wikipedia.org/wiki/ISO_8601)       |
+-------------------------+----------------------------------------------------------------------------------------------------------+
| updatedAt               | *Optional* Datetime of last update [ISO 8601 Extended Format](https://fr.wikipedia.org/wiki/ISO_8601)    |
+-------------------------+----------------------------------------------------------------------------------------------------------+

Available actions to interact with a manifestation
--------------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | List available customers                     |
+------------------+----------------------------------------------+
| Show             | Getting a single customer                    |
+------------------+----------------------------------------------+
| Create           | Create a customer                            |
+------------------+----------------------------------------------+
| Update           | Update a customer                            |
+------------------+----------------------------------------------+
| Delete           | Delete a customer                            |
+------------------+----------------------------------------------+

Creating a Customer
-------------------

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/customers

+--------------------------+----------------+-----------------------------------------------------+
| Parameter                | Parameter type | Description                                         |
+==========================+================+=====================================================+
| Authorization            | header         | Token received during authentication                |
+--------------------------+----------------+-----------------------------------------------------+
| email                    | request        | Customer's email **required**                       |
+--------------------------+----------------+-----------------------------------------------------+
| firstName                | request        | Customer's first name                               |
+--------------------------+----------------+-----------------------------------------------------+
| lastName                 | request        | Customer's last name                                |
+--------------------------+----------------+-----------------------------------------------------+
| address                  | request        | Customers postal address                            |
+--------------------------+----------------+-----------------------------------------------------+
| zip                      | request        | Customers ZIP                                       |
+--------------------------+----------------+-----------------------------------------------------+
| city                     | request        | Customers city                                      |
+--------------------------+----------------+-----------------------------------------------------+
| country                  | request        | Customers country                                   |
+--------------------------+----------------+-----------------------------------------------------+
| phoneNumber              | request        | Customers phone number                              |
+--------------------------+----------------+-----------------------------------------------------+
| subscribedToNewsletter   | request        | Empty if not subscribed, else fulfilled by anything |
+--------------------------+----------------+-----------------------------------------------------+
| password                 | request        | Customers new password                              |
+--------------------------+----------------+-----------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers \
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

    $ curl http://e-venement.local/api/v2/customers \
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

    $ curl http://e-venement.local/api/v2/customers/94 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json" \
        -X GET \

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

  {
    "id": 94,
    "email": "laurent.martin@yahoo.fr",
    "firstName": "Laurent",
    "lastName": "Martin",
    "shortName": "Coco",
    "address": "Lieu-dit kerfinous",
    "zip": "29970",
    "city": "TREGOUREZ",
    "country": "FRANCE",
    "phoneNumber": "0645877344",
    "datesOfBirth": null,
    "locale": "fr",
    "uid": null,
    "subscribedToNewsletter": true
  }

Collection of Customers
-----------------------

You can retrieve the full customers list by making the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/customers

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

    $ curl http://e-venement.local/api/v2/customers \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"
        -X GET \

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json
   
   {
    "page": 1,
    "limit": 10,
    "pages": 1,
    "total": 1,
    "_links": {
        "self": {
            "href": "\/api\/v2\/customers?limit=10"
        },
        "first": {
            "href": "\/api\/v2\/customers?limit=10&page=1"
        },
        "last": {
            "href": "\/api\/v2\/customers?limit=10&page=1"
        },
        "next": {
            "href": "\/api\/v2\/customers?limit=10&page=1"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 94,
                "email": "laurent.martin@yahoo.fr",
                "firstName": "Laurent",
                "lastName": "Martin",
                "shortName": "Coco",
                "address": "Lieu-dit kerfinous",
                "zip": "29970",
                "city": "TREGOUREZ",
                "country": "FRANCE",
                "phoneNumber": "0645877344",
                "datesOfBirth": null,
                "locale": "fr",
                "uid": null,
                "subscribedToNewsletter": true
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

+--------------------------+----------------+-----------------------------------------------------+
| Parameter                | Parameter type | Description                                         |
+==========================+================+=====================================================+
| Authorization            | header         | Token received during authentication                |
+--------------------------+----------------+-----------------------------------------------------+
| id                       | url attribute  | Id of the requested resource                        |
+--------------------------+----------------+-----------------------------------------------------+
| email                    | request        | Customer's email **required**                       |
+--------------------------+----------------+-----------------------------------------------------+
| firstName                | request        | Customer's first name                               |
+--------------------------+----------------+-----------------------------------------------------+
| lastName                 | request        | Customer's last name                                |
+--------------------------+----------------------------------------------------------------------+
| address                  | request        | Customers postal address                            |
+--------------------------+----------------------------------------------------------------------+
| zip                      | request        | Customers ZIP                                       |
+--------------------------+----------------------------------------------------------------------+
| city                     | request        | Customers city                                      |
+--------------------------+----------------------------------------------------------------------+
| country                  | request        | Customers country                                   |
+--------------------------+----------------------------------------------------------------------+
| phoneNumber              | request        | Customers phone number                              |
+--------------------------+----------------------------------------------------------------------+
| subscribedToNewsletter   | request        | Empty if not subscribed, else fulfilled by anything |
+--------------------------+----------------------------------------------------------------------+
| password                 | request        | Customers new password                              |
+--------------------------+----------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/customers/94 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "lastName": "Martin",
                "address": "Lieu-dit kerfinous",
                "password": "secret"
           }'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

   {
    "id": 94,
    "email": "laurent.martin@yahoo.fr",
    "firstName": "Laurent",
    "lastName": "Martin",
    "shortName": "Coco",
    "address": "Lieu-dit kerfinous",
    "zip": "29970",
    "city": "TREGOUREZ",
    "country": "FRANCE",
    "phoneNumber": "0645877344",
    "datesOfBirth": null,
    "locale": "fr",
    "uid": null,
    "subscribedToNewsletter": true
  }


Deleting a Customer *Optional*
------------------------------

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

    GET /api/v2/customers/{id}/orders

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

    $ curl http://e-venement.local/api/v2/customers/7/orders \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"
        -X GET \

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
                    "currencyCode":"978",
                    "localeCode":"en_US",
                    "checkoutState":"completed"
                }
            ]
        }
    }
