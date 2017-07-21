Prices API
==========

These endpoints will allow you to manage prices. Base URI is '/api/v2/prices'.

Prices API response structure
-----------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+------------------------------------------------------------+
| Field            | Description                                                |
+==================+============================================================+
| id               | Id of the price                                            |
+------------------+------------------------------------------------------------+
| translations     | Collection of translations                                 |
+------------------+------------------------------------------------------------+
| value            | Amount as float, default amount if not attached to a gauge |
+------------------+------------------------------------------------------------+

Available actions to interact with a price
------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of prices              |
+------------------+----------------------------------------------+
| Show             | Getting a single price                       |
+------------------+----------------------------------------------+

Collection of prices
--------------------

To retrieve a collection of prices you will need to call the /api/v2/prices endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/prices

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

    $ curl http://e-venement.local/api/v2/prices \
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
        "limit": 10,
        "pages": 1,
        "total": 1,
        "_links": {
            "self": {
                "href": "\/tck_dev.php\/api\/v2\/prices?limit=10"
            },
            "first": {
                "href": "\/tck_dev.php\/api\/v2\/prices?limit=10&page=1"
            },
            "last": {
                "href": "\/tck_dev.php\/api\/v2\/prices?limit=10&page=1"
            },
            "next": {
                "href": "\/tck_dev.php\/api\/v2\/prices?limit=10&page=1"
            }
        },
        "_embedded": {
            "items": [
                {
                    "id": 1,
                    "translations": {
                        "en": {
                            "name": "Invitation",
                            "description": ""
                        },
                        "fr": {
                            "name": "Invitation",
                            "description": ""
                        }
                    },
                    "value": "0.00"
                }
            ]
        }
    }

Getting a single price
---------------------------

To retrieve the detail of a single price you will need to call the /api/v2/prices/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/prices/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the price                                                   |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/prices/1 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "id": 1,
        "translations": {
            "en": {
                "name": "Invitation",
                "description": ""
            },
            "fr": {
                "name": "Invitation",
                "description": ""
            }
        },
        "value": "0.00"
    }
