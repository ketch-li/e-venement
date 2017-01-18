#!/bin/bash

#**********************************************************************************
#
#           This file is part of e-venement.
# 
#    e-venement is free software; you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation; either version 2 of the License.
# 
#    e-venement is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
# 
#    You should have received a copy of the GNU General Public License
#    along with e-venement; if not, write to the Free Software
#    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
# 
#    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
#    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
# 
#**********************************************************************************/

  git submodule init
  git submodule update
  for elt in lib/vendor/externals/*; do
    (cd $elt; git checkout -f origin/master; git pull origin master && git checkout master && git pull)
  done
  for elt in `find lib/vendor/externals/ -type d`; do chmod -R a+rx $elt; done
  echo "If you had permissions errors previously, it probably means that you are not the file owner. Please execute 'sudo for elt in `find lib/vendor/externals/ -type d`; do chmod -R a+rx $elt; done'"
