<?php
$dashletdefs = array(
'dashlets'=>array(
'SugarNews' => Array
                (
                    'type'=>'iFrameDashlet',
                    'options' => Array
                        (
                            'title' => 'Sugar News',
                            'url' => 'http://apps.sugarcrm.com/dashlet/5.2.0/sugarcrm-news-dashlet.html?lang=@@LANG@@&edition=@@EDITION@@&ver=@@VER@@',
                            'height' => 315,
                        )

                ),
                
'SugarNews2' => Array
                (
                   	'type'=>'iFrameDashlet',
                    'options' => Array
                        (
                            'title' => 'Sugar News2',
                            'url' => 'http://apps.sugarcrm.com/dashlet/5.2.0/sugarcrm-news-dashlet.html?lang=@@LANG@@&edition=@@EDITION@@&ver=@@VER@@',
                            'height' => 315,
                        )

                ),   
'MyCalls' => Array('type'=>'MyCallsDashlet'),   
'MyMeetings' => Array('type'=>'MyMeetingsDashlet'),          
'MyTasks' => Array('type'=>'MyTasksDashlet'),
'MyBugs' => Array('type'=>'MyBugsDashlet'),    
'InvadersDashlet'=>Array('type'=>'InvadersDashlet'),
'JotPadDashlet'=>array('type'=>'JotPadDashlet'),
'GoogleNews'=>array('type'=>'GoogleNews', 'options'=>array('params'=>array('q'=>'last_name'))),
'DetailView'=>array('type'=>'SugarViewDash', 'options'=>array('params'=>array('view'=>'detail'))),
'EditView'=>array('type'=>'SugarViewDash', 'options'=>array('params'=>array('view'=>'edit'))), 
'MiniView'=>array('type'=>'MiniView', 'options'=>array('params'=>array('fields'=>array('primary_address_street',array('primary_address_city', ', ' , 'primary_address_state', ' ' , 'primary_address_postalcode'), 'phone_work', 'email1')))),                
'Notefier'=>array('type'=>'Notefier'),       
'SNIPEmail'=>array('type'=>'SNIPEmail'),     
'SugarFeeds'=>array('type'=>'SugarFeeds'),
'GoogleMaps'=>array('type'=>'GoogleMaps', 'options'=>array('addresses'=>array('primary', 'alt'))),
'RSSDashlet'=>array('type'=>'RSSDashlet', 'options'=>array('url'=>'http://news.google.com/news?hl=en&tab=wn&ned=us&ie=UTF-8&output=rss', 'fields'=>array('q'=>array('name', 'account_name')))),

'BingSearch' => Array
                (
                   	'type'=>'iFrameDashlet',
                    'options' => Array
                        (
                            'title' => 'Search',
                            'url' => 'http://m.bing.com/CommonPage.aspx?a=results',
                            'height' => 315,
                            'fields'=>array('Q'=>array('name', 'account_name')
                        )

                ),
                ),
    'Twitter' => Array
                (
                   	'type'=>'iFrameDashlet',
                    'options' => Array
                        (
                            'title' => 'Twitter',
                           // 'url' => 'http://m.twitter.com/sugarclint',
                            'height' => 315,
                            //'fields'=>array(''=>array('phone_fax')),
                        )

                ),
               
),
'pages'=>array(
	array(
		'columns'=>array(
			array('width'=>'40%',
			'dashlets'=>array('MiniView',
			     'MyCalls', 'MyTasks', 'MyMeetings', 'SugarFeeds' 
			     )
			),
                        array('width'=>'40%',
			'dashlets'=>array(
			     'Twitter', 'BingSearch'
			     ),
			)
		),
		'title'=>'Cube'
	),
        array(
		'columns'=>array(
			array('width'=>'100%',
			'dashlets'=>array('GoogleMaps')
			)
			),
		'title'=>'Maps'
	),
    array(
		'columns'=>array(
			array('width'=>'100%',	
			'dashlets'=>array('DetailView')
			)
			),
		'title'=>'Detail'
	),
	array(
		'columns'=>array(
			array('width'=>'100%',	
			'dashlets'=>array('EditView')
			)
			),
		'title'=>'Edit'
	)
	
	)



);
?>