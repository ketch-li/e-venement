Manifestations API
==================

These endpoints will allow you to manage manifestations. Base URI is '/api/v2/manifestations'.

Manifestations API response structure
--------------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+----------------------------------------------------------------------------------------------------------+
| Field            | Description                                                                                              |
+==================+==========================================================================================================+
| id               | Id of the manifestation                                                                                  |
+------------------+----------------------------------------------------------------------------------------------------------+
| startsAt         | Date when the manifestation starts (`ISO 8601 Extended Format <https://fr.wikipedia.org/wiki/ISO_8601>`) |
+------------------+----------------------------------------------------------------------------------------------------------+
| endsAt           | Date when the manifestation ends (`ISO 8601 Extended Format <https://fr.wikipedia.org/wiki/ISO_8601>`)   |
+------------------+----------------------------------------------------------------------------------------------------------+
| location         | Location object serialized                                                                               |
+------------------+----------------------------------------------------------------------------------------------------------+
| event            | Name of related event                                                                                    |
+------------------+----------------------------------------------------------------------------------------------------------+
| metaEvent        | Name of related meta-event                                                                               |
+------------------+----------------------------------------------------------------------------------------------------------+
| gauges           | Collection of gauges object serialized                                                                   |
+------------------+----------------------------------------------------------------------------------------------------------+

Gauges API response structure
------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+--------------------------------------------------------------------------+
| Field            | Description                                                              |
+==================+==========================================================================+
| id               | Id of the gauge                                                          |
+------------------+--------------------------------------------------------------------------+
| name             | Name of the current Gauge (through its Workspace)                        |
+------------------+--------------------------------------------------------------------------+
| availableUnits   | The available space in this gauge                                        |
|                  | To avoid information leaks, if more space is available than the maximum  |
|                  | configured, the maximum is exposed instead of the really available space |
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

Available actions to interact with a manifestation
--------------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| Show             | Getting a single manifestation               |
+------------------+----------------------------------------------+


Getting a collection of manifestations
---------------------------------------

To retrieve the full customers list, you will need to call the /api/v2/manifestations/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/manifestations

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET \

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    [
        {
            "id": 112,
            "startsAt": "2016-07-22 21:00:00",
            "endsAt": "2016-07-22 22:30:00",
            "metaEvent": [
                {}
            ],
            "location": {
                "id": 4,
                "name": "Th\u00e9\u00e2tre de Cornouaille",
                "address": "1 Esplanade Fran\u00e7ois Mitterrand",
                "zip": "29000",
                "city": "QUIMPER",
                "country": "France"
            },
            "gauges": [
                {
                    "id": 290,
                    "name": "Placement libre assis",
                    "availableUnits": 10,
                    "prices": {
                        "id": 27,
                        "value": "20.000",
                        "currencyCode": 978,
                        "translations": {
                            "fr": {
                                "name": "TP",
                                "description": "Tarif Plein"
                            }
                        }
                    }
                }
            ]
        },
        {
            "id": 127,
            "startsAt": "2017-07-27 21:00:00",
            "endsAt": "2017-07-27 22:30:00",
            "metaEvent": [
                {}
            ],
            "location": {
                "id": 13,
                "name": "Novomax",
                "address": "2 Boulevard Dupleix",
                "zip": "29000",
                "city": "QUIMPER",
                "country": "FRANCE"
            },
            "gauges": [
                {
                    "id": 321,
                    "name": "Placement libre assis",
                    "availableUnits": 10,
                    "prices": {
                        "id": 27,
                        "value": "20.000",
                        "currencyCode": 978,
                        "translations": {
                            "fr": {
                                "name": "TP",
                                "description": "Tarif Plein"
                            }
                        }
                    }
                }
            ]
        }
    ]


Getting a single manifestation
------------------------------

To retrieve the detail of a single manifestation you will need to call the /api/v2/manifestations/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/manifestations/{id}

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations/837 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET \

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "id": 112,
        "startsAt": "2016-07-22 21:00:00",
        "endsAt": "2016-07-22 22:30:00",
        "metaEvent": [
            {}
        ],
        "location": {
            "id": 4,
            "name": "Th\u00e9\u00e2tre de Cornouaille",
            "address": "1 Esplanade Fran\u00e7ois Mitterrand",
            "zip": "29000",
            "city": "QUIMPER",
            "country": "France"
        },
        "gauges": [
            {
                "id": 290,
                "name": "Placement libre assis",
                "availableUnits": 10,
                "prices": {
                    "id": 27,
                    "value": "20.000",
                    "currencyCode": 978,
                    "translations": {
                        "fr": {
                            "name": "TP",
                            "description": "Tarif Plein"
                        }
                    }
                }
            }
        ]
    }
