#!/bin/bash
MAGMI_PATH="/home/www.wypo.cl/html/up/magmi/cli/magmi.cli.php"
IMOPRT_DIRECTORY="/home/www.wypo.cl/html/up/magmi/import"
PROCESSED_DIRECTORY="/home/www.wypo.cl/html/up/magmi/import/processed/"
for file in $IMOPRT_DIRECTORY/1_*.csv; 
do	
	file_name=$(basename $file)	
	magmi_profile=$(echo "$file_name" | grep -Po '(?<=(_)).*(?=.csv)')
	udropship_vendor=$(echo "$file_name" | sed 's/_.*//')	
	if [ -f "$PROCESSED_DIRECTORY$file_name" ]
	then 
		file1=$(md5sum $file | cut -d ' ' -f 1)				
		file2=$(md5sum $PROCESSED_DIRECTORY$file_name | cut -d ' ' -f 1)		
		if [ "$file1" = "$file2" ]
		then
			echo "Nothing to do - File:$file_name already processed"
		else	
			php $MAGMI_PATH -profile=$magmi_profile -mode=update -CSV:filename="$file" -udropship_vendor=$udropship_vendor
			cp -R $file $PROCESSED_DIRECTORY$file_name
		fi
	else			
		php $MAGMI_PATH -profile=$magmi_profile -mode=update -CSV:filename="$file" -udropship_vendor=$udropship_vendor
		cp -R $file $PROCESSED_DIRECTORY$file_name
	fi	
done
