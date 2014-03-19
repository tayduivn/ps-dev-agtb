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
({
    className: 'container-fluid',

    // components dropdowns
    _renderHtml: function () {
        this._super('_renderHtml');

        this.$('[data-alert]').on('click', function() {

            var $button = $(this),
                level = $button.data('alert'),
                state = $button.text(),
                auto_close = ['info','success'].indexOf(level) > -1;

            app.alert.dismiss('core_meltdown_' + level);

            if (state !== 'Example') {
                $button.text('Example');
            } else {
                app.alert.show('core_meltdown_' + level, {
                    level: level,
                    messages: 'The core is in meltdown!!',
                    autoClose: auto_close,
                    onClose: function () {
                        $button.text('Example');
                    }
                });
                $button.text('Dismiss');
            }
        });
    },

    _dispose: function() {
        this.$('[data-alert]').off('click');
        app.alert.dismissAll();

        this._super('_dispose');
    }
})
