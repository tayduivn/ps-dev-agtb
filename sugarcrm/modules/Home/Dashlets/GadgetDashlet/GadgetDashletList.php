<?php
//FILE SUGARCRM flav=int ONLY
$googleGadgets = array(
	'News'=>array(
		'BBC News Feed'=>'http://fillipebp.googlepages.com/bbc-news-rss-feeds.xml&amp;up_extrafeed=http%3A%2F%2Fnewsrss.bbc.co.uk%2Frss%2Fnewsonline_uk_edition%2Fhealth%2Frss.xml',
		'AccuWeather'=>"http://gwidget.accuweather.com/adcbin/googleforecastgadget/gadget.asp&amp;up_myzip={$GLOBALS["current_user"]->address_postalcode}&up_mywxview1=calendar&amp;up_mywxview2=hourly",
		'AP News Feed'=>'http://fillipebp.googlepages.com/ap-news-rss-feeds4.xml',		
	),
	'Tools'=>array(
		'Daylight Map'=>'http://www.daylightmap.com/daylight_gadget.xml&amp;up_start_location=Last%20Viewed',
		 'Area Code Lookup'=>'http://aruljohn.com/gadget/areacode.xml',
		 'Zip Code Lookup'=> 'http://aruljohn.com/gadget/zip.xml',
		'What Is MyIP'=>'http://aruljohn.com/gadget/ip.xml',
		 'Ip Lookup'=>'http://homepagegadgets.googlepages.com/iplookup.xml'
	),
	'Communication'=>array(
		'Google Talk'=>'http://www.google.com/ig/modules/googletalk.xml',
	),
	'Fun & Games'=>array(
		'Bejeweled'=>'http://bejeweledg.googlecode.com/svn/trunk/bejeweled.xml',
		'Fish Tank'=>'http://fishgadget.googlecode.com/svn/trunk/fish.xml&amp;up_fishColor=none&amp;up_fishName=Fish&amp;up_backgroundColor=F0F7FF&amp;up_backgroundImage=http%3A%2F%2F&amp;up_numFish=10&amp;up_fishColor1=F45540&amp;up_fishColor2=0E30B7&amp;up_fishColor3=97B6A6&amp;up_fishColor4=FEB859&amp;up_fishColor5=FFE114&amp;up_fishColor6=000000&amp;up_fishColor7=BFD1C1&amp;up_fishColor8=F45540&amp;up_fishColor9=0E30B7&amp;up_fishColor10=userColor1&amp;up_foodColor=FCB347&amp;up_userColor1=cccccc',
		'Pacman'=>'http://andrewgadget.googlepages.com/pacman-game.xml',
		'Funny Cats'=>'http://blog.esaba.com/projects/catphotos/catphotos.xml&amp;up_thumb_count=1&amp;up_mode=safe',
		'Duck Hunt'=>'http://www.pwncade.com/opensocial/duckhunt.xml',
		'Tetris'=>'http://www.aumentafuentegratis.com/tetris/tetris.xml&amp;up_name=',
	),
	'Finance'=>array(
		'Currency Converter'=>'http://www.donalobrien.net/apps/google/currency.xml&amp;up_def_from=USD&amp;up_def_to=EUR',
		'Stock Ticker'=>'http://padmanijain.googlepages.com/z202.xml&amp;up_NAMES=%5EDJI%2C%5EIXIC%2CCSCO',
	),
	'Sports'=>array(
		'CBS Sports Scores'=>'http://www.sportsline.com/modules/scoresDOM',
		'ESPN News Feed'=>'http://humanmaze.googlepages.com/espn-sports-rss-feeds.xml',
		
	),

);
?>