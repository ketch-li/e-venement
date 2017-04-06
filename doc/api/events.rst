Events API
==========

These endpoints will allow you to manage events. Base URI is '/api/v2/events'.

Event API response structure
----------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+----------------------------------------------+
| Field            | Description                                  |
+==================+==============================================+
| id               | Id of the product                            |
+------------------+----------------------------------------------+
| metaEvent        | Meta-event object serialized                 |
+------------------+----------------------------------------------+
| category         | Category of the event                        |
+------------------+----------------------------------------------+
| translations     | Collection of translations                   |
+------------------+----------------------------------------------+
| length           | Length of the event                          |
+------------------+----------------------------------------------+
| billing          | URI of the event's image                     |
+------------------+----------------------------------------------+
| minAge           | Minimum authorized age for participating     |
+------------------+----------------------------------------------+
| maxAge           | Maximum authorized age for participating     |
+------------------+----------------------------------------------+

Available actions to interact with an event
-------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of events              |
+------------------+----------------------------------------------+


Getting a single event
----------------------

To retrieve a collection of events you will need to call the /api/v2/events endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/events

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/events \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET \

Exemplary Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "page": 1,
        "limit": 4,
        "pages": 16,
        "total": 63,
        "_links": {
            "self": {
                "href": "\/api\/v1\/events\/?page=1&limit=4"
            },
            "first": {
                "href": "\/api\/v1\/events\/?page=1&limit=4"
            },
            "last": {
                "href": "\/api\/v1\/events\/?page=16&limit=4"
            },
            "next": {
                "href": "\/api\/v1\/events\/?page=2&limit=4"
            }
        },
        "_embedded": {
            "items": [
                {
                    "id":123,
                    "metaEvent":{
                        "id":12,
                        "translations":{
                            "en_US":{
                                "id":12,
                                "name":"2017 Season",
                                "decription":"Performances for 2017"
                            }
                        }
                    },
                    "category":"Theater",
                    "translations":{
                        "en_US":{
                            "id":123,
                            "name":"Shoot up",
                            "description":"Beautiful. Not beautiful. So is the Paloma's world."
                        }
                    },
                    "length":"00:55",
                    "billing":"shootup.png",
                    "minAge":7,
                    "maxAge":77
                },
                {
                    "id":124,
                    "metaEvent":{
                        "id":12,
                        "translations":{
                            "en_US":{
                                "id":12,
                                "name":"2017 Season",
                                "decription":"Performances for 2017"
                            }
                        }
                    },
                    "category":"Show",
                    "translations":{
                        "en_US":{
                            "id":124,
                            "name":"Online life",
                            "description":"Welcome to the teenage years 2.0."
                        }
                    },
                    "length":"01:00",
                    "billing":"onlinelife.png",
                    "minAge":10
                }
            ]
        }
    }

