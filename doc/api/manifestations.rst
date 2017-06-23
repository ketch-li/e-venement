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
| startsAt         | Date when the manifestation starts [ISO 8601 Extended Format] (https://fr.wikipedia.org/wiki/ISO_8601)   |
+------------------+----------------------------------------------------------------------------------------------------------+
| endsAt           | Date when the manifestation ends  [ISO 8601 Extended Format] (https://fr.wikipedia.org/wiki/ISO_8601)    |
+------------------+----------------------------------------------------------------------------------------------------------+
| location         | Location object serialized                                                                               |
+------------------+----------------------------------------------------------------------------------------------------------+
| event            | Event object serialized                                                                                  |
+------------------+----------------------------------------------------------------------------------------------------------+
| gauges           | Collection of gauges objects serialized                                                                  |
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
| totalUnits       | *Optional* The size of the gauge                                         |
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

To retrieve the full customers list, you will need to call the /api/v2/manifestations endpoint with the GET method.

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
        -X GET \'
         {
             'criteria[metaEvents.id][type]': 'equals',
             'criteria[metaEvents.id][value]': app.config.metaEventId,
             'limit': 100
        }'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

   {
    "page": 1,
    "limit": 10,
    "pages": 6,
    "total": 53,
    "_links": {
        "self": {
            "href": "\/tck.php\/api\/v2\/manifestations?limit=10"
        },
        "first": {
            "href": "\/tck.php\/api\/v2\/manifestations?limit=10&page=1"
        },
        "last": {
            "href": "\/tck.php\/api\/v2\/manifestations?limit=10&page=6"
        },
        "next": {
            "href": "\/tck.php\/api\/v2\/manifestations?limit=10&page=2"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 14,
                "startsAt": "20170801T124500+02:00",
                "endsAt": "20170801T144500+02:00",
                "event": {
                    "id": 8,
                    "metaEvent": {
                        "id": 1,
                        "translations": {
                            "fr": {
                                "name": "Tournoi Foot saison 2000",
                                "description": "Tournoi Foot saison 2000"
                            }
                        }
                    },
                    "category": "Moins de 18 ans",
                    "translations": {
                        "fr": {
                            "name": "Tour 1",
                            "subtitle": "",
                            "short_name": "Tour 1",
                            "description": "",
                            "extradesc": "",
                            "extraspec": ""
                        }
                    },
                    "imageId": null,
                    "imageURL": null
                },
                "location": {
                    "id": 3,
                    "name": "Terrain 10",
                    "address": "",
                    "zip": "",
                    "city": "",
                    "country": ""
                },
                "gauges": [
                    {
                        "id": 14,
                        "name": "Tournoi Foot saison 2000",
                        "availableUnits": 10,
                        "prices": [
                            {
                                "id": 1,
                                "value": "0.000",
                                "currencyCode": 978,
                                "translations": {
                                    "en": {
                                        "name": "Invitation",
                                        "description": ""
                                    },
                                    "fr": {
                                        "name": "Invitation",
                                        "description": ""
                                    }
                                }
                            }
                        ]
                    }
                ],
                "timeSlots": [
                    {
                        "id": 5,
                        "name": "Moins de 18 ans",
                        "startsAt": "20170801T124500+02:00",
                        "endsAt": "20170801T144500+02:00"
                    }
                ]
            },
            {
                "id": 20,
                "startsAt": "20170803T124500+02:00",
                "endsAt": "20170803T144500+02:00",
                "event": {
                    "id": 8,
                    "metaEvent": {
                        "id": 1,
                        "translations": {
                            "fr": {
                                "name": "Tournoi Foot saison 2000",
                                "description": "Tournoi Foot saison 2000"
                            }
                        }
                    },
                    "category": "Moins de 18 ans",
                    "translations": {
                        "fr": {
                            "name": "Tour 1",
                            "subtitle": "",
                            "short_name": "Tour 1",
                            "description": "",
                            "extradesc": "",
                            "extraspec": ""
                        }
                    },
                    "imageId": null,
                    "imageURL": null
                },
                "location": {
                    "id": 3,
                    "name": "Terrain 10",
                    "address": "",
                    "zip": "",
                    "city": "",
                    "country": ""
                },
                "gauges": [
                    {
                        "id": 20,
                        "name": "Tournoi Foot saison 2000",
                        "availableUnits": 10,
                        "prices": [
                            {
                                "id": 1,
                                "value": "0.000",
                                "currencyCode": 978,
                                "translations": {
                                    "en": {
                                        "name": "Invitation",
                                        "description": ""
                                    },
                                    "fr": {
                                        "name": "Invitation",
                                        "description": ""
                                    }
                                }
                            }
                        ]
                    }
                ],
                "timeSlots": [
                    {
                        "id": 9,
                        "name": "Présentation du tournoi",
                        "startsAt": "20170802T081500+02:00",
                        "endsAt": "20180802T084500+02:00"
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

    $ curl http://e-venement.local/api/v2/manifestations/13 \
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
        "id": 13,
        "startsAt": "20170801T173000+02:00",
        "endsAt": "20170801T181500+02:00",
        "event": {
            "id": 13,
            "metaEvent": {
                "id": 1,
                "translations": {
                    "fr": {
                        "name": "Tournoi Foot saison 2000",
                        "description": "Tournoi Foot saison 2000"
                    }
                }
            },
            "category": "Moins de 20 ans",
            "translations": {
                "fr": {
                    "name": "Tour 4",
                    "subtitle": "",
                    "short_name": "Tour 4",
                    "description": "",
                    "extradesc": "",
                    "extraspec": ""
                }
            },
            "imageId": null,
            "imageURL": null
        },
        "location": {
            "id": 4,
            "name": "Terrain 12",
            "address": "",
            "zip": "",
            "city": "",
            "country": ""
        },
        "gauges": [
            {
                "id": 13,
                "name": "Tournoi Foot saison 2000",
                "availableUnits": 10,
                "prices": [
                    {
                        "id": 1,
                        "value": "0.000",
                        "currencyCode": 978,
                        "translations": {
                            "en": {
                                "name": "Invitation",
                                "description": ""
                            },
                            "fr": {
                                "name": "Invitation",
                                "description": ""
                            }
                        }
                    }
                ]
            }
        ],
        "timeSlots": [
            {
                "id": 7,
                "name": "Présentation du tournoi",
                "startsAt": "20170801T173000+02:00",
                "endsAt": "20170801T181500+02:00"
            }
        ]
    }
  ]
