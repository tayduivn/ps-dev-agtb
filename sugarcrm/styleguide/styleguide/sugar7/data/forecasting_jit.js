var forecast_data_Q1 = {
    "properties": [{
        "gauge_target_list": "Array",
        "title": null,
        "subtitle": "",
        "type": "bar chart",
        "legend": "on",
        "labels": "value",
        "print": "on",
        "thousands": "",
        "goal_marker_type": ["group", "pareto", "pareto"],
        "goal_marker_color": ["#000000", "#8c2b2b", "#468c2b"],
        "goal_marker_label": ["Quota", "Likely Case", "Likely (Adjusted)"],
        "label_name": "Type",
        "value_name": "Amount"
    }],
   // "color": ["#8c2b2b", "#468c2b", "#2b5d8c", "#cd5200", "#e6bf00", "#7f3acd", "#00a9b8", "#572323", "#004d00", "#000087", "#e48d30", "#9fba09", "#560066", "#009f92", "#b36262", "#38795c", "#3D3D99", "#99623d", "#998a3d", "#994e78", "#3d6899", "#CC0000", "#00CC00", "#0000CC," "#cc5200", "#ccaa00", "#6600cc", "#005fcc"],
    "label": ["Likely Case", "Likely (Adjusted)"],
    "values": [{
        "label": "Sarah Smith",
        "gvalue": 0,
        "gvaluelabel": 0,
        "values": ["649700.00", "250500.00"],
        "valuelabels": ["$649,700.00", "$250,500.00"],
        "links": ["", ""],
        "goalmarkervalue": ["1368000.00", "649700.00", "250500.00"],
        "goalmarkervaluelabel": ["$1,368,000.00", "$649,700.00", "$250,500.00"]
    }, {
        "label": "Opportunities (Jim Brennan)",
        "gvalue": 0,
        "gvaluelabel": 0,
        "values": ["131200.00", "75000.00"],
        "valuelabels": ["$131,200.00", "$75,000.00"],
        "links": ["", ""],
        "goalmarkervalue": ["1368000.00", "780900.00", "325500.00"],
        "goalmarkervaluelabel": ["$1,368,000.00", "$780,900.00", "$325,500.00"]
    }, {
        "label": "Will Westin",
        "gvalue": 0,
        "gvaluelabel": 0,
        "values": ["270700.00", "50500.00"],
        "valuelabels": ["$270,700.00", "$50,500.00"],
        "links": ["", ""],
        "goalmarkervalue": ["1368000.00", "1051600.00", "376000.00"],
        "goalmarkervaluelabel": ["$1,368,000.00", "$1,051,600.00", "$376,000.00"]
    }]
}

var mark_gibson_Q1 = {
    "properties": [{
        "gauge_target_list": "Array",
        "title": null,
        "subtitle": "",
        "type": "bar chart",
        "legend": "on",
        "labels": "value",
        "print": "on",
        "thousands": "",
        "goal_marker_type": ["group", "pareto"],
        "goal_marker_color": ["#000000", "#7D12B2"],
        // line series labels
        "goal_marker_label": ["Quota", "Likely Case"],
        "label_name": "Sales Stage",
        "value_name": "Likely Case"
    }],
    "color": ["#8c2b2b", "#468c2b", "#2b5d8c", "#cd5200", "#e6bf00", "#7f3acd", "#00a9b8", "#572323", "#004d00", "#000087", "#e48d30", "#9fba09", "#560066", "#009f92", "#b36262", "#38795c", "#3D3D99", "#99623d", "#998a3d", "#994e78", "#3d6899", "#CC0000", "#00CC00", "#0000CC", "#cc5200", "#ccaa00", "#6600cc", "#005fcc"],
    // bar series labels (legend)
    "label": ["Negotiation/Review", "Value Proposition", "Closed Won"],
    "values": [{
        // bar group labels (x-axis)
        "label": "October 2012",
        "gvalue": 101500,
        "values": [50500, 25500, 25500],
        "goalmarkervalue": ["360000.00", "101500.00"],
    }, {
        "label": "November 2012",
        "gvalue": 50500,
        "values": [0, 50500, 0],
        "goalmarkervalue": ["360000.00", "152000.00"],
    }, {
        "label": "December 2012",
        "gvalue": 75500,
        "values": [75500, 0, 0],
        "goalmarkervalue": ["360000.00", "227500.00"],
    }]
};
