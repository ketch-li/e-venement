ProductCategories API
=====================

These endpoints will allow you to manage product categories. Base URI is '/api/v2/productcategories'.

Product API response structure
------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+----------------------------------------------+
| Field            | Description                                  |
+==================+==============================================+
| id               | Id of the product category                   |
+------------------+----------------------------------------------+
| translations     | Collection of translations                   |
+------------------+----------------------------------------------+

Available actions to interact with a product
--------------------------------------------

+------------------+----------------------------------------------+
| Action           | Description                                  |
+==================+==============================================+
| List             | Retrieve a collection of product categories  |
+------------------+----------------------------------------------+

Collection of product categories
--------------------------------

To retrieve a collection of product categories you will need to call the /api/v2/productcategories endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/productcategories

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

    $ curl http://e-venement.local/api/v2/productcategories \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET \

Exemplary Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json


    {
        "page":1,
        "limit":10,
        "pages":21,
        "total":205,
        "_links":{
            "self":{
                 "href":"\/api\/v2\/customers\/?page=1&limit=10"
            },
            "first":{
                 "href":"\/api\/v2\/customers\/?page=1&limit=10"
            },
            "last":{
                 "href":"\/api\/v2\/customers\/?page=21&limit=10"
            },
            "next":{
                 "href":"\/api\/v2\/customers\/?page=2&limit=10"
            }
        },
        "_embedded":{
            "items":[
                {
                    "id":12,
                    "translations":{
                        "en_US":{
                            "id":12,
                            "name":"cups-and-mugs"
                        }
                    }
                },
                {
                    "id":13,
                    "translations":{
                        "en_US":{
                            "id":13,
                            "name":"T-shirts"
                        }
                    }
                }
            ]
        }
    }

