/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
import {seedbed} from '@sugarcrm/seedbed';
import * as request from 'request-promise';

export const updateOpportunityConfig = async (data) => {

    let config = {
        opps_view_by: data['opps_view_by'],
        opps_closedate_rollup: data['opps_close_date']
    };

    let reqOptions = seedbed.api._buildOptions(
        'POST',
        'Opportunities/config',
        false,
        config);

    await request(reqOptions);
};

export const updateForecastConfig = async (data) => {

    let config = {
        is_setup: 1,
        is_upgrade: 0,
        has_commits: 1,
        timeperiod_type: 'chronological',
        timeperiod_interval: 'Annual',
        timeperiod_leaf_interval: 'Quarter',
        timeperiod_start_date: '2018-01-01',
        timeperiod_shown_forward: 2,
        timeperiod_shown_backward: 2,
        forecast_ranges: 'show_binary',
        buckets_dom: 'commit_stage_binary_dom',
        show_binary_ranges: {
            include: {
                min: parseInt(data['show_binary_ranges.include.min'], 10),
                max: parseInt(data['show_binary_ranges.include.max'], 10),
            },
            exclude: {
                min: parseInt(data['show_binary_ranges.exclude.min'], 10),
                max: parseInt(data['show_binary_ranges.exclude.max'], 10),
            }
        },
        show_buckets_ranges: {
            include: {
                min: 85,
                max: 100
            },
            upside: {
                min: 70,
                max: 84
            },
            exclude: {
                min: 0,
                max: 69
            }
        },
        sales_stage_won: [
            'Closed Won'
        ],
        sales_stage_lost: [
            'Closed Lost'
        ],
        show_worksheet_likely: 1,
        show_worksheet_best: 1,
        show_worksheet_worst: 1,
        show_projected_likely: 1,
        show_projected_best: 1,
        show_projected_worst: 0,
        show_forecasts_commit_warnings: 1,
        worksheet_columns: [
            'commit_stage',
            'parent_name',
            'opportunity_name',
            'account_name',
            'date_closed',
            'sales_stage',
            'worst_case',
            'likely_case',
            'best_case'
        ]
    };

    let reqOptions = seedbed.api._buildOptions(
        'POST',
        'Forecasts/config',
        false,
        config);

    await request(reqOptions);
};
