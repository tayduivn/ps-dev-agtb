/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
{
	'properties': [

	{

		'gauge_target_list':'Array'
,
		'title':''
,
		'subtitle':''
,
		'type':'horizontal group by chart'
,
		'legend':'on'
,
		'labels':'value'
,
		'print':'on'
,
		'goal_marker_type': [
            'group',
            'group'
            ]
,
		'goal_marker_color': [
            '#3FB300',
            '#444444'
           ]
,       'goal_marker_label' : [
        'Quota',
        'Likely'
          ]
,
        'label_name':'Sales Stage'
,
        'value_name':'Amount'
	}

	],

	'label': [

		'Qualified'
,
		'Proposed'
,
		'Quotes'
,
        'Closed/Won'

	],

	'color': [

		'#8c2b2b'
,
		'#468c2b'
,
		'#2b5d8c'
,
		'#cd5200'
,
		'#e6bf00'
,
		'#7f3acd'
,
		'#00a9b8'
,
		'#572323'
,
		'#004d00'
,
		'#000087'
,
		'#e48d30'
,
		'#9fba09'
,
		'#560066'
,
		'#009f92'
,
		'#b36262'
,
		'#38795c'
,
		'#3D3D99'
,
		'#99623d'
,
		'#998a3d'
,
		'#994e78'
,
		'#3d6899'
,
		'#CC0000'
,
		'#00CC00'
,
		'#0000CC'
,
		'#cc5200'
,
		'#ccaa00'
,
		'#6600cc'
,
		'#005fcc'

	],

	'values': [

	{

		'label': 'January',

		'gvalue': '400',

		'gvaluelabel': '400K',

		'values': [
			100
,
			200
,
			50
,
            50


		],

		'valuelabels': [
			'100K'
,
			'200K'
,
			'50K'
,
            '50K'


		],

		'links': [
			''
,
			''
,
			''


		],
		'goalmarkervalue' : [
            410,
            200
        ],
		'goalmarkervaluelabel' : [
            '410K',
            '200K'
        ]

	}
,
	{

		'label': 'Febuary',

		'gvalue': '590',

		'gvaluelabel': '590K',

		'values': [
			120
,
			190
,
			240
,
            40


		],

		'valuelabels': [
			'120K'
,
			'190K'
,
			'240K'
,
            '40K'

		],

		'links': [
			''
,
			''
,
			''


		],
		'goalmarkervalue' : [
            410,
            50
            ],
		'goalmarkervaluelabel' : [
            '410k',
            '50k'
            ]

	}
,
	{

		'label': 'March',

		'gvalue': '410',

		'gvaluelabel': '410K',

		'values': [
			10
,
			100
,
			200
,
            100

		],

		'valuelabels': [
			'10K'
,
			'100K'
,
			'200K'
,
            '100K'

		],

		'links': [
			''
,
			''
,
			''


		],
		'goalmarkervalue' : [
            800,
            110
            ],
		'goalmarkervaluelabel' : [
            '800K',
            '110k'
            ]

	}




	]

}