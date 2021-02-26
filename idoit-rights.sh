#!/bin/bash
##
## This file is a component of i-doit
## http://www.i-doit.org
##
## Author:  dennis stuecken <dstuecken@i-doit.org>
## License: http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
## Copyright 2004-2008 - synetics GmbH
##
## --
## You may have to configure this tool to match your system environment
## ---

## ---------------
SU_CMD="sudo"          # check if sudo is installed on your system or change to an equivalent
## ---------------

## ----------------
FILE_RIGHTS="664"
DIR_RIGHTS="775"
## ----------------

echo "-------------------------------------------"
echo "--    i-doit - http://www.i-doit.org     --"
echo "--               -----                   --"
echo "--           Rights Manager              --"
echo "--               -----                   --"
echo "--  Note: You may need to type in your   --"
echo "--        root password on first action  --"
echo "-------------------------------------------"

if [ -n $1 ]; then
  echo "[*] usage: idoit-rights.sh set   - sets write permissions to the i-doit directory."
  echo "                                   this is usefull for updating with the automatic update procedure"
  echo "                                   in http://your-ip/i-doit-path/updates/."
  echo "           idoit-rights.sh unset - removes these permissions and sets write permissions"
  echo "                                   to the i-doit temp directories only. This is also usefull"
  echo "                                   before the web-installation."
  echo ""
fi;

if [ "$1" = "set" ]; then
  echo "-----------------------------------------"
  echo "[*] Changing write permissions recursively on `pwd`"
  $SU_CMD chmod -R 777 .
  echo "[*] Done"
  echo "-----------------------------------------"
  echo ""
  echo "[i] You can start the i-doit update procedure now"
  echo "[i] For security reasons, don't forget to unset these permissions with"
  echo "[i] \"idoit-rights.sh unset\" after a successfull update"
  echo ""
fi;

if [ "$1" != "set" ]; then
  echo "-----------------------------------------"
  echo "[*] Changing permissions back to default "
  $SU_CMD  find . -type f -exec chmod "$FILE_RIGHTS" "{}" \;
  $SU_CMD  find . -type d -exec chmod "$DIR_RIGHTS" "{}" \;
  echo "[*] Setting execution rights for scripts"
  $SU_CMD chmod +x 	controller \
                    console.php \
                    idoit-rights.sh \

  if [ -d "imports/scripts" ]; then
  	$SU_CMD chmod -R +x imports/scripts
  fi

  if [ -d "imports/qcw" ]; then
  	$SU_CMD chmod -R "$DIR_RIGHTS" imports/qcw
  fi

  echo "[*] Setting up temp rights"
  $SU_CMD chmod -R "$DIR_RIGHTS"	temp \
                        src/ \
                        log/ \
                        updates/versions \
                        imports/ \
                        upload/files \
                        upload/fonts \
                        upload/images

  $SU_CMD chmod -R "$FILE_RIGHTS" src/config.inc.php

  echo "[*] Deleting temp files"

  if [ $(ls -1 temp/ | wc -l) -gt 0 ]; then
    for file in $(ls temp/)
      do
      if [ -f $file ]; then
      	rm -R $file
      fi
    done
  fi

  echo "[*] Done"
  echo "-----------------------------------------"
  echo ""
fi;

echo "[*] Exiting.."
