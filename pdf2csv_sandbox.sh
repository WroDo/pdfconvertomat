#!/bin/bash
#set -x
#
# https://lc-wiki.intern.hcsn.de/wiki/PDF_in_Excel_konvertieren#Vorschlag_2:_pdftotext
#
#

GFILEIN="${1}" # Some textfile, created with SumatraPDF (saving as TXT)
GFILEOUT="`echo \"${GFILEIN}\" | rev | cut -d. -f2- | rev`.tsv"
GFILETMP="`echo \"${GFILEIN}\" | rev | cut -d. -f2- | rev`.tmp"
#GFIRSTROW="${2}" # First Row with left-most header, useful if textfile contains rubbish at beginning
GCOLUMNCOUNT="${3}" # Anzahl der Spalten im PDF/TXT/CSV
GBASENAME="`basename \"${0}\"`"
GBASENAMENOSUFFIX="`echo \"${GBASENAME}\" | rev | cut -d. -f2 | rev`"
GSEPARATOR=";"

if [ -z "${GFILEIN}" -a -z "${GFIRSTROW}" -a -z "${GCOLUMNCOUNT}" ]; then
	echo "Usage:"
	echo ""
	echo "${GBASENAME} INFILE"
	echo ""
#	echo "Example:"
#	echo
#	#echo "${GBASENAME} ~h/Cloud/Downloads/AOP-Rechnungslauf\ 47409-20231106.txt 18 6"
#	echo "${GBASENAME} ~h/Cloud/Downloads/AOP-Rechnungslauf\ 47409-20231106.txt 23 6 > /home/h/Downloads/AOP-Rechnungslauf\ 47409-20231106.csv"
	exit 1
fi

# Neuer Ansatz mit pdftotext
pdftotext -tsv "${GFILEIN}" "${GFILETMP}"

# Finde die Anzahl der Seiten
GPAGENUMBERS="`cat \"${GFILETMP}\" | awk '{print $2}' | sort -nu | grep -v page_num`"
echo "Found this pages:"
echo "${GPAGENUMBERS}"

# Finde die Zeilen (Y)
#grep '###LINE###' "${GFILETMP}"
# Ich glaube,da sind andere Sprachen besser geeignet

# Mach das mal besser in PHP...
./${GBASENAMENOSUFFIX}.php "${GFILETMP}" "${GFILEOUT}"
libreoffice "${GFILEOUT}"
#./${GBASENAMENOSUFFIX}.php "${GFILETMP}" -



# Cleanup
#test -f "${GFILETMP}" && rm "${GFILETMP}"

exit 0


#EOF
