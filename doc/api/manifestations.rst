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
| endsAt           | Vat rate appliable for this manifestation                                                                |
+------------------+----------------------------------------------------------------------------------------------------------+
| confirmed        | Confirmation state (boolean) of this manifestation (excepted for specific cases, should be true)         |
+------------------+----------------------------------------------------------------------------------------------------------+
| location         | Location object serialized                                                                               |
+------------------+----------------------------------------------------------------------------------------------------------+
| event            | Event object serialized                                                                                  |
+------------------+----------------------------------------------------------------------------------------------------------+
| gauges           | Collection of gauges objects serialized                                                                  |
+------------------+----------------------------------------------------------------------------------------------------------+
| timeSlots        | Collection of timeslot objects serialized                                                                |
+------------------+----------------------------------------------------------------------------------------------------------+

TimeSlots API response structure *Optional*
--------------------------------------------

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
| List             | List manifestations                          |
+------------------+----------------------------------------------+
| Show             | Getting a single manifestation               |
+------------------+----------------------------------------------+
| Create           | Create a manifestation                       |
+------------------+----------------------------------------------+
| Update           | Update a manifestation                       |
+------------------+----------------------------------------------+
| Delete           | Delete a manifestation                       |
+------------------+----------------------------------------------+
| Add Price        | Add a price to a manifestation               |
+------------------+----------------------------------------------+
| Remove Price     | Remove a price from a manifestation          |
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
        -X GET \
        -- data '{
                "criteria[metaEvents.id][type]": "equals",
                "criteria[metaEvents.id][value]": 12,
                "limit": 100
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
            "href": "\/api\/v2\/manifestations?limit=10"
        },
        "first": {
            "href": "\/api\/v2\/manifestations?limit=10&page=1"
        },
        "last": {
            "href": "\/api\/v2\/manifestations?limit=10&page=6"
        },
        "next": {
            "href": "\/api\/v2\/manifestations?limit=10&page=2"
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
        -X GET

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

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

Creating a manifestation
-------------------------

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/manifestations

+--------------------------+----------------+-----------------------------------------------------+
| Parameter                | Parameter type | Description                                         |
+==========================+================+=====================================================+
| Authorization            | header         | Token received during authentication                |
+--------------------------+----------------+-----------------------------------------------------+
| id                       | url attribute  | Id of the requested resource                        |
+--------------------------+----------------+-----------------------------------------------------+
| startsAt                 | request        | Manifestation start date & time *Required*          |
+--------------------------+----------------+-----------------------------------------------------+
| endsAt                   | request        | Manifestation end date & time *Required*            |
+--------------------------+----------------+-----------------------------------------------------+
| eventId                  | request        | Manifestation event Id *Required*                   |
+--------------------------+----------------+-----------------------------------------------------+
| locationId               | request        | Manifestation location Id *Required*                |
+--------------------------+----------------+-----------------------------------------------------+
| vatId                    | request        | Manifestation appliable VAT Id *Required*           |
+--------------------------+----------------+-----------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl -k "https://dev2.libre-informatique.fr/tck.php/api/v2/manifestations" \
           -H "Content-Type: application/json" \
           -H "Authorization: Bearer 00d22dd8b44673c16012f16d3d6bbe35" \
           -X POST
           --data '{
                "startsAt":"20170717T120355+02:00",
                "endsAt":"20170717T130355+02:00",
                "eventId":1,
                "locationId":5,
                "vatId":1
           }'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 201 Created

.. code-block:: json

    {
        "id": 89,
        "startsAt": "20170717T120355+02:00",
        "endsAt": "20170717T130355+02:00",
        "vat": "0.0000",
        "event": {
            "id": 1,
            "metaEvent": {
                "id": 1,
                "translations": {
                    "fr": {
                        "name": "Semaine des ambassadeurs 2017",
                        "description": "Semaine des ambassadeurs 2017"
                    }
                }
            },
            "category": "Caf\u00e9 d'accueil",
            "translations": {
                "en": {
                    "name": "",
                    "subtitle": "",
                    "short_name": "",
                    "description": "",
                    "extradesc": "",
                    "extraspec": ""
                },
                "fr": {
                    "name": "Caf\u00e9 d'accueil",
                    "subtitle": "",
                    "short_name": "Accueil",
                    "description": "",
                    "extradesc": "",
                    "extraspec": ""
                }
            },
            "imageId": 1,
            "imageURL": "\/tck.php\/api\/v2\/pictures\/1"
        },
        "location": {
            "id": 5,
            "name": "Ext01",
            "address": "",
            "zip": "",
            "city": "",
            "country": ""
        },
        "gauges": []
    }

If you try to create a manifestation without a required field, you will receive a ``400 Bad Request`` error.

Example
^^^^^^^

.. code-block:: bash

    $ curl -k "https://dev2.libre-informatique.fr/tck.php/api/v2/manifestations" \
           -H "Content-Type: application/json" \
           -H "Authorization: Bearer 00d22dd8b44673c16012f16d3d6bbe35" \
           -X POST
           --data '{
                "startsAt":"20170717T120355+02:00",
                "endsAt":"20170717T130355+02:00",
                "eventId":1,
           }'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 400 Bad Request

.. code-block:: json

    {
        "code": 400,
        "message": "Create failed"
    }

Updating a Manifestation
-------------------------

You can request full or partial update of resource, using the POST method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/manifestations/{id}

+--------------------------+----------------+-----------------------------------------------------+
| Parameter                | Parameter type | Description                                         |
+==========================+================+=====================================================+
| Authorization            | header         | Token received during authentication                |
+--------------------------+----------------+-----------------------------------------------------+
| id                       | url attribute  | Id of the requested resource                        |
+--------------------------+----------------+-----------------------------------------------------+
| startsAt                 | request        | Manifestation start date & time                     |
+--------------------------+----------------+-----------------------------------------------------+
| endsAt                   | request        | Manifestation end date & time                       |
+--------------------------+----------------+-----------------------------------------------------+
| eventId                  | request        | Manifestation event Id                              |
+--------------------------+----------------+-----------------------------------------------------+
| locationId               | request        | Manifestation location Id                           |
+--------------------------+----------------+-----------------------------------------------------+
| vatId                    | request        | Manifestation appliable VAT Id                      |
+--------------------------+----------------+-----------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations/84 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '
            {
                "endsAt":"20170717T111927+02:00",
                "locationId":1
           }'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "id": 84,
        "startsAt": "20170717T094924+02:00",
        "endsAt": "20170717T111927+02:00",
        "vat": "0.0000",
        "event": {
            "id": 1,
            "metaEvent": {
                "id": 1,
                "translations": {
                    "fr": {
                        "name": "Semaine des ambassadeurs 2017",
                        "description": "Semaine des ambassadeurs 2017"
                    }
                }
            },
            "category": "Caf\u00e9 d'accueil",
            "translations": {
                "en": {
                    "name": "",
                    "subtitle": "",
                    "short_name": "",
                    "description": "",
                    "extradesc": "",
                    "extraspec": ""
                },
                "fr": {
                    "name": "Caf\u00e9 d'accueil",
                    "subtitle": "",
                    "short_name": "Accueil",
                    "description": "",
                    "extradesc": "",
                    "extraspec": ""
                }
            },
            "imageId": 1,
            "imageURL": "\/tck.php\/api\/v2\/pictures\/1"
        },
        "location": {
            "id": 1,
            "name": "CCM - Grande salle",
            "address": "",
            "zip": "",
            "city": "",
            "country": ""
        },
        "gauges": []
    }

Deleting a Manifestation *Optional*
------------------------------------

Definition
^^^^^^^^^^

.. code-block:: text

    DELETE /api/v2/manifestations/{id}

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

    $ curl http://e-venement.local/api/v2/manifestations/84 \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json" \
        -X DELETE

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 No Content
