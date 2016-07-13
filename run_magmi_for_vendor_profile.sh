#!/bin/bash
MAGMI_PATH = "/up/magmi/web/magmi.cli.php"
IMOPRT_DIRECTORY="/home/www.wypo.cl/html/up/magmi/import/"
PROCESSED_DIRECTORY="/home/www.wypo.cl/html/up/magmi/import/processed/"

for file in $IMOPRT_DIRECTORY/*.csv; 
do	
	file_name = $(basename $file)
	PROCESSED_FILE =  $PROCESSED_DIRECTORY.$file_name
	udropship_vendor =  "$file_name" | grep -Po '(?<=(_)).*(?=.csv)'
	magmi_profile =  "$file_name" | grep -Po '(?<=(_)).*(?=.csv)'		
	if [ -f "$PROCESSED_FILE" ]
	then
		file1=`md5 $file`				
		file2=`md5 $PROCESSED_FILE`
		if [ "$file1" = "$file2" ]
		then
			echo "Process already run"
		else	
			# php $MAGMI_PATH -profile=$magmi_profile -mode=update -CSV:filename="$file"			
			# cp -R $file $PROCESSED_FILE
		fi
	else				
		# php $MAGMI_PATH -profile=$magmi_profile -mode=update -CSV:filename="$file" 
		# cp -R $file $PROCESSED_FILE
	fi	
done