#!/bin/bash

read -p "Do you want to update your french geographical data, with departements & regions? [Y/n] " geo
if [ "$geo" != 'n' ]
then
  echo 'DELETE FROM geo_fr_department' | bin/db-connect.sh
  echo 'DELETE FROM geo_fr_region' | bin/db-connect.sh
  ./symfony doctrine:data-load --append data/fixtures/50-geo-fr-dpt+regions.yml
fi
