Events API
==========

These endpoints will allow you to manage events. Base URI is '/api/v2/events'.

Events API response structure
-----------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the event                                |
+------------------+------------------------------------------------+
| metaEvent        | Meta-event object serialized                   |
+------------------+------------------------------------------------+
| category         | Category of the event                          |
+------------------+------------------------------------------------+
| translations     | Collection of translations                     |
+------------------+------------------------------------------------+
| imageURL         | URI of the image of the event                  |
+------------------+------------------------------------------------+
| manifestations   | Collection of manifestations object serialized |
+------------------+------------------------------------------------+

If you request for more detailed data, you will receive an object with the following fields:

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the event                                |
+------------------+------------------------------------------------+
| metaEvent        | Meta-event object serialized                   |
+------------------+------------------------------------------------+
| category         | Category of the event                          |
+------------------+------------------------------------------------+
| translations     | Collection of translations                     |
+------------------+------------------------------------------------+
| imageURL          | URI of the event's image                      |
+------------------+------------------------------------------------+
| minAge           | Minimum authorized age for participating       |
+------------------+------------------------------------------------+
| maxAge           | Maximum authorized age for participating       |
+------------------+------------------------------------------------+
| manifestations   | Collection of manifestations object serialized |
+------------------+------------------------------------------------+

Available actions to interact with an event
-------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of events              |
+------------------+----------------------------------------------+
| Show             | Getting a single event                       |
+------------------+----------------------------------------------+

Collection of events
--------------------

To retrieve a collection of events you will need to call the /api/v2/events endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/events

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| page          | query          | (optional) Number of the page, by default = 1                     |
+---------------+----------------+-------------------------------------------------------------------+
| limit         | query          | (optional) Number of items to display per page, by default = 10   |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/events \
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
    "pages": 2,
    "total": 14,
    "_links": {
        "self": {
            "href": "\/tck.php\/api\/v2\/events?limit=10"
        },
        "first": {
            "href": "\/tck.php\/api\/v2\/events?limit=10&page=1"
        },
        "last": {
            "href": "\/tck.php\/api\/v2\/events?limit=10&page=2"
        },
        "next": {
            "href": "\/tck.php\/api\/v2\/events?limit=10&page=2"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 6,
                "metaEvent": {
                    "id": 1,
                    "translations": {
                        "fr": {
                            "name": "Jeux Olympiques 2020",
                            "description": "Jeux Olympiques 2020"
                        }
                    }
                },
                "category": "Hommes/Femmes",
                "translations": {
                    "fr": {
                        "name": "Présentation des pays",
                        "subtitle": "",
                        "short_name": "Zone Nord",
                        "description": "",
                        "extradesc": "",
                        "extraspec": ""
                    }
                },
                "imageURL": "\/tck.php\/api\/v2\/picture\/6",
                "manifestations": [
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
                                        "name": "Jeux Olympiques 2020",
                                        "description": "Jeux Olympiques 2020"
                                    }
                                }
                            },
                            "category": "Femmes",
                            "translations": {
                                "fr": {
                                    "name": "Natation",
                                    "subtitle": "",
                                    "short_name": "Natation",
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
                            "name": "Piscine 1",
                            "address": "",
                            "zip": "",
                            "city": "",
                            "country": ""
                        },
                        "gauges": [
                            {
                                "id": 14,
                                "name": "Jeux Olympiques 2020",
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
                                "name": "Natation",
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
                                        "name": "Jeux Olympiques 2020",
                                        "description": "Jeux Olympiques 2020"
                                    }
                                }
                            },
                            "category": "Hommes",
                            "translations": {
                                "fr": {
                                    "name": "Atletisme",
                                    "subtitle": "",
                                    "short_name": "Atletisme",
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
                            "name": "Stade Louis II",
                            "address": "",
                            "zip": "",
                            "city": "",
                            "country": ""
                        },
                        "gauges": [
                            {
                                "id": 20,
                                "name": "Jeux Olympiques 2020",
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
                                "name": "Atletisme",
                                "startsAt": "20170802T081500+02:00",
                                "endsAt": "20180802T084500+02:00"
                            }
                        ]
                    }
                ]
             }
          ]
        }
    }

Getting a single event
----------------------

To retrieve the detail of a single event you will need to call the /api/v2/events/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/events/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the event                                                   |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/events/123 \
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
        "id": 123,
        "metaEvent": {
            "id": 1,
            "translations": {
                "fr": {
                    "name": "Jeux Olympiques 2020",
                    "description": "Jeux Olympiques 2020"
                }
            }
        },
        "category": "Hommes",
        "translations": {
            "fr": {
                "name": "tenis",
                "subtitle": "",
                "short_name": "Seniors",
                "description": "",
                "extradesc": "",
                "extraspec": ""
            }
        },
        "imageURL": "\/tck.php\/api\/v2\/picture\/6",
        "manifestations": [
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
                                "name": "Jeux Olympiques 2020",
                                "description": "Jeux Olympiques 2020"
                            }
                        }
                    },
                    "category": "Pays Sud",
                    "translations": {
                        "fr": {
                            "name": "Groupe H",
                            "subtitle": "",
                            "short_name": "Seniors",
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
                    "name": "Rolans Garros",
                    "address": "",
                    "zip": "",
                    "city": "",
                    "country": ""
                },
                "gauges": [
                    {
                        "id": 14,
                        "name": "Jeux Olympiques 2020",
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
                        "name": "Remise des medailles",
                        "startsAt": "20170801T124500+02:00",
                        "endsAt": "20170801T144500+02:00"
                    }
                ]
            }
         ]
      }
  ]
