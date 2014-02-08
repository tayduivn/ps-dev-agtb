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
(function(app) {
    app.events.on('app:init', function() {

        _.mixin({
            /**
             * Move an item to a specific index.
             *
             * @param {Array} initialArray The initial array.
             * @param {Integer} fromIndex The index of the item to move.
             * @param {Integer} toIndex The index where the item is moved.
             * @return {Array} The array reordered.
             */
            moveIndex: function(array, fromIndex, toIndex) {
                // Remove the item, and add it back to its new position.
                array.splice(toIndex, 0, _.first(array.splice(fromIndex, 1)));
                return array;
            }
        });

    });
})(SUGAR.App);
