DB_USERNAME=''
DB_PWD=''
DB_NAME=''
if [ -r ./secret.cfg ] ; then
	. ./secret.cfg
fi

mysql --user=$DB_USERNAME --password=$DB_PWD $DB_NAME < dbload.sql
echo mysql --user=$DB_USERNAME --password=$DB_PWD $DB_NAME < dbload.sql
