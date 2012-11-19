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
/**
 * View that displays the activity stream.
 * @class View.Views.ActivityView
 * @alias SUGAR.App.layout.ActivityView
 * @extends View.View
 */
({
    events: {
        'click .search': 'showSearch',
        'click .addNote': 'openNoteModal',
        'click .activity a': 'loadChildDetailView',
        'click [name=show_more_button]': 'showMoreRecords'
    },
    bindDataChange: function() {
        if (this.collection) {
            this.collection.on("reset", this.render, this);
        }
    },
    showSearch: function() {
        var $searchEl = $('.search');
        $searchEl.toggleClass('active');
        $searchEl.parent().parent().parent().find('.dataTables_filter').toggle();
        $searchEl.parent().parent().parent().find('.form-search').toggleClass('hide');
        return false;
    },
    openNoteModal: function() {
        // triggers an event to show the modal
        this.layout.trigger("app:view:activity:editmodal");
        this.$('li.open').removeClass('open');
        return false;
    },
    loadChildDetailView: function(e) {
        // UI fix
        this.$("li.activity").removeClass("on");
        var $parent = this.$(e.currentTarget).parents("li.activity");
        $parent.addClass("on");

        // gets the activityId in the data attribute
        var activityId = $parent.data("id");

        // gets the activity model
        var activity = this.collection.get(activityId);

        // clears the current listened model and push the new one
        this.model.clear().set(activity.toJSON());
    },
    showMoreRecords: function(evt) {
        var self = this, options;
        app.alert.show('show_more_records', {level:'process', title:app.lang.getAppString('LBL_PORTAL_LOADING')});

        // If in "search mode" (the search filter is toggled open) set q:term param
        options = self.filterOpened ? self.getSearchOptions() : {};

        // Indicates records will be added to those already loaded in to view
        options.add = true;

        if (this.collection.link) {
            options.relate = true;
        }

        options.success = function() {
            app.alert.dismiss('show_more_records');
            self.layout.trigger("list:paginate:success");
            self.render();
            window.scrollTo(0, document.body.scrollHeight);
        };
        options.limit = this.limit;
        this.collection.paginate(options);
    },
    _render: function(){
        // Bug 54597 activity view not respecting list ACL
        var oViewName = this.name;
        this.name = 'list';
        app.view.View.prototype._render.call(this);
        this.name = oViewName;
    }
})
