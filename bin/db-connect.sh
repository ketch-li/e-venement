#!/bin/bash

LINE=1
TMP=1;
[ "$1" != "" ] && TMP="$1"
[ "$TMP" -gt 1 ] &> /dev/null && LINE=$TMP

# user
echo $PGUSER;
[ -z "$PGUSER" ] && \
export PGUSER=`grep username config/databases.yml | grep -v '^#' | sed "s/\s*username:[^a-Z]*\(\w*\)$/\1/g" | head -n$LINE | tail -n1`

# passwd
[ -z "$PGPASSWORD" ] && \
export PGPASSWORD=`grep password config/databases.yml | grep -v '^#' | sed "s/\s*password:[^a-Z^0-9]*\(.*\)$/\1/g" | head -n$LINE | tail -n1`

# host & db
CONN=`grep dsn config/databases.yml | grep -v '^#' | sed "s/\s*dsn:\s*'\(.*\):host=\(.*\);dbname=\(\w*\).*/\1:\2:\3/g" | head -n$LINE | tail -n1`
if [ "`echo $CONN | cut -d : -f 1`" = 'pgsql' ]; then
  CMD=psql
fi

# host
export PGHOST=`echo $CONN | cut -d : -f 2`

# db
export PGDATABASE=`echo $CONN | cut -d : -f 3`

echo "+------------------------------------------------+"
echo "| Host    : $PGHOST"
echo "| Database: $PGDATABASE"
echo "| User    : $PGUSER"
echo "+------------------------------------------------+"
echo ""
psql
