#!/bin/bash
# author dennis stucken <dstuecken@i-doit.org>

host="localhost"
port="3306"
mysql="`which mysql`"
mysqldump="`which mysqldump`"

PHP=`which php`
REVISION=`${PHP} --run 'include("../src/version.inc.php"); echo(\$g_product_info["version"]);'`

idoit_data="idoit_data_install"
idoit_system="idoit_system_install"

while getopts "m:s:p:" opt; do
	case $opt in
		m)
			idoit_data=$OPTARG
			;;
		s)
			idoit_system=$OPTARG
			;;
		p)
			pass=$OPTARG
			;;
		\?)
			echo "Invalid option: -$OPTARG" >&2
			;;
    esac
done

echo "i-doit install dump creation"

until [ -n "$pass" ]; do
	echo ""
	echo "MySQL root password: []: "
	
	stty -echo
	read pass
	stty echo
	
	if [ -z "$pass" ]; then
		pass="^^"
		continue
	fi
done

if [ "$pass" = "^^" ]; then
	unset pass
fi

echo ""
echo ""
echo "Dumping..."
echo ""

if [ "$pass" = "" ]; then
	PASSARGUMENT=" "
else
	PASSARGUMENT="--password=${pass} "
fi

SQLDUMP="$mysqldump --default-character-set=utf8 -uroot ${PASSARGUMENT} -h $host -P $port --compact --skip-extended-insert -C -n -q --dump-date "

SQLEXECSYS="$mysql -uroot ${PASSARGUMENT} -h $host -P $port -D${idoit_system} -N -e"
SQLEXECDATA="$mysql -uroot ${PASSARGUMENT} -h $host -P $port -D${idoit_data} -N -e"

$SQLEXECSYS "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE isys_licence;" > /dev/null 2>&1
$SQLEXECSYS "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE isys_mandator;" > /dev/null 2>&1

$SQLEXECDATA "UPDATE isys_cats_person_list SET isys_cats_person_list__last_login = NULL;" > /dev/null 2>&1
$SQLEXECDATA "TRUNCATE TABLE temp_obj_data;"> /dev/null 2>&1
$SQLEXECDATA "TRUNCATE TABLE isys_user_session;"> /dev/null 2>&1
$SQLEXECDATA "TRUNCATE TABLE isys_cache_qinfo;"> /dev/null 2>&1
$SQLEXECDATA "TRUNCATE TABLE isys_summary;"> /dev/null 2>&1
#$SQLEXECDATA "TRUNCATE TABLE isys_property_2_cat;"> /dev/null 2>&1

echo "--" > sql/idoit_system.sql
echo "-- i-doit system dump for version $REVISION" >> sql/idoit_system.sql
echo "-- created with: `mysqldump --version`" >> sql/idoit_system.sql
echo "-- at: `date`" >> sql/idoit_system.sql
echo "--" >> sql/idoit_system.sql
echo "-- For manual installations you need to insert your tenant connection info into isys_mandator in order to connect to a tenant." >> sql/idoit_system.sql
echo "--" >> sql/idoit_system.sql
echo "-- INSERT INTO isys_mandator SET" >> sql/idoit_system.sql
echo "--   isys_mandator__title = 'Mandator (DE)'," >> sql/idoit_system.sql
echo "--   isys_mandator__dir_cache = 'cache_mandator'," >> sql/idoit_system.sql
echo "--   isys_mandator__dir_tpl = 'default'," >> sql/idoit_system.sql
echo "--   isys_mandator__db_host = 'localhost'," >> sql/idoit_system.sql
echo "--   isys_mandator__db_port = 3306," >> sql/idoit_system.sql
echo "--   isys_mandator__db_name = 'idoit_data'," >> sql/idoit_system.sql
echo "--   isys_mandator__db_user = 'idoit'," >> sql/idoit_system.sql
echo "--   isys_mandator__db_pass = 'idoit'," >> sql/idoit_system.sql
echo "--   isys_mandator__active = 1;" >> sql/idoit_system.sql
echo "--" >> sql/idoit_system.sql

echo "" >> sql/idoit_system.sql
echo "SET FOREIGN_KEY_CHECKS=0;" >> sql/idoit_system.sql
echo "" >> sql/idoit_system.sql

$SQLDUMP ${idoit_system} 1>>sql/idoit_system.sql 2>/dev/null

echo "--" > sql/idoit_data.sql
echo "-- i-doit data dump for version $REVISION" >> sql/idoit_data.sql
echo "-- created with: `mysqldump --version`" >> sql/idoit_data.sql
echo "-- at: `date`" >> sql/idoit_data.sql
echo "--" >> sql/idoit_data.sql

echo "" >> sql/idoit_data.sql
echo "SET FOREIGN_KEY_CHECKS=0;" >> sql/idoit_data.sql
echo "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";" >> sql/idoit_data.sql
echo "" >> sql/idoit_data.sql

$SQLDUMP ${idoit_data} 1>>sql/idoit_data.sql 2>/dev/null
