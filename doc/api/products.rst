Products API
============

These endpoints will allow you to manage products. Base URI is '/api/v2/products'.

Products API response structure
--------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+----------------------------------------------+
| Field            | Description                                  |
+==================+==============================================+
| id               | Id of the product                            |
+------------------+----------------------------------------------+
| category         | Category of the product                      |
+------------------+----------------------------------------------+
| translations     | Collection of translations                   |
+------------------+----------------------------------------------+
| prices           | Collection of Prices                         |
+------------------+----------------------------------------------+
| imageURL         | Image (URI) of the product                   |
+------------------+----------------------------------------------+
| declinations     | Collection of the product declinations       |
+------------------+----------------------------------------------+

Declination API response structure
-----------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+--------------------------------------------------------------------------+
| Field            | Description                                                              |
+==================+==========================================================================+
| id               | Id of the product declination                                            |
+------------------+--------------------------------------------------------------------------+
| code             | Code of the declination (can be a barcode)                               |
+------------------+--------------------------------------------------------------------------+
| weight           | Weight of the declination (in grams)                                     |
+------------------+--------------------------------------------------------------------------+
| availableUnits   | The available quantity of this declination                               |
|                  | To avoid information leaks, if more units are available than the maximum |
|                  | configured, the maximum is exposed instead of the really available stock |
+------------------+--------------------------------------------------------------------------+
| prices           | Collection of Prices                                                     |
+------------------+--------------------------------------------------------------------------+

Prices API response structure
------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+--------------------------------------------------------------------------+
| Field            | Description                                                              |
+==================+==========================================================================+
| id               | Id of the price                                                          |
+------------------+--------------------------------------------------------------------------+
| translations     | Collection of translations                                               |
+------------------+--------------------------------------------------------------------------+
| value            | Amount of the price in the current currency                              |
+------------------+--------------------------------------------------------------------------+
| currencyCode     | Currency of the cart                                                     |
+------------------+--------------------------------------------------------------------------+

Available actions to interact with a product
--------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| Show             | Getting a single product                     |
+------------------+----------------------------------------------+
| List             | Retrieve a collection of products            |
+------------------+----------------------------------------------+


Getting a single product
------------------------

To retrieve the details of a product you will need to call the /api/v2/products/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/products/{id}

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/products/123 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET \

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "id":123,
        "category":"cups-and-mugs",
        "translations":{
            "en_US":{
                "id":123,
                "name":"The VIm Mug",
                "description":"Perfect not only for your coffee, but also as tea or infusion mug."
            }
        },
        "declinations":[
            {
                "id":59,
                "code":99823300,
                "weight":650,
                "availableUnits":10,
                "translations":{
                    "en_US":{
                        "id":59,
                        "name":"The Black VIm Mug",
                        "description":"A great VIm Mug in black."
                    }
                },
                "prices":[
                    "id":4,
                    "translations:{
                        "en_US":{
                            "name":"Normal"
                        }
                    },
                    "value":12,
                    "currencyCode":"EUR",
                ]
            },
            {
                "id":60,
                "code":99823301,
                "weight":650,
                "availableUnits":8,
                "translations":{
                    "en_US":{
                        "id":59,
                        "name":"The Orange VIm Mug",
                        "description":"A great VIm Mug in orange."
                    }
                },
                "prices":[
                    "id":4,
                    "translations:{
                        "en_US":{
                            "name":"Normal"
                        }
                    },
                    "value":12,
                    "currencyCode":"EUR",
                ]
            }
        ],
        "imageURL":"vimmug.png",
        "price":15
    }


Collection of products
------------------------

To retrieve a collection of products you will need to call the /api/v2/products endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/products

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

    $ curl http://e-venement.local/api/v2/products \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET \

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
                    "id":123,
                    "category":"cups-and-mugs",
                    "translations":{
                        "en_US":{
                            "id":123,
                            "name":"The VIm Mug",
                            "description":"Perfect not only for your coffee, but also as tea or infusion mug."
                        }
                    },
                    "declinations":[
                        {
                            "id":59,
                            "code":99823300,
                            "weight":650,
                            "availableUnits":10,
                            "translations":{
                                "en_US":{
                                    "id":59,
                                    "name":"The Black VIm Mug",
                                    "description":"A great VIm Mug in black."
                                }
                            },
                            "prices":[
                                "id":4,
                                "translations:{
                                    "en_US":{
                                        "name":"Normal"
                                    }
                                },
                                "value":12,
                                "currencyCode":"EUR",
                            ]
                        }
                    ],
                    "imageURL":"vimmug.png",
                    "price":15
                },
                {
                    "id":124,
                    "category":"cups-and-mugs",
                    "translations":{
                        "en_US":{
                            "id":124,
                            "name":"Arch Linux Coffee Mug",
                            "description":"The Arch Linux Mug, an awesome ceramic mug printed on both sides with the Arch Linux logo."
                        }
                    },
                    "declinations":[
                        {
                            "id":66,
                            "code":99823312,
                            "weight":650,
                            "availableUnits":10,
                            "translations":{
                                "en_US":{
                                    "id":66,
                                    "name":"Arch Linux Coffee Mug",
                                    "description":"The Arch Linux Mug, an awesome ceramic mug printed on both sides with the Arch Linux logo."
                                }
                            },
                            "prices":[
                                "id":4,
                                "translations:{
                                    "en_US":{
                                        "name":"Normal"
                                    }
                                },
                                "value":12,
                                "currencyCode":"EUR",
                            ]
                        }
                    ],
                    "imageURL":"vimmug.png",
                    "price":15
                }
            ]
        }
    }
