MetaEvents API
==============

These endpoints will allow you to get meta-events. Base URI is '/api/v2/metaevents'.

MetaEvents API response structure
----------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the meta event                           |
+------------------+------------------------------------------------+
| translations     | Collection of translations, with langs as keys |
+------------------+------------------------------------------------+

A translation resource will be exposed as:

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the meta event                           |
+------------------+------------------------------------------------+
| name             | Meta Event name                                |
+------------------+------------------------------------------------+
| description      | A description of the Meta Event                |
+------------------+------------------------------------------------+

Available actions to interact with a meta event
------------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of meta events         |
+------------------+----------------------------------------------+
| Show             | Getting a single meta event                  |
+------------------+----------------------------------------------+

Collection of meta events
--------------------------

To retrieve a collection of meta events you will need to call the /api/v2/metaevents endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/metaevents

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

    $ curl http://e-venement.local/api/v2/metaevents \
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
              "href": "\/tck_dev.php\/api\/v2\/metaevents?limit=10"
          },
          "first": {
              "href": "\/tck_dev.php\/api\/v2\/metaevents?limit=10&page=1"
          },
          "last": {
              "href": "\/tck_dev.php\/api\/v2\/metaevents?limit=10&page=1"
          },
          "next": {
              "href": "\/tck_dev.php\/api\/v2\/metaevents?limit=10&page=1"
          }
      },
      "_embedded": {
          "items": [
              {
                  "id": 1,
                  "translations": {
                      "fr": {
                          "name": "Semaine des ambassadeurs 2017",
                          "description": "Semaine des ambassadeurs 2017"
                      }
                  }
              }
          ]
      }
  }


Getting a single meta event
---------------------------

To retrieve the detail of a single meta event you will need to call the /api/v2/metaevents/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/metaevents/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the meta event                                                   |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/metaevents/1 \
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
            "fr": {
                "name": "Semaine des ambassadeurs 2017",
                "description": "Semaine des ambassadeurs 2017"
            }
        }
    }
