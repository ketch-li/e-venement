Cart API
================

These endpoints will allow you to manage transactions and transaction items. Base URI is ``/api/v2/carts/``.

.. note::

    Remember that a **Transaction** in e-venement is called here a **Cart** that becomes an **Order** as soon
    as it is validated, probably through a payment receipt.

Cart API response structure
----------------------------

If you request a cart via API, you will receive an object with the following fields:

+-------------------+----------------------------------------------------------------------------------------------+
| Field             | Description                                                                                  |
+===================+==============================================================================================+
| id                | Id of the transaction                                                                        |
+-------------------+----------------------------------------------------------------------------------------------+
| items             | List of items in the cart                                                                    |
+-------------------+----------------------------------------------------------------------------------------------+
| itemsTotal        | Sum of all items prices                                                                      |
+-------------------+----------------------------------------------------------------------------------------------+
| adjustments       | List of adjustments related to the cart                                                      |
+-------------------+----------------------------------------------------------------------------------------------+
| adjustmentsTotal  | Sum of all cart adjustments values                                                           |
+-------------------+----------------------------------------------------------------------------------------------+
| total             | Sum of items total and adjustments total                                                     |
+-------------------+----------------------------------------------------------------------------------------------+
| customer          | :doc:`The customer object serialized with the default data </api/customers>` for transaction |
+-------------------+----------------------------------------------------------------------------------------------+
| currencyCode      | Currency of the cart                                                                         |
+-------------------+----------------------------------------------------------------------------------------------+
| localeCode        | Locale of the cart (by default, the locale set in the API)                                   |
+-------------------+----------------------------------------------------------------------------------------------+
| checkoutState     | State of the checkout process of the cart (cart -> new -> fulfilled or cancelled             |
+-------------------+----------------------------------------------------------------------------------------------+

CartItem API response structure
-------------------------------

Each CartItem in an API response will be build as follows:

+-------------------+--------------------------------------------------------------------------------------------+
| Field             | Description                                                                                |
+===================+============================================================================================+
| id                | Id of the cart item                                                                        |
+-------------------+--------------------------------------------------------------------------------------------+
| type              | Type of item (can be ticket, product or pass)                                              |
+-------------------+--------------------------------------------------------------------------------------------+
| quantity          | Quantity of item units                                                                     |
+-------------------+--------------------------------------------------------------------------------------------+
| declination       | Item family declination                                                                    |
+-------------------+--------------------------------------------------------------------------------------------+
| unitPrice         | Price of each item unit                                                                    |
+-------------------+--------------------------------------------------------------------------------------------+
| total             | Sum of units total and adjustments total of that cart item                                 |
+-------------------+--------------------------------------------------------------------------------------------+
| vat               | The VAT specific value of this item                                                        |
+-------------------+--------------------------------------------------------------------------------------------+
| units             | A collection of units related to the cart item                                             |
+-------------------+--------------------------------------------------------------------------------------------+
| unitsTotal        | Sum of all unit prices of the cart item                                                    |
+-------------------+--------------------------------------------------------------------------------------------+
| adjustments       | List of adjustments related to the cart item                                               |
+-------------------+--------------------------------------------------------------------------------------------+
| adjustmentsTotal  | Sum of all item adjustments related to that cart item                                      |
+-------------------+--------------------------------------------------------------------------------------------+
| _link[product]    | Relative link to product                                                                   |
+-------------------+--------------------------------------------------------------------------------------------+
| _link[order]      | Relative link to order                                                                     |
+-------------------+--------------------------------------------------------------------------------------------+
| rank              | Rank of item in the cart (*optional*, can be null)                                         |
+-------------------+--------------------------------------------------------------------------------------------+
| state             | State of the item (*optional*, can be null or a string depending on business logic         |
+-------------------+--------------------------------------------------------------------------------------------+

CartItemUnit API response structure
-----------------------------------

Each CartItemUnit API response will be build as follows:

+-------------------+-----------------------------------------------+
| Field             | Description                                   |
+===================+===============================================+
| id                | Id of the cart item unit                      |
+-------------------+-----------------------------------------------+
| adjustments       | List of adjustments related to the unit       |
+-------------------+-----------------------------------------------+
| adjustmentsTotal  | Sum of all units adjustments of the unit      |
+-------------------+-----------------------------------------------+
| _link[pdf]        | *(optional)* URL of a PDF version of the item |
+-------------------+-----------------------------------------------+


Adjustment API response structure
---------------------------------

And each Adjustment will be build as follows:

+--------+----------------------------------------------------------+
| Field  | Description                                              |
+========+==========================================================+
| id     | Id of the adjustment                                     |
+--------+----------------------------------------------------------+
| type   | Type of the adjustment (E.g. *order_promotion* or *tax*) |
+--------+----------------------------------------------------------+
| label  | Label of the adjustment                                  |
+--------+----------------------------------------------------------+
| amount | Amount of the adjustment (value)                         |
+--------+----------------------------------------------------------+

.. note::

    An Adjustment can be VAT, shipping fees, promotion, etc.
    
Creating a cart
-----------------------

To create a new cart you will need to call the ``/api/v2/carts/`` endpoint with the ``POST`` method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/carts

+---------------+----------------+----------------------------------------------------------+
| Parameter     | Parameter type | Description                                              |
+===============+================+==========================================================+
| Authorization | header         | Token received during authentication                     |
+---------------+----------------+----------------------------------------------------------+
| localeCode    | request        | Code of the locale in which the cart should be created   |
+---------------+----------------+----------------------------------------------------------+

Example
^^^^^^^

To create a new cart for the ``shop@example.com`` user with the ``en_US`` locale use the below method:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "localeCode": "en_US"
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 201 Created

.. code-block:: json

    {
        "id":21,
        "items":[

        ],
        "itemsTotal":0,
        "adjustments":[

        ],
        "adjustmentsTotal":0,
        "total":0,
        "customer":{},
        "_links":{},
        "currencyCode":"978",
        "localeCode":"en_US",
        "checkoutState":"cart"
    }

.. note::

    A currency code will be added automatically based on the application settings.

.. warning::

    If you try to create a resource without localeCode, you will receive a ``400 Bad Request`` error, that will contain validation errors.

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 400 Bad Request

.. code-block:: json

    {
        "code":400,
        "message":"Validation Failed",
        "errors":{
            "children":{
                "localeCode":{
                    "errors":[
                        "This value should not be blank."
                    ]
                },
            }
        }
    }

Collection of Carts
-------------------

To retrieve a paginated list of carts you will need to call the ``/api/v2/carts/`` endpoint with the ``GET`` method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/carts

+---------------+----------------+-----------------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                                 |
+===============+================+=============================================================================+
| Authorization | header         | Token received during authentication                                        |
+---------------+----------------+-----------------------------------------------------------------------------+
| page          | query          | *(optional)* Number of the page, by default = 1                             |
+---------------+----------------+-----------------------------------------------------------------------------+
| paginate      | query          | *(optional)* Number of carts displayed per page, by default = 10, max = 100 |
+---------------+----------------+-----------------------------------------------------------------------------+

Example
^^^^^^^

To see the first page of the paginated carts collection use the below method:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

   {
    "page": 1,
    "limit": 10,
    "pages": 23,
    "total": 222,
    "_links": {
        "self": {
            "href": "\/tck.php\/api\/v2\/carts?limit=10"
        },
        "first": {
            "href": "\/tck.php\/api\/v2\/carts?limit=10&page=1"
        },
        "last": {
            "href": "\/tck.php\/api\/v2\/carts?limit=10&page=23"
        },
        "next": {
            "href": "\/tck.php\/api\/v2\/carts?limit=10&page=2"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 963,
                "checkoutState": "cart",
                "customer": {},
                "items": [],
                "itemsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0,
                "currencyCode": 978
            }
        ]
     }
  }


Getting a Single Cart
---------------------

To retrieve details of the cart you will need to call the ``/api/v2/carts/{id}`` endpoint with ``GET`` method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/carts/{id}

+---------------+----------------+--------------------------------------+
| Parameter     | Parameter type | Description                          |
+===============+================+======================================+
| Authorization | header         | Token received during authentication |
+---------------+----------------+--------------------------------------+
| id            | url attribute  | Id of the requested cart             |
+---------------+----------------+--------------------------------------+

Example
^^^^^^^

To see details of the cart with ``id = 822`` use the below method:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts/822 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"

.. note::

    The *822* value was taken from the previous create response. Your value can be different.
    Check in the list of all carts if you are not sure which id should be used.

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

      [
    {
        "id": 822,
        "checkoutState": "cart",
        "customer": {
            "id": 74,
            "email": "zamou45@yahoo.fr",
            "firstName": "Bob",
            "lastName": "Zamou",
            "shortName": "Coco",
            "address": "36 rue Bobo",
            "zip": "29970",
            "city": "Bordeaux",
            "country": "FRANCE",
            "phoneNumber": "0645877344",
            "datesOfBirth": null,
            "locale": "fr",
            "uid": null,
            "subscribedToNewsletter": true
        },
        "items": [
            {
                "id": 538,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 9,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 707,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 13,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 708,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 6,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 709,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 15,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 710,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 11,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            }
        ],
        "itemsTotal": 0,
        "adjustments": [],
        "adjustmentsTotal": 0,
        "total": 0,
        "currencyCode": 978
    }
]


Deleting a Cart
---------------

A cart cannot be deleted. It simply has to be abandonned if needed.

Creating a Cart Item
--------------------

To add a new cart item to an existing cart you will need to call the ``/api/v2/carts/{cartId}/items`` endpoint with ``POST`` method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/carts/{cartId}/items

+---------------+----------------+---------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                         |
+===============+================+=====================================================================+
| Authorization | header         | Token received during authentication                                |
+---------------+----------------+---------------------------------------------------------------------+
| cartId        | url attribute  | Id of the requested cart                                            |
+---------------+----------------+---------------------------------------------------------------------+
| declinationId | request        | Code of the item you want to add to the cart                        |
+---------------+----------------+---------------------------------------------------------------------+
| type          | request        | Type of item to add (can be ticket, product or pass)                |
+---------------+----------------+---------------------------------------------------------------------+
| quantity      | request        | Amount of variants you want to add to the cart (cannot be < 1)      |
+---------------+----------------+---------------------------------------------------------------------+
| priceId       | request        | Price aimed for the item                                            |
+---------------+----------------+---------------------------------------------------------------------+
| numerotations | request        | An array of specific items of the requested declinations (optional) |
+---------------+----------------+---------------------------------------------------------------------+

Example
^^^^^^^

To add a new item of a product to the cart with id = 822 (assuming, that we didn't remove it in the
previous example) use the below method:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts/822/items \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "type": "ticket",
                "declinationId": itemId,
                "quantity": 1,
                "priceId": priceId
          }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 201 Created

.. code-block:: json

   {
    "id": 711,
    "unitPrice": "0.00",
    "rank": 1,
    "state": "none",
    "type": "ticket",
    "quantity": 1,
    "declination": {
        "id": 14,
        "code": "TODO",
        "position": "TODO",
        "translations": "TODO"
    },
    "units": [
        {
            "id": "XXX",
            "adjustments": [],
            "adjustmentsTotal": 0
        }
    ],
    "unitsTotal": 0,
    "adjustments": [],
    "adjustmentsTotal": 0,
    "total": 0
  }

.. tip::

Updating a Cart Item
--------------------

To change the quantity of a cart item you will need to call the ``/api/v1/carts/{cartId}/items/{cartItemId}`` endpoint with the ``POST``  method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/carts/{cartId}/items/{cartItemId}

+---------------+----------------+---------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                         |
+===============+================+=====================================================================+
| Authorization | header         | Token received during authentication                                |
+---------------+----------------+---------------------------------------------------------------------+
| cartId        | url attribute  | Id of the requested cart                                            |
+---------------+----------------+---------------------------------------------------------------------+
| declinationId | url attribute  | Id of the requested declination                                     |
+---------------+----------------+---------------------------------------------------------------------+
| quantity      | request        | Amount of items you want to have in the cart (cannot be < 1)        |
+---------------+----------------+---------------------------------------------------------------------+
| numerotations | request        | An array of specific items of the requested declinations (optional) |
+---------------+----------------+---------------------------------------------------------------------+

Example
^^^^^^^

To change the rank of the cart item with ``id = 710`` in the cart of ``id = 822`` to 3 use the below method:


.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts/822/items/710 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '{"rank": 3}'

.. tip::

    If you are not sure where does the value **710** come from, check the previous response, and look for the cart item id.


Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK
    
.. code-block:: json

   {
    "code": 200,
    "message": "Update successful"
   }

Now we can check how does the cart look like after changing the rank of a cart item.

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts/822 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

      [
    {
        "id": 822,
        "checkoutState": "cart",
        "customer": {
            "id": 74,
            "email": "zamou45@yahoo.fr",
            "firstName": "Bob",
            "lastName": "Zamou",
            "shortName": "Coco",
            "address": "36 rue Bobo",
            "zip": "29970",
            "city": "Bordeaux",
            "country": "FRANCE",
            "phoneNumber": "0645877344",
            "datesOfBirth": null,
            "locale": "fr",
            "uid": null,
            "subscribedToNewsletter": true
        },
        "items": [
            {
                "id": 710,
                "unitPrice": "0.00",
                "rank": 3,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 11,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 712,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 14,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 709,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 15,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 708,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 6,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 707,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 13,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            },
            {
                "id": 538,
                "unitPrice": "0.00",
                "rank": 1,
                "state": "none",
                "type": "ticket",
                "quantity": 1,
                "declination": {
                    "id": 9,
                    "code": "TODO",
                    "position": "TODO",
                    "translations": "TODO"
                },
                "units": [
                    {
                        "id": "XXX",
                        "adjustments": [],
                        "adjustmentsTotal": 0
                    }
                ],
                "unitsTotal": 0,
                "adjustments": [],
                "adjustmentsTotal": 0,
                "total": 0
            }
        ],
        "itemsTotal": 0,
        "adjustments": [],
        "adjustmentsTotal": 0,
        "total": 0,
        "currencyCode": 978
    }
]


.. tip::

    In this response you can see that promotion and shipping have been taken into account to calculate the appropriate price.

Deleting a Cart Item
--------------------

To delete a cart item from a cart you will need to call the ``/api/v2/carts/{cartId}/items/{cartItemId}`` endpoint with the ``DELETE`` method.

Definition
^^^^^^^^^^

To delete the cart item with ``id = 58`` from the cart with ``id = 21`` use the below method:

.. code-block:: text

    DELETE /api/v2/carts/{cartId}/items/{cartItemId}

+---------------+----------------+--------------------------------------+
| Parameter     | Parameter type | Description                          |
+===============+================+======================================+
| Authorization | header         | Token received during authentication |
+---------------+----------------+--------------------------------------+
| cartId        | url attribute  | Id of the requested cart             |
+---------------+----------------+--------------------------------------+
| cartItemId    | url attribute  | Id of the requested cart item        |
+---------------+----------------+--------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts/21/items/58 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json" \
        -X DELETE

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK
    
.. code-block:: json

   {
    "code": 200,
    "message": "Delete successful"
   }


Reordering Cart Items
---------------------

To reorder cart items you can call the ``/api/v2/carts/{cartId}/items/reorder`` endpoint with the ``POST`` method.
All the cart items you are reordering must belong to the same time slot. This feature is optional and can be unavailable, depending on business logic.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/carts/{cartId}/items/reorder

+---------------+----------------+----------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                    |
+===============+================+================================================================+
| Authorization | header         | Token received during authentication                           |
+---------------+----------------+----------------------------------------------------------------+
| cartId        | url attribute  | Id of the requested cart                                       |
+---------------+----------------+----------------------------------------------------------------+

Example
^^^^^^^

To reorder cart items 465, 466, 467 in cart id = 21 use the below method:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/carts/21/items/reorder \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            [
                {
                    "cartItemId": 465,
                    "rank": 1
                },
                {
                    "cartItemId": 466,
                    "rank": 3
                },	
                {
                    "cartItemId": 467,
                    "rank": 2
                }                
            ]
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK
    
.. code-block:: json

   {
    "code": 200,
    "message": "Update successful"
   }
