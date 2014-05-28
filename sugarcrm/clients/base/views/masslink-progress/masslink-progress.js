/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
/**
 * @class View.Views.Base.MasslinkProgressView
 * @alias SUGAR.App.view.views.BaseMasslinkProgressView
 * @extends View.Views.Base.MassupdateProgressView
 */
({
    extendsFrom: 'MassupdateProgressView',

    /**
     * Set of labels.
     */
    _labelSet: {
        'update': {
            PROGRESS_STATUS: 'TPL_MASSLINK_PROGRESS_STATUS',
            DURATION_FORMAT: 'TPL_MASSLINK_DURATION_FORMAT',
            FAIL_TO_ATTEMPT: 'TPL_MASSLINK_FAIL_TO_ATTEMPT',
            WARNING_CLOSE: 'TPL_MASSLINK_WARNING_CLOSE',
            WARNING_INCOMPLETE: 'TPL_MASSLINK_WARNING_INCOMPLETE',
            SUCCESS: 'TPL_MASSLINK_SUCCESS',
            TITLE: 'TPL_MASSLINK_TITLE'
        }
    }

})
