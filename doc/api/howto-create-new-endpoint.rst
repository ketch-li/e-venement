How to Create a new endpoint for the API ?
==========================================

1. Create the documentation as it has been done for other endpoints
2. Create a controler (in ```modules/```)
3. Create a service (in ```lib/services/```)
4. Declare this service (in ```config/services.yml```)
5. Declare your routes (in ```config/routing.yml```)
6. Enable your module in the config/extra-modules.php of your targeted app
7. Test it
8. Create a pull-request for your contribution if everything went good
