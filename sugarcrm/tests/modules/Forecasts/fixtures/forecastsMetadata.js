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
if(!_.has(fixtures.metadata.modules, 'ForecastWorksheets')) {
    fixtures.metadata.modules.ForecastWorksheets = {
        '_hash': '12345678910asdgae',
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                name: {
                    audited: true,
                    dbType: "varchar",
                    full_text_search: {boost: 3},
                    len: 255,
                    link: true,
                    merge_filter: "selected",
                    name: "name",
                    type: "name",
                    unified_search: true,
                    vname: "LBL_SUBJECT"
                },
                "best_case": {
                    "name": "best_case",
                    "type": "currency"
                },
                "worst_case": {
                    "name": "worst_case",
                    "type": "currency"
                },
                "likely_case": {
                    "name": "likely_case",
                    "type": "currency"
                }
            }
    };
}
if(!_.has(fixtures.metadata.modules, 'ForecastManagerWorksheets')) {
    fixtures.metadata.modules.ForecastManagerWorksheets = {
        '_hash': '123456asdfaasdgae',
            "fields": {
                "id": {
                    "name": "id",
                    "type": "id"
                },
                name: {
                    audited: true,
                    dbType: "varchar",
                    full_text_search: {boost: 3},
                    len: 255,
                    link: true,
                    merge_filter: "selected",
                    name: "name",
                    type: "name",
                    unified_search: true,
                    vname: "LBL_SUBJECT"
                },
                "quota": {
                    "name": "quota",
                    "type": "currency"
                },
                "best_case": {
                    "name": "best_case",
                    "type": "currency"
                },
                "worst_case": {
                    "name": "worst_case",
                    "type": "currency"
                },
                "likely_case": {
                    "name": "likely_case",
                    "type": "currency"
                },
                "best_case_adjusted": {
                    "name": "best_case_adjusted",
                    "type": "currency"
                },
                "worst_case_adjusted": {
                    "name": "worst_case_adjusted",
                    "type": "currency"
                },
                "likely_case_adjusted": {
                    "name": "likely_case_adjusted",
                    "type": "currency"
                }
            }
    };
}

// Add config metadata
fixtures.metadata.modules.Forecasts.config = {
    "is_setup":1,
    "timeperiod_type":"fiscal",
    "timeperiod_interval":"Annual",
    "timeperiod_leaf_interval":"Quarter",
    "timeperiod_start_date":"2012-01-01",
    "timeperiod_shown_forward":4,
    "timeperiod_shown_backward":4,
    "forecast_ranges":"show_binary",
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
    "show_worksheet_worst":1,
    "show_projected_likely":1,
    "show_projected_best":1,
    "show_projected_worst":1,
    "show_print_button":1
}
