MetaGauges API
==============

These endpoints will allow you to get meta-gauges. Base URI is '/api/v2/metagauges'.

MetaGauges API response structure
----------------------------------

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the meta gauge                           |
+------------------+------------------------------------------------+
| name             | Meta Gauge name                                |
+------------------+------------------------------------------------+

Available actions to interact with a meta gauge
------------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of meta gauges         |
+------------------+----------------------------------------------+
| Show             | Getting a single meta gauge                  |
+------------------+----------------------------------------------+

Collection of meta gauges
--------------------------

To retrieve a collection of meta gauges you will need to call the /api/v2/metagauges endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/metagauges

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

    $ curl http://e-venement.local/api/v2/metagauges \
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
      "total": 3,
      "_links": {
          "self": {
              "href": "\/tck.php\/api\/v2\/metagauges?limit=10"
          },
          "first": {
              "href": "\/tck.php\/api\/v2\/metagauges?limit=10&page=1"
          },
          "last": {
              "href": "\/tck.php\/api\/v2\/metagauges?limit=10&page=1"
          },
          "next": {
              "href": "\/tck.php\/api\/v2\/metagauges?limit=10&page=1"
          }
      },
      "_embedded": {
          "items": [
              {
                  "id": 1,
                  "name": "Semaine des ambassadeurs 2017"
              },
              {
                  "id": 2,
                  "name": "Test"
              },
              {
                  "id": 3,
                  "name": "TEST"
              }
          ]
      }
  }

Getting a single meta gauge
---------------------------

To retrieve the detail of a single meta gauge you will need to call the /api/v2/metagauges/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/metagauges/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the meta gauge                                                   |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/metagauges/1 \
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
      "name": "Semaine des ambassadeurs 2017"
  }
