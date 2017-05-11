Sorting and filtration API
==========================

A list of resources can be sorted and filtered by passed url query parameters. Here you can find examples how to do it with sample resources.

How to sort resources?
----------------------

Let’s assume that you want to sort products by code in descending order. In this case you should call the /api/v2/products/ endpoint with the GET method and provide sorting query parameters.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/products/?sorting\[nameOfField\]={direction}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| nameOfField   | query          | *(required)* Name of field by which the resource will be sorted   |
+---------------+----------------+-------------------------------------------------------------------+
| direction     | query          | *(required)* Define a direction of ordering                       |
+---------------+----------------+-------------------------------------------------------------------+
| limit         | query          | *(optional)* Number of items to display per page, by default = 10 |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/products/?sorting\[code\]=desc&limit=4 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 Ok

.. code-block:: json

    {
        "page": 1,
        "limit": 4,
        "pages": 1,
        "total": 2,
        "_links": {
            "self": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=1&limit=4"
            },
            "first": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=1&limit=4"
            },
            "last": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=15&limit=4"
            },
            "next": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=2&limit=4"
            }
        },
        "_embedded": {
            "items": [
                {
                    "id":123,
                    "category":"cups-and-mugs",
                    "translations": {
                        "en_US": {
                            "id":123,
                            "name":"The VIm Mug",
                            "description":"Perfect not only for your coffee, but also as tea or infusion mug."
                        }
                    },
                    "declinations": [
                        {
                            "id":59,
                            "code":99823300,
                            "weight":650,
                            "availableUnits":10,
                            "translations": {
                                "en_US": {
                                    "id":59,
                                    "name":"The Black VIm Mug",
                                    "description":"A great VIm Mug in black."
                                }
                            },
                            "prices": [
                                "id":4,
                                "translations: {
                                    "en_US": {
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
                    "translations": {
                        "en_US": {
                            "id":124,
                            "name":"Arch Linux Coffee Mug",
                            "description":"The Arch Linux Mug, an awesome ceramic mug printed on both sides with the Arch Linux logo."
                        }
                    },
                    "declinations": [
                        {
                            "id":66,
                            "code":99823312,
                            "weight":650,
                            "availableUnits":10,
                            "translations": {
                                "en_US": {
                                    "id":66,
                                    "name":"Arch Linux Coffee Mug",
                                    "description":"The Arch Linux Mug, an awesome ceramic mug printed on both sides with the Arch Linux logo."
                                }
                            },
                            "prices": [
                                {
                                    "id":4,
                                    "translations: {
                                        "en_US": {
                                            "name":"Normal"
                                        }
                                    },
                                    "value":12,
                                    "currencyCode":"EUR",
                                }
                            ]
                        }
                    ],
                    "imageURL":"vimmug.png",
                    "price":15
                }
            ]
        }
    }

How to filter resources?
----------------------

Let’s assume that you want to find all products which contain the word 'linux' in the name. In this case you should call the /api/v2/products/ endpoint with the GET method and provide filter query parameters.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/products/?criteria\[{nameOfCriterion}\]\[type\]={searchOption}&criteria\[{nameOfCriterion}\]\[value\]={searchPhrase}'

+-----------------+----------------+-------------------------------------------------------------------+
| Parameter       | Parameter type | Description                                                       |
+=================+================+===================================================================+
| Authorization   | header         | Token received during authentication                              |
+-----------------+----------------+-------------------------------------------------------------------+
| nameOfCriterion | query          | *(required)* The name of criterion (for example "search")         |
+-----------------+----------------+-------------------------------------------------------------------+
| searchPhrase    | query          | *(required)* The searching phrase                                 |
+-----------------+----------------+-------------------------------------------------------------------+
| searchOption    | query          | *(required)* Search option (for example "contain")                |
+-----------------+----------------+-------------------------------------------------------------------+
| limit           | query          | *(optional)* Number of items to display per page, by default = 10 |
+-----------------+----------------+-------------------------------------------------------------------+

Search options
^^^^^^^^^^^^^^
- contain
- not contain
- equal
- not equal
- start with
- end with
- empty
- not empty
- in
- not in
- greater
- greater or equal
- lesser
- lesser or equal

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/products/?criteria\[search\]\[type\]=contain&criteria\[search\]\[value\]=linux&limit=4 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET

Sample Response
^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 Ok

.. code-block:: json


    {
        "page": 1,
        "limit": 4,
        "pages": 1,
        "total": 1,
        "_links": {
            "self": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=1&limit=4"
            },
            "first": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=1&limit=4"
            },
            "last": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=15&limit=4"
            },
            "next": {
                "href": "\/api\/v2\/products\/?sorting%5Bcode%5D=desc&page=2&limit=4"
            }
        },
        "_embedded": {
            "items": [
                {
                    "id":124,
                    "category":"cups-and-mugs",
                    "translations": {
                        "en_US": {
                            "id":124,
                            "name":"Arch Linux Coffee Mug",
                            "description":"The Arch Linux Mug, an awesome ceramic mug printed on both sides with the Arch Linux logo."
                        }
                    },
                    "declinations": [
                        {
                            "id":66,
                            "code":99823312,
                            "weight":650,
                            "availableUnits":10,
                            "translations": {
                                "en_US": {
                                    "id":66,
                                    "name":"Arch Linux Coffee Mug",
                                    "description":"The Arch Linux Mug, an awesome ceramic mug printed on both sides with the Arch Linux logo."
                                }
                            },
                            "prices": [
                                {
                                    "id":4,
                                    "translations: {
                                        "en_US": {
                                            "name":"Normal"
                                        }
                                    },
                                    "value":12,
                                    "currencyCode":"EUR",
                                }
                            ]
                        }
                    ],
                    "imageURL":"vimmug.png",
                    "price":15
                }
            ]
        }
    }
