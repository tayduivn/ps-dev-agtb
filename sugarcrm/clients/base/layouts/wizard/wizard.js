/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
({
    /**
     * Layout used for Wizards (like the first time login wizard).
     * Extend this layout and provide metadata for your wizard page components.
     *
     * Default implementation allows you to register a callback on the context
     * to get notified when Wizard is finished.
     *
     * For example,
     * <pre>
     * context.set("callbacks", {
     *     complete: function(){...}
     * }
     * </pre>
     *
     * @class View.Layouts.WizardLayout
     * @alias SUGAR.App.view.layouts.WizardLayout
     */

    /**
     * Current page index shown in Wizard
     * @private
     */
    _currentIndex: 0,
    /**
     * Place only initial wizard page at first
     * @param component Wizard page component
     * @override
     * @private
     */
    _placeComponent: function(component){
        if (component == this._components[this._currentIndex]) {
            this.$el.append(component.el);
        }
    },

    /**
     * Add only wizard pages that the current user needs to see.
     *
     * @param {View.Layout/View.View} component Component (view or layout) to add
     * @param {Object} def Metadata definition
     * @override
     */
    addComponent: function(component, def) {
        component = this._addButtonsForComponent(component);
        if (component.showPage()) {
            app.view.Layout.prototype.addComponent.call(this, component, def);
        }
    },
    /**
     * Helper to add appropriate buttons based on which page of wizard we're on.
     * Assumes that button 0 is previous, 1 is next, 2 is finish (Start Sugar).
     * Should only be called internal by `addComponent`.
     * @param {Object} component component from `addComponent`
     * @private
     */
    _addButtonsForComponent: function(component) {
        var buttons = [];
        component.meta = component.meta || {};
        //Adds appropriate button for component based on position in wizard
        _.each(this.meta.components, function(comp, i) {
            //found a match, add appropriate buttons based on wizard position
            if (comp.view === component.name) {
                if (i===0) {
                    //next button only
                    buttons.push(this.meta.buttons[1]);
                } else if (i === this.meta.components.length-1) {
                    //prevous/start sugar buttons
                    buttons.push(this.meta.buttons[0]);
                    buttons.push(this.meta.buttons[2]);
                } else {
                    //prevous/next buttons
                    buttons.push(this.meta.buttons[0]);
                    buttons.push(this.meta.buttons[1]);
                }
            }
        }, this);
        component.meta.buttons = buttons;
        return component;
    },
    /**
     * Renders a different page from the wizard
     * @param {Number} newIndex New page index to select
     * @returns {{page: number, lastPage: number}} Current page number and the
     * last page number
     */
    setPage: function(newIndex){
        if (newIndex !== this._currentIndex &&
                (newIndex >= 0 && newIndex < this._components.length)) {
            //detach preserves jQuery event listeners, etc.
            this._components[this._currentIndex].$el.detach();
            this._currentIndex = newIndex;
            this.$el.append(this._components[this._currentIndex].el);
            this._components[this._currentIndex].render();
        }
        return this.getProgress();
    },
    /**
     * Only render the current component (WizardPageView) instead of each component in layout
     * @override
     * @private
     */
    _render: function(){
        if (Modernizr.touch) {
            app.$contentEl.addClass('content-overflow-visible');
        }
        if (this._components) {
            this._components[this._currentIndex].render();
        }
    },
    /**
     * Returns current progress through wizard
     * @returns {{page: number, lastPage: number}} Current page number and the
     * last page number
     */
    getProgress: function(){
        return {
            page: this._currentIndex + 1,
            lastPage: this._components.length
        };
    },
    /**
     * Moves to previous page, if possible.
     * @returns {{page: number, lastPage: number}} Current page number and the
     * last page number
     */
    previousPage: function(){
        return this.setPage(this._currentIndex - 1);
    },
    /**
     * Moves to next page, if possible.
     * @returns {{page: number, lastPage: number}} Current page number and the
     * last page number
     */
    nextPage: function(){
        return this.setPage(this._currentIndex + 1);
    },

    /**
     * Disposes of layout then calls finished callback if registered
     */
    finished: function(){
        if (Modernizr.touch) {
            app.$contentEl.removeClass('content-overflow-visible');
        }
        var callbacks = this.context.get("callbacks"); //save callbacks first
        this.dispose();
        if (callbacks && callbacks.complete) {
            callbacks.complete();
        }
    }
})
