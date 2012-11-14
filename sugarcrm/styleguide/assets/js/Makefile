DATE=$(shell date +%I:%M%p)
CHECK=\033[32m✔\033[39m

bootstrap:
	cat bootstrap-transition.js bootstrap-alert.js bootstrap-button.js bootstrap-carousel.js bootstrap-collapse.js bootstrap-dropdown.js bootstrap-modal.js bootstrap-tooltip.js bootstrap-popover.js bootstrap-scrollspy.js bootstrap-tab.js bootstrap-typeahead.js > bootstrap.js
	uglifyjs -nc bootstrap.js > bootstrap.min.tmp.js
	echo "/*!\n* Bootstrap.js by @fat & @mdo\n* Copyright 2012 Twitter, Inc.\n* http://www.apache.org/licenses/LICENSE-2.0.txt\n*/" > copyright.js
	cat copyright.js bootstrap.min.tmp.js > bootstrap.min.js
	rm copyright.js bootstrap.min.tmp.js
