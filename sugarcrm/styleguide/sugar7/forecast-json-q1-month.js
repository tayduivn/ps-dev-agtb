{
    "properties": [

    {

        "gauge_target_list":"Array"
        ,
        "title":""
        ,
        "subtitle":""
        ,
        "type":"horizontal group by chart"
        ,
        "legend":"on"
        ,
        "labels":"value"
        ,
        "print":"on"
        ,
        "goal_marker_type": [
            "group",
            "pareto"
        ]
        ,
        "goal_marker_color": [
            "#3FB300",
            "#7D12B2"
        ]
        ,       "goal_marker_label" : [
        "Quota",
        "Likely"
    ]
        ,
        "label_name":"Sales Stage"
        ,
        "value_name":"Amount"
    }

],

    "label": ["Worst","Likely","Best"],
    "color": [

    "#E61718"
    ,
    "#3FB300"
    ,
    "#177EE5"
    ,
    "#cd5200"
    ,
    "#e6bf00"
    ,
    "#7f3acd"
    ,
    "#00a9b8"
    ,
    "#572323"
    ,
    "#004d00"
    ,
    "#000087"
    ,
    "#e48d30"
    ,
    "#9fba09"
    ,
    "#560066"
    ,
    "#009f92"
    ,
    "#b36262"
    ,
    "#38795c"
    ,
    "#3D3D99"
    ,
    "#99623d"
    ,
    "#998a3d"
    ,
    "#994e78"
    ,
    "#3d6899"
    ,
    "#CC0000"
    ,
    "#00CC00"
    ,
    "#0000CC"
    ,
    "#cc5200"
    ,
    "#ccaa00"
    ,
    "#6600cc"
    ,
    "#005fcc"

],

    "values": [

    {
        "label": "January",
        "gvalue": "50",
        "gvaluelabel": "50K",
        "values": [50, 50, 10],
        "valuelabels": ["50K","50K","10K"],
        "probability": [50,70,90],
        "sales_stage": ["Qualified","Proposal","Negotiation"],
        "close_date": ["2012-01-01","2012-01-01","2012-01-01"],
        "links": ["","",""],
        "goalmarkervalue" : [200,120],
        "goalmarkervaluelabel" : ["200K","120K"]
    }
    ,
    {

        "label": "Febuary",
        "gvalue": "50",
        "gvaluelabel": "50K",
        "values": [80,80,50],
        "valuelabels": ["80K","80K","50K"],
        "probability": [50,70,90],
        "sales_stage": ["Qualified","Proposal","Negotiation"],
        "close_date": ["2012-02-01","2012-02-01","2012-02-01"],
        "links": ["","",""],
        "goalmarkervalue" : [
            200,
            240
        ],
        "goalmarkervaluelabel" : [
            "200k",
            "240K"
        ]

    }
    ,
    {
        "label": "March",
        "gvalue": "50",
        "gvaluelabel": "50K",
        "values": [100,100,40],
        "valuelabels": ["100K","100K","40K"],
        "sales_stage": ["Qualified","Proposal","Negotiation"],
        "close_date": ["2012-03-01","2012-03-01","2012-03-01"],
        "probability": [50,70,90],
        "links": ["","",""],
        "goalmarkervalue" : [
            200,
            290
        ],
        "goalmarkervaluelabel" : [
            "200K",
            "290K"
        ]

    }



]

}