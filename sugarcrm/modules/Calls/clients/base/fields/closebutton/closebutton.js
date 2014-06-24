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
 * @class View.Fields.Base.Calls.ClosebuttonField
 * @alias SUGAR.App.view.fields.BaseCallsClosebuttonField
 * @extends View.Fields.Base.ClosebuttonField
 */
({
    extendsFrom: 'ClosebuttonField',

    /**
     * Status indicating that the call is closed or complete.
     *
     * @type {String}
     */
    closedStatus: 'Held',

    /**
     * @inheritDoc
     */
    showSuccessMessage: function() {
        var options = app.metadata.getModule(this.module).fields.status.options,
            strings = app.lang.getAppListStrings(options),
            status = strings[this.closedStatus].toLocaleLowerCase();

        app.alert.show('close_call_success', {
            level: 'success',
            autoClose: true,
            messages: app.lang.get('TPL_CALL_STATUS_CHANGED', this.module, {status: status})
        });
    }
})
