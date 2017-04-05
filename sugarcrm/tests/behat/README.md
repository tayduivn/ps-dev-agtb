# End to end testing by [behat](http://behat.org/)

# Setting up 
For setting up and running Behat:
Fill base_url in `behat.yml`
```sh
  cd sugarcrm
  chmod +x behat.sh
  ./behat.sh
``` 

# Configuration
In `behat.yml` change base url for SugarCrm instance `base_url: http://sugar.host/` 

# Installation
Download: 
 * [chromedriver](https://sites.google.com/a/chromium.org/chromedriver/downloads)
 * [Selenium Standalone Server](http://www.seleniumhq.org/download/)

Run:
 * chromedriver - `unzip chromedriver_mac64.zip && ./chromedriver`
 * Selenium Standalone Server - `java -jar selenium-server-standalone-3.3.1.jar`

# Running tests:
`./vendor/bin/behat`
