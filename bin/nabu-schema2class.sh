#!/bin/sh
echo ========================================================================
echo nabu-3 -  Export Schema
echo ========================================================================
echo Copyright 2009-2011 Rafael Gutierrez Martinez
echo Copyright 2012-2013 Welma WEB MKT LABS, S.L.
echo Copyright 2014-2016 Where Ideas Simply Come True, S.L.
echo
echo Licensed under the Apache License, Version 2.0 \(the "License"\);
echo you may not use this file except in compliance with the License.
echo You may obtain a copy of the License at
echo
echo     http://www.apache.org/licenses/LICENSE-2.0
echo
echo Unless required by applicable law or agreed to in writing, software
echo distributed under the License is distributed on an \"AS IS\" BASIS,
echo WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
echo See the License for the specific language governing permissions and
echo limitations under the License.
echo ========================================================================
echo

# This variable defines the path for config files. You can change this value.
# When nabu-3 install script runs, he creates this path if not exists.
SCRIPT_PATH=`realpath $0`
SCRIPT_PATH=`dirname ${SCRIPT_PATH}`

if [ -f ${SCRIPT_PATH}/../etc/nabu-3.conf ] ; then
    source ${SCRIPT_PATH}/../etc/nabu-3.conf
else
    INSTALL_PATH=`pwd`/${0}
    INSTALL_PATH=`dirname ${INSTALL_PATH}`
    if [ -f ${INSTALL_PATH}/../etc/nabu-3.conf ] ; then
        source ${INSTALL_PATH}/../etc/nabu-3.conf
    else
        echo Config file not found
        exit 1
    fi
fi

if [ -f ${SCRIPT_PATH}/inc/schema2class.php ] ; then
    php ${PHP_PARAMS} ${SCRIPT_PATH}/inc/schema2class.php "$@"
else
    echo Execution error: schema2class.php script not found.
fi
