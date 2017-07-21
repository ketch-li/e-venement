VAT API
========

These endpoints will allow you to get VATs. Base URI is '/api/v2/vats'.

vats API response structure
----------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the VAT                                  |
+------------------+------------------------------------------------+
| name             | Name of the VAT                                |
+------------------+------------------------------------------------+
| address          | Address of the VAT                             |
+------------------+------------------------------------------------+
| zip              | Zip of the VAT                                 |
+------------------+------------------------------------------------+
| city             | City of the VAT                                |
+------------------+------------------------------------------------+
| country          | Country of the VAT                             |
+------------------+------------------------------------------------+

Available actions to interact with a VAT
----------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of VATs                |
+------------------+----------------------------------------------+
| Show             | Getting a single VATs                        |
+------------------+----------------------------------------------+

Collection of vats
------------------------

To retrieve a collection of VATs you will need to call the /api/v2/vats endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/vats

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

    $ curl http://e-venement.local/api/v2/vats \
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
        "pages": 1,
        "total": 1,
        "_links": {
            "self": {
                "href": "\/api\/v2\/vats?limit=10"
            },
            "first": {
                "href": "\/api\/v2\/vats?limit=10&page=1"
            },
            "last": {
                "href": "\/api\/v2\/vats?limit=10&page=1"
            },
            "next": {
                "href": "\/api\/v2\/vats?limit=10&page=1"
            }
        },
        "_embedded": {
            "items": [
                {
                    "id": 1,
                    "name": "Mini",
                    "value": "0.0210"
                }
            ]
        }
    }

Getting a single VAT
---------------------------

To retrieve the detail of a single VAT you will need to call the /api/v2/vats/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/vats/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the VAT                                                |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/vats/1 \
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
        "name": "Mini",
        "value": "0.0210"
    }
