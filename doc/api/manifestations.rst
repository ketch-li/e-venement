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
| event_id         | Id of related event                                                                                      |
+------------------+----------------------------------------------------------------------------------------------------------+
| event            | Translations for the related event                                                                       |
+------------------+----------------------------------------------------------------------------------------------------------+
| metaEvent        | Name of related meta-event                                                                               |
+------------------+----------------------------------------------------------------------------------------------------------+
| gauges           | Collection of gauges object serialized                                                                   |
+------------------+----------------------------------------------------------------------------------------------------------+
| timeSlots        | Collection of timeslot objects serialized                                                                |
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

TimeSlots API response structure
--------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+--------------------------------------------------------------------------+
| Field            | Description                                                              |
+==================+==========================================================================+
| id               | Id of the timeslot                                                       |
+------------------+--------------------------------------------------------------------------+
| title            | Title of the timeslot                                                    |
+------------------+--------------------------------------------------------------------------+
| startsAt         | Start date of timeslot                                                   |
+------------------+--------------------------------------------------------------------------+
| endsAt           | End date of timeslot                                                     |
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

    {
        "page": 1,
        "limit": 10,
        "pages": 147,
        "total": 1463,
        "_links": {
            "self": {
                "href": "\/tck_dev.php\/api\/v2\/events?limit=10"
            },
            "first": {
                "href": "\/tck_dev.php\/api\/v2\/events?limit=10&page=1"
            },
            "last": {
                "href": "\/tck_dev.php\/api\/v2\/events?limit=10&page=147"
            },
            "next": {
                "href": "\/tck_dev.php\/api\/v2\/events?limit=10&page=2"
            }
        },
        "_embedded": {
            "items": [
                {
                    "id": 1,
                    "metaEvent": {
                        "id": 3,
                        "translations": {
                            "en": {
                                "name": "Talents en Sc\u00e8ne 2012",
                                "description": ""
                            },
                            "fr": {
                                "name": "Talents en Sc\u00e8ne 2012 ",
                                "description": " "
                            }
                        }
                    },
                    "category": null,
                    "translations": {
                        "fr": {
                            "name": "Talents en Sc\u00e8ne",
                            "subtitle": null,
                            "short_name": "",
                            "description": "",
                            "extradesc": "",
                            "extraspec": ""
                        }
                    },
                    "imageURL": "\/pub_dev.php\/picture\/1\/display",
                    "manifestations": [
                        {
                            "id": 123,
                            "startsAt": "2016-07-23 15:00:00",
                            "endsAt": "2016-07-23 16:30:00",
                            "event_id": 115,
                            "event": {
                                "fr": {
                                    "name": "Sadorn Ar Vugale",
                                    "subtitle": "",
                                    "short_name": "",
                                    "description": "",
                                    "extradesc": "",
                                    "extraspec": ""
                                }
                            },
                            "metaEvent": {
                                "fr": {
                                    "name": "Cornouaille 2016",
                                    "description": ""
                                }
                            },
                            "location": {
                                "id": 11,
                                "name": "Cour du Coll\u00e8ge La Tour d'Auvergne",
                                "address": "",
                                "zip": "",
                                "city": "",
                                "country": "France"
                            },
                            "gauges": [
                                {
                                    "id": 314,
                                    "name": "Placement libre assis",
                                    "availableUnits": 10,
                                    "prices": [
                                        {
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
                                    ]
                                }
                            ],
                            "timeSlots": [
                                {
                                    "id":10001,
                                    "title": "Créneau de l'après midi",
                                    "startsAt": "2016-07-23 14:00:00",
                                    "endsAt": "2016-07-23 18:00:00"
                                }
                            ]
                        }
                    ]
                },
                {
                    "id": 2,
                    "metaEvent": {
                        "id": 2,
                        "translations": {
                            "en": {
                                "name": "Da\u00f1s 2012",
                                "description": ""
                            },
                            "fr": {
                                "name": "Da\u00f1s 2012 ",
                                "description": " "
                            }
                        }
                    },
                    "category": "Danse traditionnelle",
                    "translations": {
                        "fr": {
                            "name": "FESTIVAL DA\u00d1S ",
                            "subtitle": null,
                            "short_name": "",
                            "description": "",
                            "extradesc": "",
                            "extraspec": ""
                        }
                    },
                    "imageURL": "\/pub_dev.php\/picture\/2\/display",
                    "manifestations": [
                        {
                            "id": 123,
                            "startsAt": "2016-07-23 15:00:00",
                            "endsAt": "2016-07-23 16:30:00",
                            "event_id": 115,
                            "event": {
                                "fr": {
                                    "name": "Sadorn Ar Vugale",
                                    "subtitle": "",
                                    "short_name": "",
                                    "description": "",
                                    "extradesc": "",
                                    "extraspec": ""
                                }
                            },
                            "metaEvent": {
                                "fr": {
                                    "name": "Cornouaille 2016",
                                    "description": ""
                                }
                            },
                            "location": {
                                "id": 11,
                                "name": "Cour du Coll\u00e8ge La Tour d'Auvergne",
                                "address": "",
                                "zip": "",
                                "city": "",
                                "country": "France"
                            },
                            "gauges": [
                                {
                                    "id": 314,
                                    "name": "Placement libre assis",
                                    "availableUnits": 10,
                                    "prices": [
                                        {
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
                                    ]
                                }
                            ],
                            "timeSlots": [
                                {
                                    "id":10002,
                                    "title": "Début du festival",
                                    "startsAt": "2016-07-23 15:00:00",
                                    "endsAt": "2016-07-23 19:00:00"
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    }


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
        "id": 123,
        "startsAt": "2016-07-23 15:00:00",
        "endsAt": "2016-07-23 16:30:00",
        "event_id": 115,
        "event": {
            "fr": {
                "name": "Sadorn Ar Vugale",
                "subtitle": "",
                "short_name": "",
                "description": "",
                "extradesc": "",
                "extraspec": ""
            }
        },
        "metaEvent": {
            "fr": {
                "name": "Cornouaille 2016",
                "description": ""
            }
        },
        "location": {
            "id": 11,
            "name": "Cour du Coll\u00e8ge La Tour d'Auvergne",
            "address": "",
            "zip": "",
            "city": "",
            "country": "France"
        },
        "gauges": [
            {
                "id": 314,
                "name": "Placement libre assis",
                "availableUnits": 10,
                "prices": [
                    {
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
                ]
            }
        ],
        "timeSlots": [
            {
                "id":10001,
                "title": "Créneau de l'après midi",
                "startsAt": "2016-07-23 14:00:00",
                "endsAt": "2016-07-23 18:00:00"
            }
        ]
    }
