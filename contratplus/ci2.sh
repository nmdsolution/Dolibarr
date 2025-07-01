#!/bin/bash

# Copyright (C) 2019	David MOYON <david@code42.fr>
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

echo "Détection du module"
MODULE_NAME=$(grep -Po '(?<=(\$this->rights_class = ''))(\w*)(\W*)(\w*)(?=(\W*))' core/modules/*.class.php | tr -d \')
MODULE_VERSION=$(grep -Po '(?<=(\$this->version =\W.))([\d\.]+)' core/modules/*.class.php)

HTDOCS_PATH="BUILD/htdocs/$MODULE_NAME"
BUILD_PATH="BUILD/build"

PATH_TO_CLEAN=($HTDOCS_PATH/.git $HTDOCS_PATH/BUILD $HTDOCS_PATH/$MODULE_NAME $HTDOCS_PATH/ci.sh)


echo -e "\tNom du module : $MODULE_NAME"
echo -e "\tVersion : $MODULE_VERSION"
echo ''

echo -n "Nettoyage des anciens builds"
rm -rf BUILD
rm -rf module_$MODULE_NAME-$MODULE_VERSION.zip
echo -n -e "\t\t[OK]\n"
echo ''

echo -n "Création du repertoire de travail"
mkdir -p $HTDOCS_PATH
mkdir -p $BUILD_PATH
echo -n -e "\t[OK]\n"
#echo -e "\tRépertoire de travail : $HTDOCS_PATH"
echo ''

echo "Création de la liste des fichiers du module"
find . -not -path '*/\.*' -not -path "*BUILD*" | sed 's/^..//' |
while read filename
do
    if [ -d $filename ]
    then
    #echo "Création du repertoire : $filename dans $HTDOCS_PATH/$filename"
    echo -n '#'
        mkdir -p $HTDOCS_PATH/$filename
    else
    #echo "Copie du fichier : $filename dans $HTDOCS_PATH/$filename"
        cp $filename $HTDOCS_PATH/$filename
    echo -n '.'
    fi
done
echo -e "\n"
echo "Nettoyage avant la compression"

for item in ${PATH_TO_CLEAN[*]}
do
    rm -rf $item
    #if [ $? -eq 0 ]; then echo -e "\t $item : [OK]"; else echo -e "\t $item : \t [KO]"; fi
    if [ $? -eq 0 ]; then printf "%40s \t%s\n" $item "[OK]"; fi
    #printf "%40s \t%s\n" $item "[OK]";
done


echo "Création de l'archive ZIP du module"
echo -e "Fichier : module_$MODULE_NAME-$MODULE_VERSION.zip"

cd BUILD && zip -r ../module_$MODULE_NAME-$MODULE_VERSION.zip . -q > /dev/null && cd ..
