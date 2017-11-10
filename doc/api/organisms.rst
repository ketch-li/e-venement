Organisms API
=============

These endpoints will allow you to easily manage organisms. Base URI is `/api/v2/organisms`.

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+----------------+------------------------------------------+
| Field          | Description                              |
+================+==========================================+
| id             | Id of organism                           |
+----------------+------------------------------------------+
| name           | Organism name                            |
+----------------+------------------------------------------+


Available actions to interact with an organism
--------------------------------------------------

+------------------+-----------------------------------------------------+
| Action           | Description                                         |
+==================+=====================================================+
| List             | List available organisms                            |
+------------------+-----------------------------------------------------+


Collection of Organisms
-----------------------

You can retrieve the full organisms list by making the following request:

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/organisms

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

    $ curl http://e-venement.local/api/v2/organisms \
        -H "Authorization: Bearer SampleToken" \
        -H "Accept: application/json"
        -X GET \

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json
   
   {
    "page": 1,
    "limit": 10,
    "pages": 1,
    "total": 1,
    "_links": {
        "self": {
            "href": "\/api\/v2\/organisms?limit=10"
        },
        "first": {
            "href": "\/api\/v2\/organisms?limit=10&page=1"
        },
        "last": {
            "href": "\/api\/v2\/organisms?limit=10&page=1"
        },
        "next": {
            "href": "\/api\/v2\/organisms?limit=10&page=1"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 94,
                "name": "Organism N94",
            },
            {
                "id": 95,
                "name": "Organism N54",
            }
        ]
    }
  }
