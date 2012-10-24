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
    // date
    events: {
        'click .icon-calendar': 'toggleDatepicker'
    },
    datepickerVisible: false,
    _render: function(value) {
        var self, viewName, usersDateFormatPreference;
        self = this;

        // Although the server serves up iso date string with Z and all .. for date types going back it wants this
        self.serverDateFormat = 'Y-m-d';

        usersDateFormatPreference = app.user.get('datepref');
        
        app.view.Field.prototype._render.call(this);//call proto render

        viewName = self.view.meta && self.view.meta.type ? self.view.meta.type : self.view.name;
        $(function() {
            if(self.options.def.view === 'edit' || self.options.viewName === 'edit' ||
                viewName === 'edit') {
                self.$(".datepicker").attr('placeholder', app.date.toDatepickerFormat(usersDateFormatPreference));
                self.$(".datepicker").datepicker({
                    format: (usersDateFormatPreference) ? app.date.toDatepickerFormat(usersDateFormatPreference) : 'mm-dd-yyyy',
                    show: function(evt) {
                        self.datepickerVisible = true;
                    },
                    hide: function(evt) {
                        self.datepickerVisible = false;
                    }
                });
            }
        });
    },
    toggleDatepicker: function() {
        var action = (this.datepickerVisible) ? 'hide' : 'show';
        this.$(".datepicker").datepicker(action);
    },
    unformat:function(value) {
        var jsDate, 
            usersDateFormatPreference = app.user.get('datepref');
        // In case ISO 8601 get it back to js native date which date.format understands
        jsDate = new Date(value);
        return app.date.format(jsDate, this.serverDateFormat);
    },

    format:function(value) {
        var jsDate, parts,
            usersDateFormatPreference = app.user.get('datepref');

        // If there is a default 'string' value like "yesterday", format it as a date
        if(this.model.isNew() && !value && this.def.display_default && this.view.name === 'edit') {
            value = app.date.parseDisplayDefault(this.def.display_default);
            parts = value.match(/(\d+)/g);
            jsDate = new Date(parts[0], parts[1]-1, parts[2]); //months are 0-based
            this.model.set(this.name, app.date.format(jsDate, this.serverDateFormat));
        } else if(!value) {
            return value;
        } else {
            // Bug 56249 .. Date constructor doesn't reliably handle yyyy-mm-dd
            // e.g. new Date("2011-10-10" ) // in my version of chrome browser returns
            // Sun Oct 09 2011 17:00:00 GMT-0700 (PDT)
            parts = value.match(/(\d+)/g);
            jsDate = new Date(parts[0], parts[1]-1, parts[2]); //months are 0-based
            value  = app.date.format(jsDate, usersDateFormatPreference);
        }

        jsDate = app.date.parse(value);
        return app.date.format(jsDate, usersDateFormatPreference);
    }

})
