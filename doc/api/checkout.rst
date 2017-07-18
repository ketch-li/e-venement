Checkout API
============

These endpoints will allow you to go through the order checkout. It can be useful for integrations with tools like `Twillo <https://www.twilio.com/docs/>`_ or an inspiration for your custom Shop API.
Base URI is `/api/v2/checkouts/`.

After you create a cart (an empty order) and add some items to it, you can start the checkout via API.
This basically means updating the order with concrete information, step by step, in a correct order.

e-venement checkout flow is built from 3 steps, which have to be done in a certain order :

+------------+---------------------------------------------------------+
| Step       | Description                                             |
+============+=========================================================+
| addressing | A customer is assigned to the cart                      |
+------------+---------------------------------------------------------+
| payment    | Choosing a payment method from the available ones       |
+------------+---------------------------------------------------------+
| finalize   | The order is built and its data can be confirmed        |
+------------+---------------------------------------------------------+

.. note::

    We do not present the order serialization in this chapter, because it is the same order serialization as described in :doc:`the article about orders </api/orders>`.

Addressing step
---------------

After you added some items to the cart, to start the checkout you simply need to authentify as a customer to get
back needed informations about where to "ship" your order. Refer to :doc:`the article about the login process </api/login>`.

Once logged in, you can check the state of the order, by asking for the checkout summary:

DEFINITION
^^^^^^^^^^

.. code-block:: text

  GET /api/v2/checkouts/21

Example
^^^^^^^

To check the checkout process state for the cart with `id = 21`, we need to execute this command:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/checkouts/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"

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
                    "variant":{
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

Payment step
------------

When we are done with addressing and we know the final price of an order, we can select a payment method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/checkouts/select-payment/{id}

+---------------+----------------+--------------------------------------+
| Parameter     | Parameter type | Description                          |
+===============+================+======================================+
| Authorization | header         | Token received during authentication |
+---------------+----------------+--------------------------------------+
| id            | url attribute  | Id of the requested cart             |
+---------------+----------------+--------------------------------------+

Example
^^^^^^^

To check available payment methods for the cart that has a shipping methods assigned, we need to execute this curl command:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/checkouts/select-payment/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json"

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "methods": [
            {
                "id": 1,
                "name": "En Ligne",
                "account": ""
            },
            {
                "id": 2,
                "name": "Carte Abo",
                "account": ""
            }
        ]
    }

With that information, another ``POST`` request with the id of payment method is enough to proceed:

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/checkouts/select-payment/{id}

+----------------------+----------------+--------------------------------------+
| Parameter            | Parameter type | Description                          |
+======================+================+======================================+
| Authorization        | header         | Token received during authentication |
+----------------------+----------------+--------------------------------------+
| id                   | url attribute  | Id of the requested cart             |
+----------------------+----------------+--------------------------------------+
| payment_method_id    | request        | Id of chosen payment method          |
+----------------------+----------------+--------------------------------------+

Example
^^^^^^^

To choose the ``Bank transfer`` method for our shipment, simply use the following code:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/checkouts/select-payment/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "payment_method_id": 1
            }
        '

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 Success

.. code-block:: json

    {
        "url": "http://www.paybox.com/pay/",
        "method": "GET",
        "arguments": {
            "PBX_SITE": "http://e-venement.local/",
            "PBX_IDENTIFIANT": "123123123",
            "PBX_HASH": "abcdefghijklmn",
            "PBX_TOTAL": 100,
            "PBX_REPONDRE_A": "http://e-venement.local/api/v2/checkouts/complete/21"
        }
    }

Final step
-----------

After choosing the payment method we are ready to finalize the cart and make an order. Now, you can get its snapshot by calling a ``GET`` request:

.. tip::

    The same definition has been used over this chapter, to see the current state of the order.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/checkouts/{id}

+---------------+----------------+--------------------------------------+
| Parameter     | Parameter type | Description                          |
+===============+================+======================================+
| Authorization | header         | Token received during authentication |
+---------------+----------------+--------------------------------------+
| id            | url attribute  | Id of the requested cart             |
+---------------+----------------+--------------------------------------+

Example
^^^^^^^

To check the fully constructed cart with `id = 21`, use the following command:

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/checkouts/21 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json"

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "id":21,
        "items":[
            {
                "id":74,
                "type":"tickets",
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
                    "code":"MEDIUM_MUG_CUP",
                    "optionValues":[
                        {
                            "code":"mug_type_medium"
                        }
                    ],
                    "position":2,
                    "translations":{
                        "en_US":{
                        }
                    },
                    "on_hold":0,
                    "on_hand":10,
                    "tracked":false,
                    "channelPricings":{
                        "US_WEB":{
                            "channelCode":"US_WEB",
                            "price":100000
                        }
                    }
                },
                "_links":{
                    "product":{
                        "href":"\/api\/v1\/products\/5"
                    },
                    "variant":{
                        "href":"\/api\/v1\/products\/5\/declinations\/331"
                    }
                }
            }
        ],
        "itemsTotal":100000,
        "adjustments":[
            {
                "id":252,
                "type":"shipping",
                "label":"DHL Express",
                "amount":3549
            }
        ],
        "adjustmentsTotal":3549,
        "total":103549,
        "state":"cart",
        "customer":{
            "id":1,
            "email":"shop@example.com",
            "firstName":"John",
            "lastName":"Doe",
            "gender":"u"
            },
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
                    "id":2,
                    "code":"bank_transfer"
                },
                "amount":103549,
                "state":"cart"
            }
        ],
        "shipments":[
            {
                "id":21,
                "state":"cart",
                "method":{
                    "code":"dhl_express",
                    "enabled":true
                }
            }
        ],
        "currencyCode":"978",
        "localeCode":"en_US",
        "checkoutState":"payment_selected"
    }

This is how your final order will look like. If you are satisfied with that response, simply call another request as defined in the ``select-payment`` POST call to follow with the bank to confirm the checkout, which will (according to the details given for payment) transform the current cart into a real order that will appear in the backend.

Definition
^^^^^^^^^^

Example:

.. code-block:: text

    POST http://www.paybox.com/pay/

+---------------+----------------+---------------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                               |
+===============+================+===========================================================================+
| Authorization | header         | Token received during authentication                                      |
+---------------+----------------+---------------------------------------------------------------------------+
| id            | url attribute  | Id of the requested cart                                                  |
+---------------+----------------+---------------------------------------------------------------------------+
| arguments     | request        | Batch of arguments given in the select-payment POST response URL encoded  |
+---------------+----------------+---------------------------------------------------------------------------+

Example
^^^^^^^

To finalize the previously built order, execute the following URL in a browser, as usual clients should do:

Note: this is an example using GET requests, it usually can be required to use POST...

.. code-block:: bash

    $ firefox http://www.paybox.com/pay?PBX_SITE=http%3A%2F%2Fe-venement.local%2F&PBX_IDENTIFIANT=123123123&PBX_HASH=abcdefghijklmn&PBX_TOTAL=100&PBX_REPONDRE_A=http%3A%2F%2Fe-venement.local%2Fapi%2Fv2%2Fcheckouts%2Fcomplete%2F21"

At the end, if payment is successful, the bank system should have call the ``/api/v2/checkouts/complete/21`` URI with expected arguments. This call should have transformed the cart into an order, and created a new cart attached to the current customer. Then the previous order can be found back in the list of available carts calling ``/api/v2/carts`` while the customer is still known by the system.
