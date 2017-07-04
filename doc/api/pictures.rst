Pictures API
=============

These endpoints will allow you to manage picture. Base URI is '/api/v2/pictures'.

Picture API response structure
-------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+---------------------------------------------------+
| Field            | Description                                       |
+==================+===================================================+
| id               | Id of the picture                                 |
+------------------+---------------------------------------------------+
| type             | Type of the picture                               |
+------------------+---------------------------------------------------+
| name             | Name of the picture                               |
+------------------+---------------------------------------------------+
| width            | Width of the picture                              |
+------------------+---------------------------------------------------+
| height           | Height of the picture                             |
+------------------+---------------------------------------------------+

Available actions to interact with a promotion
----------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of pictures            |
+------------------+----------------------------------------------+
| Show             | Getting a single picture                     |
+------------------+----------------------------------------------+
| Create           | Create a picture                             |
+------------------+----------------------------------------------+

Collection of pictures
-----------------------

To retrieve a collection of pictures you will need to call the /api/v2/pictures endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/pictures

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

    $ curl http://e-venement.local/api/v2/pictures \
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
    "total": 2,
    "_links": {
        "self": {
            "href": "\/api\/v2\/pictures?limit=10"
        },
        "first": {
            "href": "\/api\/v2\/pictures?limit=10&page=1"
        },
        "last": {
            "href": "\/api\/v2\/pictures?limit=10&page=1"
        },
        "next": {
            "href": "\/api\/v2\/pictures?limit=10&page=1"
        }
    },
    "_embedded": {
        "items": [
            {
                "id": 1,
                "name": "wallpaper672898.jpeg",
                "type": "image\/jpeg",
                "width": 200,
                "height": 300
            },
            {
                "id": 2,
                "name": "wallpaper.png",
                "type": "image\/png",
                "width": 200,
                "height": 300
            },
        ]
    }
}

Getting a single picture
-------------------------

To retrieve the content of a single picture you will need to call the /api/v2/pictures/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/pictures/{id}

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| id            | query          | Id of the promotion                                               |
+---------------+----------------+-------------------------------------------------------------------+


Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/pictures/628 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

Create a picture
-----------------

To create a picture you will need to call the /api/v2/pictures endpoint with the POST method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/pictures

+---------------+----------------+-------------------------------------------------------------------+
| Parameter     | Parameter type | Description                                                       |
+===============+================+===================================================================+
| Authorization | header         | Token received during authentication                              |
+---------------+----------------+-------------------------------------------------------------------+
| type          | request        | Type of the picture                                               |
+---------------+------------------------------------------------------------------------------------+
| name          | request        | Name of the picture                                               |
+---------------+------------------------------------------------------------------------------------+
| content       | request        | Content of the picture, base64 encoded                            |
+---------------+------------------------------------------------------------------------------------+


Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/pictures \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK
