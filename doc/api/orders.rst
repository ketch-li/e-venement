Orders API
==========

These endpoints will allow you to manage orders (when a checkout is confirmed). Base URI is '/api/v2/orders'.

Product API response structure
------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+----------------------------------------------+
| Field            | Description                                  |
+==================+==============================================+
| id               | Id of order                                  |
+------------------+----------------------------------------------+
| itemsTotal       | Sum of all item prices                       |
+------------------+----------------------------------------------+
| adjustmentsTotal | Sum of all order adjustments                 |
+------------------+----------------------------------------------+
| total            | Sum of all items total and adjustments total |
+------------------+----------------------------------------------+
| state            | State of the order                           |
+------------------+----------------------------------------------+
| currencyCode     | Currency of the order                        |
+------------------+----------------------------------------------+
| localeCode       |                                              |
+------------------+----------------------------------------------+
| checkoutState    | State of the checkout process                |
+------------------+----------------------------------------------+

If you request for a more detailed data, you will receive an object with following fields:

+------------------+------------------------------------------------------+
| Field            | Description                                          |
+==================+======================================================+
| id               | Id of order                                          |
+------------------+------------------------------------------------------+
| customer         | Customer detail serialization                        |
+------------------+------------------------------------------------------+
| items            | :doc:`List of items related to the order</api/carts>`|
+------------------+------------------------------------------------------+
| itemsTotal       | Sum of all item prices                               |
+------------------+------------------------------------------------------+
| adjustments      | List of adjustments related to the order             |
+------------------+------------------------------------------------------+
| adjustmentsTotal | Sum of all order adjustments                         |
+------------------+------------------------------------------------------+
| total            | Sum of all items total and adjustments total         |
+------------------+------------------------------------------------------+
| state            | State of the order                                   |
+------------------+------------------------------------------------------+
| payments         | Detailed serialization of payments                   |
+------------------+------------------------------------------------------+
| currencyCode     | Currency of the order                                |
+------------------+------------------------------------------------------+
| localeCode       |                                                      |
+------------------+------------------------------------------------------+
| checkoutState    | State of the checkout process                        |
+------------------+------------------------------------------------------+

Available actions to interact with a product
--------------------------------------------

Orders endpoint gives an access point to finalized carts, so to the orders that have been placed. At this moment only certain actions are allowed:

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Orders for a specified customer              |
+------------------+----------------------------------------------+
| Show             | Presenting of the order                      |
+------------------+----------------------------------------------+
| Cancel           | Cancelling of the order                      |
+------------------+----------------------------------------------+
| Ship             | Shipping of the order                        |
+------------------+----------------------------------------------+
| Complete         | Complete the order's payment                 |
+------------------+----------------------------------------------+

List Action
-----------

You can retrieve the full orders list or request a list of orders from a customer:

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/orders
    
+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| customerId    | request        | *(optional)* Id of the customer                                   |
+---------------+----------------+-------------------------------------------------------------------+
| page          | query          | *(optional)* Number of the page, by default = 1                   |
+---------------+----------------+-------------------------------------------------------------------+
| limit         | query          | *(optional)* Number of items to display per page, by default = 10 |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

To retrieve the orders of the customer 214, the following snippet can be used:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/orders \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "customerId": 214
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 Ok

.. code-block:: json

    {
        "page":1,
        "limit":10,
        "pages":21,
        "total":205,
        "_links":{
            "self":{
                 "href":"\/api\/v2\/orders\/?page=1&limit=10"
            },
            "first":{
                 "href":"\/api\/v2\/orders\/?page=1&limit=10"
            },
            "last":{
                 "href":"\/api\/v2\/orders\/?page=21&limit=10"
            },
            "next":{
                 "href":"\/api\/v2\/orders\/?page=2&limit=10"
            }
        },
        "_embedded":{
            "items":[
                {
                    "id":21,
                    "type":"ticket",
                    "itemsTotal":100000,
                    "adjustmentsTotal":8787,
                    "total":108787,
                    "state":"fulfilled",
                    "currencyCode":"978",
                    "localeCode":"en_US",
                    "checkoutState":"completed"
                },
                {
                    "id":22,
                    "type":"ticket",
                    "itemsTotal":100000,
                    "adjustmentsTotal":5656,
                    "total":105656,
                    "state":"cancelled",
                    "currencyCode":"978",
                    "localeCode":"en_US",
                    "checkoutState":"addressed"
                }
            ]
        }
    }


Show Action
-----------

You can request detailed order information by executing the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/orders/{id}

+------------------------------+----------------+---------------------------------------+
| Parameter                    | Parameter type | Description                           |
+==============================+================+=======================================+
| Authorization                | header         | Token received during authentication  |
+------------------------------+----------------+---------------------------------------+
| id                           | url attribute  | Id of the requested resource          |
+------------------------------+----------------+---------------------------------------+

Example
^^^^^^^

To retrieve the order 21, the following snippet can be used:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/orders/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \


Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 Ok

.. code-block:: json

    {
        "id":21,
        "items":[
            {
                "id":74,
                "type":"ticket",
                "quantity":1,
                "unitPrice":100000,
                "total":100000,
                "units":[
                    {
                        "id":228,
                        "adjustments":[
                        ],
                        "adjustmentsTotal":0
                    }
                ],
                "unitsTotal":100000,
                "adjustments":[
                ],
                "adjustmentsTotal":0,
                "declination":{
                    "id":331,
                    "code":"3156844564",
                    "position":2,
                    "translations":{
                        "en_US":{
                            "id":331,
                            "name":"Medium Mug"
                        }
                    },
                    "onHold":0,
                    "onHand":10,
                    "tracked":true,
                },
                "_links":{
                    "product":{
                        "href":"\/api\/v2\/products\/5"
                    },
                    "declination":{
                        "href":"\/api\/v2\/products\/5\/declinations\/331"
                    }
                }
            }
        ],
        "itemsTotal":100000,
        "adjustments":[
            {
                "id":249,
                "type":"shipping",
                "label":"UPS",
                "amount":8787
            }
        ],
        "adjustmentsTotal":8787,
        "total":108787,
        "state":"cart",
        "customer":{
            "id":1,
            "email":"shop@example.com",
            "firstName":"John",
            "lastName":"Doe",
            "_links":{
                "self":{
                    "href":"\/api\/v2\/customers\/1"
                }
            }
        },
        "payments":[
            {
                "id":21,
                "method":{
                    "id":1,
                    "code":"cash_on_delivery"
                },
                "amount":108787,
                "state":"cart"
            }
        ],
        "currencyCode":"978",
        "localeCode":"en_US",
        "checkoutState":"addressed"
    }


Cancel Action
-------------

You can cancel an already placed order by executing the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/orders/cancel/{id}
    
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+
| Parameter                    | Parameter type | Description                                                                                         |
+==============================+================+=====================================================================================================+
| Authorization                | header         | Token received during authentication                                                                |
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+
| id                           | url attribute  | Id of the requested resource                                                               |
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+

Example
^^^^^^^

To cancel the order 21, the following snippet can be used:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/orders/cancel/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \


Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 No Content


Ship Action
-----------

You can ship an already placed order by executing the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/orders/ship/{id}
    
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+
| Parameter                    | Parameter type | Description                                                                                         |
+==============================+================+=====================================================================================================+
| Authorization                | header         | Token received during authentication                                                                |
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+
| id                           | url attribute  | Id of the requested resource                                                               |
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+

Example
^^^^^^^

To ship the order 21, the following snippet can be used:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/orders/ship/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \


Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 No Content

Complete Action
---------------

You can complete the payment of an already placed order by executing the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/orders/complete/{id}
    
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+
| Parameter                    | Parameter type | Description                                                                                         |
+==============================+================+=====================================================================================================+
| Authorization                | header         | Token received during authentication                                                                |
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+
| id                           | url attribute  | Id of the requested resource                                                               |
+------------------------------+----------------+-----------------------------------------------------------------------------------------------------+

Example
^^^^^^^

To complete the order 21, the following snippet can be used:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/orders/complete/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \


Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 No Content



