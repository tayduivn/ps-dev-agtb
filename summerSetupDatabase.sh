#!/bin/bash

clear

echo "******************************"
echo "Important note:"
echo ""
echo "Your SugarCRM database must be named 'sugarcrm'."
echo "******************************"
echo ""

/Applications/MAMP/Library/bin/mysql -uroot -proot -e "CREATE DATABASE summer;"
/Applications/MAMP/Library/bin/mysql -uroot -proot summer < sugarcrm/summer/splash/boxoffice/boxoffice.sql

echo "Navigate to the summer splash page. Please log in with your SugarCRM"
echo "Google Account. Afterwards, return to this window and press enter."
echo ""
sleep 2
read -p "Waiting until you log in. Press enter after you're done."

/Applications/MAMP/Library/bin/mysql -uroot -proot -e 'UPDATE instances SET status="Active",flavor="ent",config="{\"dbconfig\":{\n\"db_host_name\":\"localhost\",\n    \"db_host_instance\": \"\",\n    \"db_user_name\": \"root\",\n    \"db_password\": \"root\",\n    \"db_name\": \"sugarcrm\",\n    \"db_type\": \"mysql\",\n    \"db_port\": \"3306\",\n    \"db_manager\": \"MysqliManager\"\n}}";' summer

echo ""
echo "You're almost done! The next step is to install SugarCRM. Before you do"
echo "that, you need to log in to summer. Once you see the list of instances,"
echo "click on the instance name and go to the install.php page. I suggest"
echo "that you install SugarCRM with demo data, so that you'll be up and"
echo "running in no time! Have fun!"
echo ""
