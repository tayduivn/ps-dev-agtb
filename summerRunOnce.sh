#!/bin/bash

clear

echo "******************************"
echo "Important notes:"
echo ""
echo "Your SugarCRM database must be named 'sugarcrm' and the path to it must"
echo "be either http://<host_name>:<port>/sugar66 or "
echo "http://<hostname>:<port>/toffee/ent/sugarcrm/. Choosing sugar66 will allow"
echo "you to use the defaults for most prompts."
echo ""
echo "In order to do this, you will need to manually symlink the folder. The"
echo "following command will set up the symlink. Run it from your htdocs folder."
echo ""
echo "ln -s `pwd`/build/rome/builds/ent/sugarcrm sugar66"
echo "******************************"
echo ""

git submodule update --init
/Applications/MAMP/Library/bin/mysql -uroot -proot -e "CREATE DATABASE summer;"
/Applications/MAMP/Library/bin/mysql -uroot -proot summer < sugarcrm/summer/splash/boxoffice/boxoffice.sql

pushd sugarcrm/summer/splash
chmod +x ./setupDevEnv.sh
./setupDevEnv.sh
popd

pushd build/rome
php build.php --ver=6.6.0 --flav=ent
popd

echo ""
echo "Go ahead and create that symlink now. I'll wait here while you complete"
echo "that step. Press enter once you've done that. Again, here's the command:"
echo "ln -s `pwd`/build/rome/builds/ent/sugarcrm sugar66"
echo ""
read -p ""

echo "A browser will open now with the summer splash page. Please log in with"
echo "your SugarCRM Google Account. Afterwards, return to this window and"
echo "press enter. Press enter to open the browser."
echo ""
read -p ""
open http://localhost:8888/sugar66/summer/splash/
sleep 2
read -p "Waiting until you log in. Press enter after you're done."

/Applications/MAMP/Library/bin/mysql -uroot -proot -e 'UPDATE instances SET status="Active",flavor="ent",config="{\"dbconfig\":{\n\"db_host_name\":\"localhost\",\n    \"db_host_instance\": \"\",\n    \"db_user_name\": \"root\",\n    \"db_password\": \"root\",\n    \"db_name\": \"sugarcrm\",\n    \"db_type\": \"mysql\",\n    \"db_port\": \"3306\",\n    \"db_manager\": \"MysqliManager\"\n}}";' summer

echo ""
echo "You're almost done! The next step is to install SugarCRM. Before you do"
echo "that, you need to log in to summer. Once you see the list of instances,"
echo "return to this window and press enter to launch the installation page."
echo "I suggest that you install SugarCRM with demo data, so that you'll be up"
echo "and running in no time!"
echo ""
read -p ""
open http://localhost:8888/sugar66/install.php
sleep 2
echo ""
echo "Now that you've finished installing SugarCRM, you can now log in to"
echo "summer at http://localhost:8888/sugar66/summer/splash/. Have fun!"
