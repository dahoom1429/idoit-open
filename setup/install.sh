#!/bin/bash
# author dennis stücken <dstuecken@i-doit.org>

db_user="root"
host="localhost"
port="3306"
sed=`which sed`
pwd=`pwd`
base=`dirname ${pwd}/ | sed -e 's/\//\\\\\//g'`
admincenterpw=""
pass=""
chash=""
adminpwhash=""

usage()
{
	cat << EOF
i-doit command line installer

Usage: $0 options
Example: $0 -m idoit_data_install -s idoit_system_install -n "install-test"

OPTIONS:
   -d  db_suffix
   -n  mandator name
   -s  system database name
   -m  mandator database name
   -h  mysql host (default: localhost)
   -u  mysql user (default: root)
   -p  mysql root password
   -P  mysql port (default: 3306)
   -a  set admin center password (default: empty)
   -r  revert (uninstall)
   -U  help
   -q  quiet mode
EOF
}

# initial config
REVERT="0"
QUIET="0"

while getopts ":d:m:s:n:p:P:a:h:u:Urq" opt; do
	case $opt in
	    d)
			db_suffix=$OPTARG
			;;
		m)
			idoit_data=$OPTARG
			;;
		s)
			idoit_system=$OPTARG
			;;
		n)
			mandator_name=$OPTARG
			;;
		a)
			admincenterpw=$OPTARG
			;;
		h)
			host=$OPTARG
			;;
		p)
			pass=$OPTARG
			;;
		P)
			port=$OPTARG
			;;
		q)
			QUIET="1"
			;;
		r)
			REVERT="1"
			;;
		u)
			db_user=$OPTARG
			;;
		U)
			usage
			exit 0
			;;
		:)
			echo
			echo
			echo "Missing option argument for -$OPTARG" >&2
			echo
			echo
			usage
			exit 1
			;;
		\?)
			echo "Invalid option: -$OPTARG" >&2
			;;
    esac
done

if [ "$REVERT" = "1" ]; then
	# REVERT:
	mysql -u"$db_user" -p"$pass" -e "DROP DATABASE ${idoit_data};" 2>/dev/null
	mysql -u"$db_user" -p"$pass" -e "DROP DATABASE ${idoit_system};" 2>/dev/null
	echo "Databases ${idoit_data} and ${idoit_system} dropped."

	exit 0;
fi

if [ "$QUIET" = "0" ]; then
	echo "Welcome to the i-doit command line setup."
	echo "Specify your configuration:"
fi

if [ "`which php 2>/dev/null`" ]; then
	php="`which php`"
else
	echo "Could not autodetect php executable location."
	echo "Enter your path to the php binary manually."
	until [ -n "$php" ]; do
		echo -n "Path: [/usr/bin/php]: "
		read php
		if [ -z "$php" ]; then
			php="/usr/bin/php"
			if [ ! -e "$php" ]; then
				echo "PHP does not exist in $php."
				unset php
			fi
			continue
		fi
	done

	if [ ! -e "$php" ]; then
		 echo "PHP does not exist in $php."
		 exit 0
	fi
fi

if [ "`which mysql 2>/dev/null`" ]; then
	mysql="`which mysql`"
else
	echo "Could not autodetect mysql client location."
	echo "Enter your path to the mysql binary manually."
	until [ -n "$mysql" ]; do
		echo -n "Path: [/usr/bin/mysql]: "
		read mysql
		if [ -z "$mysql" ]; then
			mysql="/usr/bin/mysql"
			if [ ! -e "$mysql" ]; then
				echo "mysql does not exist in $mysql."
				unset mysql
			fi
			continue
		fi
	done

	if [ ! -e "$mysql" ]; then
		 echo "The mysql client does not exist in $mysql."
		 exit 0
	fi
fi

if [ "$QUIET" = "0" ]; then
	echo ""
fi

until [ -n "$idoit_data" ]; do
	echo -n "Mandator Database: [idoit_data${db_suffix}]: "
	read idoit_data
	if [ -z "$idoit_data" ]; then
		idoit_data="idoit_data${db_suffix}"
		continue
	fi
done

if [ "$QUIET" = "0" ]; then
	echo ""
fi

until [ -n "$idoit_system" ]; do
	echo -n "System Database: [idoit_system${db_suffix}]: "
	read idoit_system
	if [ -z "$idoit_system" ]; then
		idoit_system="idoit_system${db_suffix}"
		continue
	fi
done

if [ "$QUIET" = "0" ]; then
	echo ""
fi

until [ -n "$mandator_name" ]; do
	echo -n "Mandator name: [synetics gmbh]: "
	read mandator_name
	if [ -z "$mandator_name" ]; then
		mandator_name="synetics gmbh"
		continue
	fi
done

if [ "$QUIET" = "0" ]; then
	until [ -n "$pass" ]; do
		echo -n "MySQL root password: []: "

		stty -echo
		read pass
		stty echo

		if [ -z "$pass" ]; then
			pass="^^"
			continue
		fi
	done
fi

if [ "$pass" = "^^" ]; then
	unset pass
fi

if [ "$QUIET" = "0" ]; then
	echo ""
	echo "i-doit will be installed using the following databases: "
	echo "Mandator: ${idoit_data}, System: ${idoit_system}"
	echo "MySQL host: ${host}:${port}"
	echo ""

	until [ -n "$go" ]; do
	  echo -n "Continue? [Y]es [N]o: "
	  read go
	  case $go in
	    [Nn])
	      exit 0
	      continue
	      ;;
	    [Yy])
	      echo " "
	      go=n
	      ;;
	    *)
	     unset go
	     continue
	     ;;
	  esac
	done
	unset go

	echo "Installing..."
	echo ""
fi

if [ "$pass" = "" ]; then
	PASSARGUMENT=" "
else
	PASSARGUMENT="--password=$pass "
fi

SQLEXEC="$mysql -u"$db_user" ${PASSARGUMENT}-h $host -P $port -N -e"
SQL="$mysql --default-character-set=utf8 -u"$db_user" ${PASSARGUMENT}-h $host -P $port -D"

$SQLEXEC "CREATE DATABASE $idoit_data DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;" 2>/dev/null
$SQL $idoit_data < sql/idoit_data.sql 2>/dev/null

$SQLEXEC "CREATE DATABASE $idoit_system DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;" 2>/dev/null
$SQL $idoit_system < sql/idoit_system.sql 2>/dev/null

if [ "$QUIET" = "0" ]; then
	echo "Databases installed. Creating mandator entries.."
fi

# ID-6814 Using 'REPLACE INTO isys_mandator SET ...' syntax, to prevent column mismatch errors.
$SQL ${idoit_system} -e "REPLACE INTO isys_mandator SET
    isys_mandator__id = 1,
    isys_mandator__title = '${mandator_name}',
    isys_mandator__description = '${mandator_name}',
    isys_mandator__dir_cache = 'cache_${mandator_name}',
    isys_mandator__dir_tpl = 'default',
    isys_mandator__db_host = '${host}',
    isys_mandator__db_port = '${port}',
    isys_mandator__db_name = '${idoit_data}',
    isys_mandator__db_user = '${db_user}',
    isys_mandator__db_pass = '${pass}',
    isys_mandator__apikey = '',
    isys_mandator__sort = 1,
    isys_mandator__active = 1,
    isys_mandator__license_objects = 0;"

# Creating Crypto Hash
chash=`$php -r "echo @sha1(@uniqid('', true));"`
# Creating Admin Password Hash
adminpwhash=$(echo "<?php global \$argv;if (!isset(\$argv[1]) || empty(\$argv[1])) { echo ''; die; }include_once __DIR__ . '/../src/idoit/Component/Security/Hash/Password.php';\$instance = \idoit\Component\Security\Hash\Password::instance();\$instance->setPassword(\$argv[1]);\$instance->hash();echo str_replace(['$', '.', '*', '/', '[', ']', '^'], ['\$', '\.', '\*', '\/', '\[', '\]', '\^'], \$instance->getHash());" > generatePassword.php && php generatePassword.php $admincenterpw && rm generatePassword.php)

if [ "$QUIET" = "0" ]; then
	if [ -f ../src/config.inc.php ]; then
		echo "Old configuration file found. Moving to ../src/config.inc.php.old"
		mv -f ../src/config.inc.php ../src/config.inc.php.old
	fi
	echo "Creating config.."
fi

$sed 	-e 's/%config.adminauth.username%/admin/g' \
		-e "s/%config.adminauth.password%/${adminpwhash}/g" \
		-e "s/%config.db.host%/${host}/g" \
		-e "s/%config.db.port%/${port}/g" \
		-e 's/%config.db.username%/root/g' \
		-e "s/%config.db.password%/${pass}/g" \
		-e "s/%config.db.name%/${idoit_system}/g" \
		-e "s/%config.crypt.hash%/${chash}/g" \
		-e "s/%config.admin.disable_addon_upload%/0/g" \
		config_template.inc.php > ../src/config.inc.php

if [ "$QUIET" = "0" ]; then
	echo "Finished. If you have no errors above, the setup is complete."
	echo "You may want to take a look at the auto-generated config.inc.php in ../src/ to check if everything is correct"
	echo ""
	echo "Then you can login to i-doit using the default login: "
	echo "admin/admin"
fi
exit 0
