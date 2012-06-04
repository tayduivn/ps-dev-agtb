#!/bin/bash

cp /Applications/MAMP/htdocs/Mango/ent/sugarcrm/config.php /tmp/config.php
~/bin/sugarbuildfresh
cp /tmp/config.php /Applications/MAMP/htdocs/Mango/ent/sugarcrm/config.php
