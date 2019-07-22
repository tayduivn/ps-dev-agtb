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

({
    extendsFrom: 'RecordListView',
    plugins: [
        'SugarLogic',
        'ListColumnEllipsis',
        'ResizableColumns',
        'ErrorDecoration',
        'MergeDuplicates',
        'Pagination',
        'MassCollection'
    ],

    /**
     * Adds the right side preview column to the recordlist view.
     * Overrides the base recordlist view to remove the left side selection column
     */
    addActions: function() {
        var meta = this.meta;
        if (meta && _.isObject(meta.rowactions)) {
            this.addRowActions();
        }
    }
})
