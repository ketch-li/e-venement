Manifestations API
==================

These endpoints will allow you to manage manifestations. Base URI is '/api/v2/manifestations'.

Manifestations API response structure
-----------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+----------------------------------------------+
| Field            | Description                                  |
+==================+==============================================+
| id               | Id of the manifestation                      |
+------------------+----------------------------------------------+
| startsAt         | Date when the manifestation starts           |
+------------------+----------------------------------------------+
| endsAt           | Date when the manifestation ends             |
+------------------+----------------------------------------------+
| location         | Location object serialized                   |
+------------------+----------------------------------------------+
| gauges           | Collection of gauges object serialized       |
+------------------+----------------------------------------------+

Available actions to interact with a manifestation
--------------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| Show             | Getting a single manifestation               |
+------------------+----------------------------------------------+


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
        "id":837,
        "startsAt":"2017-04-05T10:00:00+0100",
        "endsAt":"2017-04-05T10:55:00+0100",
        "location":{
            "id":20,
            "translations":{
                "en_US":{
                    "id":20,
                    "name":"Auditorium"
                }
            },
            "address":{
                "street":"22 acacia avenue",
                "zip":"29000",
                "city":"Kemper",
                "country":"France"
            }
        },
        "gauges": [
            {
                "id":1085,
                "translations":{
                    "en_US":{
                        "id":1085,
                        "name":"General field"
                    }
                },
                "gauge":240,
                "sold":180,
                "ordered":12,
                "free":48,
                "prices": [
                    {
                        "id":3,
                        "translations":{
                            "en_US":{
                                "id":3,
                                "name":"Free",
                                "description":"Free price"
                            }
                        },
                        "value":0.00
                    },
                    {
                        "id":4,
                        "translations":{
                            "en_US":{
                                "id":4,
                                "name":"Individual",
                                "description":"Full price"
                            }
                        },
                        "value":6.00
                    }
                ]
            }
        ]
    }
