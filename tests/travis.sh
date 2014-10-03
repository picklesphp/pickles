#!/bin/sh

VERSION=`phpenv version-name`

if [ "$VERSION" -eq "hhvm" ]
then
    PHPINI=/etc/hhvm/php.ini
else
    PHPINI="~/.phpenv/versions/$VERSION/etc/php.ini"
fi

echo "extension = memcache.so"  >> $PHPINI
echo "extension = memcached.so" >> $PHPINI
echo "extension = redis.so"     >> $PHPINI
