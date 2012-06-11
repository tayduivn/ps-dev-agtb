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

    'label': ['Recurring','Non Recurring'],
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
        'gvalue': '50',
        'gvaluelabel': '50K',
        'values': [0, 50],
        'valuelabels': ['0','50K'],
        'probability': [0,60],

        'close_date': ["","2012-01-01"],
        'links': ['','',''],
        'goalmarkervalue' : [200,180],
        'goalmarkervaluelabel' : ['200K','180K']
    }
    ,
    {

        'label': 'Febuary',
        'gvalue': '50',
        'gvaluelabel': '50K',
        'values': [0,50],
        'valuelabels': ['0','50k'],
        'probability': [70,0],
        'close_date': ["","2012-02-01"],
        'links': ['','',''],
        'goalmarkervalue' : [
            200,
            180
        ],
        'goalmarkervaluelabel' : [
            '200k',
            '180'
        ]

    }
    ,
    {
        'label': 'March',
        'gvalue': '50',
        'gvaluelabel': '50K',
        'values': [50,0],
        'valuelabels': ['50K','0'],
        'close_date': ["2012-03-01"],
        'probability': [100,0],
        'links': ['','',''],
        'goalmarkervalue' : [
            200,
            180
        ],
        'goalmarkervaluelabel' : [
            '200K',
            '180k'
        ]

    }




]

}