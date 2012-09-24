if (!(fixtures)) {
    var fixtures = {};
}
// Make forecastsMetadata play nice if fixtures has already been defined for other tests
// so we dont overwrite data
if(!_.has(fixtures, 'metadata')) {
    fixtures.metadata = {};
}
if(!_.has(fixtures.metadata, 'modules')) {
    fixtures.metadata.modules = {};
}
if(!_.has(fixtures.metadata.modules, 'Forecasts')) {
    fixtures.metadata.modules.Forecasts = {};
}

// Add config metadata
fixtures.metadata.modules.Forecasts.config = {
    "is_setup":1,
    "timeperiod_type":"fiscal",
    "timeperiod_interval":"yearly",
    "timeperiod_leaf_interval":"first",
    "timeperiods_shown_forward":4,
    "timeperiods_shown_backward":4,
    "forecast_categories":"show_binary",
    "buckets_dom":"commit_stage_dom",
    "category_ranges":{
        "include":{
            "min":"70",
            "max":"100"
        },
        "exclude":{
            "min":"0",
            "max":"69"
        }
    },
    "sales_stage_won":["Closed Won"],
    "sales_stage_lost":["Closed Lost"],
    "show_worksheet_likely":1,
    "show_worksheet_best":1,
    "show_worksheet_worst":"",
    "show_projected_likely":1,
    "show_projected_best":1,
    "show_projected_worst":"",
    "show_print_button":1
}