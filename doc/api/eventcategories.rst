EventCategories API
===================

These endpoints will allow you to get meta-events. Base URI is '/api/v2/eventcategories'.

EventCategories API response structure
---------------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+------------------------------------------------+
| Field            | Description                                    |
+==================+================================================+
| id               | Id of the event category                       |
+------------------+------------------------------------------------+
| name             | The name of the category (w/o i18n)            |
+------------------+------------------------------------------------+

Available actions to interact with an event
-------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of event categories    |
+------------------+----------------------------------------------+
| Show             | Getting a single event category              |
+------------------+----------------------------------------------+

Collection of event categories
-------------------------------

To retrieve a collection of event categories you will need to call the /api/v2/eventcategories endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/eventcategories

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

    $ curl http://e-venement.local/api/v2/eventcategories \
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
      "total": 6,
      "_links": {
          "self": {
              "href": "\/api\/v2\/eventcategories?limit=10"
          },
          "first": {
              "href": "\/api\/v2\/eventcategories?limit=10&page=1"
          },
          "last": {
              "href": "\/api\/v2\/eventcategories?limit=10&page=1"
          },
          "next": {
              "href": "\/api\/v2\/eventcategories?limit=10&page=1"
          }
      },
      "_embedded": {
          "items": [
              {
                  "id": 2,
                  "name": "Ateliers th\u00e9matiques"
              },
              {
                  "id": 1,
                  "name": "Caf\u00e9 d'accueil"
              },
              {
                  "id": 4,
                  "name": "D\u00e9jeuners th\u00e9matiques"
              },
              {
                  "id": 6,
                  "name": "Allocution du premier ministre"
              },
              {
                  "id": 7,
                  "name": "Cocktail dinatoire"
              },
              {
                  "id": 3,
                  "name": "S\u00e9ance pl\u00e9ni\u00e8re consacr\u00e9e \u00e0 l'Europe"
              }
          ]
      }
  }

Getting a single event category
--------------------------------

To retrieve the detail of a single event category you will need to call the /api/v2/eventcategories/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/eventcategories/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the event categories                                        |
+---------------+----------------+-------------------------------------------------------------------+

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/eventcategories/4 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

  {
      "id": 4,
      "name": "D\u00e9jeuners th\u00e9matiques"
  }
