#!/bin/bash

## This file is a component of the i-doit framework
## Website: http://www.i-doit.org/
## Licence: http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
## Copyright: synetics GmbH

# Edit these variables to fit your environment

idoit_login="admin"                     # i-doit login user
idoit_pass="admin"                      # i-doit password
idoit_tenant=1                          # i-doit tenant id
idoit_language=3                        # Language of the export? 1=All 2=German 3=English
idoit_structure=3                       # Export i-doit locations to Check_MK? 0=No 1=Physical 2=Logical 3=ObjectType
checkmk_export_path="checkmk_export"    # Check_MK Export path as defined in the i-doit system settings, relative to the idoit_base path
checkmk_version=""                      # Please enter you Check_MK version like "1.2" or "1.4".
omd_site="idoit"                        # Name of the target OMD site
omd_host="1.2.3.4"                      # IP-Address or DNS of the Check_MK host
is_configured=0                         # Set value to 1 to enable transfer scriptt

if [ ${is_configured} == 0 ]; then
	echo "Error: Please configure your transfer script in order to use it properly."
	exit 0;
fi

# System variables, only change when necessary

tmp=/tmp
idoit_base=$(dirname $0)                # Webserver base path for i-doit without trailing slash
scp=$(which scp)
ssh=$(which ssh)
md5sum=$(which md5sum)
php=$(which php)
find=$(which find)
cat=$(which cat)
grep=$(which grep)
xargs=$(which xargs)
awk=$(which awk)
import="\{\'need_restart\'\: True, \'need_sync\'\: True, \'user_id\'\: u\'cmkadmin\', \'time\'\: -3600, \'domains\'\: \[\'check_mk\'\], \'text\'\: u\'I-Doit Export aktualisierung\', \'object\'\: \(\'CREFolder\', \'\'\), \'id\'\: \'0\', \'action_name\'\: \'\'\}"


if [ $checkmk_version = "1.4" ];then
###### For Check_MK version 1.4 ######
md5_check=1

if [ $# -eq 0 ]; then
	md5_check=1
else
	if [ $1 == "--force" ]; then
		echo "Forcing the transfer"
		md5_check=0
	fi
fi

# Export the config
${php} ${idoit_base}/console.php check_mk-export -v -u ${idoit_login} -p ${idoit_pass} -i ${idoit_tenant} -l ${idoit_language} -x ${idoit_structure} > temp/check-mk-transfer.log

# Initialize result
md5_result=1

if [ ${md5_check} == 1 ]; then

	if [ -f "${tmp}/${checkmk_export_path}.md5" ]; then

		md5_old=`${cat} ${tmp}/${checkmk_export_path}.md5 |${awk} '{print $1}'`

		if [ -d "${idoit_base}/${checkmk_export_path}/" ]; then

			md5_act=`${find} ${idoit_base}/${checkmk_export_path}/ -type f|${grep} -v \.zip |${grep} -v \.tar\.gz |${grep} -v \.tar |${xargs} cat |${md5sum} |${awk} '{print $1}'`

			if [ "${md5_act}" != "" ]; then

				echo ${md5_act} > ${tmp}/${checkmk_export_path}.md5

				if [ "${md5_act}" == "${md5_old}" ]; then
					md5_result=0
				else
					md5_result=1
				fi

			else

				echo "Error: Could not create MD5 Checksum of Check-MK exports. You need to export the Check-MK configuration first."
				exit 0;

			fi

		else

			echo "Error: Check-MK export path ${idoit_base}/${checkmk_export_path}/ does not exist. Please create it and start the Check-MK config file export."

			exit 0;

		fi

	fi

fi


if ${ssh} ${omd_site}@${omd_host} "test -s ~/var/check_mk/wato/replication_changes_${omd_site}.mk"; then

	echo "Error: Not all changes are done, please check your CheckMK-Site for not done changes!"

else
	if [ ${md5_result} == 1 ]; then
		if [ -f "${idoit_base}/${checkmk_export_path}/${checkmk_export_path}.zip" ]; then

		${scp} ${idoit_base}/${checkmk_export_path}/${checkmk_export_path}.zip ${omd_site}@${omd_host}:/omd/sites/${omd_site}/etc/check_mk/conf.d/wato
		${ssh} ${omd_site}@${omd_host} "cd etc/check_mk/conf.d/wato/ ;rm -rf ${checkmk_export_path}/* >/dev/null 2>&1;mkdir ${checkmk_export_path} >/dev/null 2>&1; unzip -d ${checkmk_export_path} ${checkmk_export_path}.zip && rm -f ${checkmk_export_path}.zip && mv ${checkmk_export_path}/idoit_hosttags.mk ~/etc/check_mk/multisite.d/"
		${ssh} ${omd_site}@${omd_host} "echo ${import} >> ~/var/check_mk/wato/replication_changes_${omd_site}.mk"
		echo "No errors during transfer!"

		else

		echo "Error: Check-MK export file ${checkmk_export_path}.zip does not exist. Have you configured your 'checkmk_export_path' correctly?"

		fi
	else
	echo Nothing changed.
	fi
fi
else
##### For other Check_MK versions than 1.4 #####
md5_check=1

if [ $# -eq 0 ]; then
	md5_check=1
else
	if [ $1 == "--force" ]; then
		echo "Forcing the transfer"
		md5_check=0
	fi
fi

# Export the config
${php} ${idoit_base}/console.php check_mk-export -v -u ${idoit_login} -p ${idoit_pass} -i ${idoit_tenant} -l ${idoit_language} -x ${idoit_structure} > temp/check-mk-transfer.log

# Initialize result
md5_result=1

if [ ${md5_check} == 1 ]; then

	if [ -f "${tmp}/${checkmk_export_path}.md5" ]; then

		md5_old=`${cat} ${tmp}/${checkmk_export_path}.md5 |${awk} '{print $1}'`

		if [ -d "${idoit_base}/${checkmk_export_path}/" ]; then

			md5_act=`${find} ${idoit_base}/${checkmk_export_path}/ -type f|${grep} -v \.zip |${grep} -v \.tar\.gz |${grep} -v \.tar |${xargs} cat |${md5sum} |${awk} '{print $1}'`

			if [ "${md5_act}" != "" ]; then

				echo ${md5_act} > ${tmp}/${checkmk_export_path}.md5

				if [ "${md5_act}" == "${md5_old}" ]; then
					md5_result=0
				else
					md5_result=1
				fi

			else

				echo "Error: Could not create MD5 Checksum of Check-MK exports. You need to export the Check-MK configuration first."
				exit 0;

			fi

		else

			echo "Error: Check-MK export path ${idoit_base}/${checkmk_export_path}/ does not exist. Please create it and start the Check-MK config file export."

			exit 0;

		fi

	fi

fi

if [ ${md5_result} == 1 ]; then
	if [ -f "${idoit_base}/${checkmk_export_path}/${checkmk_export_path}.zip" ]; then

		${scp} ${idoit_base}/${checkmk_export_path}/${checkmk_export_path}.zip ${omd_site}@${omd_host}:/omd/sites/${omd_site}/etc/check_mk/conf.d/wato
		${ssh} ${omd_site}@${omd_host} "cd etc/check_mk/conf.d/wato/ ;rm -rf ${checkmk_export_path}/* >/dev/null 2>&1;mkdir ${checkmk_export_path} >/dev/null 2>&1; unzip -d ${checkmk_export_path} ${checkmk_export_path}.zip && rm -f ${checkmk_export_path}.zip && mv ${checkmk_export_path}/idoit_hosttags.mk ~/etc/check_mk/multisite.d/ && echo $(date +%s) - i-doit i-doit i-doit Export Konfiguration aktualisiert >> ~/var/check_mk/wato/log/pending.log"

		echo "No errors during transfer!"

	else

		echo "Error: Check-MK export file ${checkmk_export_path}.zip does not exist. Have you configured your 'checkmk_export_path' correctly?"

	fi
else
	echo Nothing changed.
fi
fi