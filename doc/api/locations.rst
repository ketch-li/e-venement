Locations API
==============

These endpoints will allow you to get locations. Base URI is '/api/v2/locations'.

Locations API response structure
----------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the location                             |
+------------------+------------------------------------------------+
| name             | Name of the location                           |
+------------------+------------------------------------------------+
| address          | Address of the location                        |
+------------------+------------------------------------------------+
| zip              | Zip of the location                            |
+------------------+------------------------------------------------+
| city             | City of the location                           |
+------------------+------------------------------------------------+
| country          | Country of the location                        |
+------------------+------------------------------------------------+

Available actions to interact with a location
----------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of locations           |
+------------------+----------------------------------------------+
| Show             | Getting a single locations                   |
+------------------+----------------------------------------------+

Collection of locations
------------------------

To retrieve a collection of locations you will need to call the /api/v2/locations endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/locations

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

    $ curl http://e-venement.local/api/v2/locations \
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
      "total": 5,
      "_links": {
          "self": {
              "href": "\/tck.php\/api\/v2\/locations?limit=10"
          },
          "first": {
              "href": "\/tck.php\/api\/v2\/locations?limit=10&page=1"
          },
          "last": {
              "href": "\/tck.php\/api\/v2\/locations?limit=10&page=1"
          },
          "next": {
              "href": "\/tck.php\/api\/v2\/locations?limit=10&page=1"
          }
      },
      "_embedded": {
          "items": [
              {
                  "id": 5,
                  "name": "Ext01",
                  "address": "",
                  "zip": null,
                  "city": "",
                  "country": ""
              },
              {
                  "id": 4,
                  "name": "Quay d'orsay",
                  "address": "",
                  "zip": null,
                  "city": "",
                  "country": ""
              },
              {
                  "id": 3,
                  "name": "CCM - Salle rouge",
                  "address": "",
                  "zip": null,
                  "city": "",
                  "country": ""
              },
              {
                  "id": 2,
                  "name": "CCM - Salle bleue",
                  "address": "",
                  "zip": null,
                  "city": "",
                  "country": ""
              },
              {
                  "id": 1,
                  "name": "CCM - Grande salle",
                  "address": "",
                  "zip": null,
                  "city": "",
                  "country": ""
              }
          ]
      }
  }

Getting a single location
---------------------------

To retrieve the detail of a single location you will need to call the /api/v2/locations/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/locations/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the location                                                |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/locations/1 \
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
      "name": "CCM - Grande salle",
      "address": "",
      "zip": null,
      "city": "",
      "country": ""
  }
