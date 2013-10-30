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
    className: 'headerpane',
    pageData: {},
    section: {},
    page: {},
    section_page: false,
    parent_link: '',
    file: '',
    keys: [],

    initialize: function(options) {
        var self = this,
            keys = [];

        app.view.View.prototype.initialize.call(this, options);

        this.pageData = options.meta.page_data;

        this.file = this.context.get('page_name');
        if (this.file && this.file !== '') {
            keys = this.file.split('_');
        }
        this.keys = keys;

        if (keys.length) {
            // get page content variables from pageData (defined in view/docs.php)
            if (keys[0] === 'index') {
                if (keys.length > 1) {
                    // section index call
                    this.section = this.pageData[keys[1]];
                } else {
                    // master index call
                    this.section = this.pageData[keys[0]];
                    //this.index_search = true;
                }
                this.section_page = true;
                this.file = 'index';
            } else if (keys.length > 1) {
                // section page call
                this.section = this.pageData[keys[0]];
                this.page = this.section.pages[keys[1]];
                this.parent_link = '_' + keys[0];
            } else {
                // general page call
                this.section = this.pageData[keys[0]];
            }
        }
    },

    _render: function() {
        var self = this,
            $find;

        // render view
        app.view.View.prototype._render.call(this);

        // styleguide guide doc search
        $find = $('#find_patterns');

        if ($find.length)
        {
            // build search select2 options
            var $optgroup;

            $.each(this.pageData, function (k,v) {
                if ( !v.index ) return;
                $optgroup = $('<optgroup>').appendTo($find).attr('label',v.title);
                $.each(v.pages, function (i,d) {
                    renderSearchOption(k, i, d, $optgroup);
                });
            });

            // search for patterns
            $find.on('change', function (e) {
                window.location.href = $(this).val();
            });

            $find.select2();
        }

        function renderSearchOption(section, page, d, optgroup) {
            $('<option>')
                .appendTo(optgroup)
                .attr('value', (d.url ? d.url : fmtLink(section, page)) )
                .text(d.label);
        }

        function fmtLink(section, page) {
            return '#Styleguide/docs/' +
                (page?'':'index_') + section.replace(/[\s\,]+/g,'-').toLowerCase() + (page?'_'+page:'');
        }
    },

})
