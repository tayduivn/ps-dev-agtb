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
    className: 'container-fluid',
    section_description: '',
    section_key: '',

    /* RENDER index page
    *******************/
    _renderHtml: function() {
        var self = this,
            i = 0,
            html = '',
            request = this.context.attributes.request;

        this._super('_renderHtml');

        this.section_key = request.keys[1];

        function fmtLink(s, p) {
            return '#Styleguide/docs/' +
                (p ? '' : 'index_') +
                s.replace(/[\s\,]+/g,'-').toLowerCase() +
                (p ? '_' + p : '');
        }

        if (request.keys.length === 1) {

            // home index call
            $.each(request.page_data, function (kS, vS) {
                if (!vS.index) {
                    return;
                }

                html += (i % 3 === 0 ? '<div class="row-fluid">' : '');
                html += '<div class="span4"><h3>' +
                    '<a class="section-link" href="' +
                    (vS.url ? vS.url : fmtLink(kS)) + '">' +
                    vS.title + '</a></h3><p>' + vS.description + '</p><ul>';
                if (vS.pages) {
                    $.each(vS.pages, function (kP, vP) {
                        html += '<li ><a class="section-link" href="' +
                            (vP.url ? vP.url : fmtLink(kS, kP)) + '">' +
                            vP.label + '</a></li>';
                    });
                }
                html += '</ul></div>';
                html += (i % 3 === 2 ? '</div>' : '');

                i += 1;
            });

            this.section_description = request.page_data[request.keys[0]].description;

        } else {

            // section index call
            $.each(request.page_data[this.section_key].pages, function (kP, vP) {
                html += (i % 4 === 0 ? '<div class="row-fluid">' : '');
                html += '<div class="span3"><h3>' +
                    (!vP.items ?
                        ('<a class="section-link" href="' + (vP.url ? vP.url : fmtLink(self.section_key, kP)) + '">' + vP.label + '</a>') :
                        vP.label
                    ) +
                    '</h3><p>' + vP.description;
                // if (vS.items) {
                //     l = vS.items.length-1;
                //     $.each(d.items, function (kP,vP) {
                //         m += ' <a class="section-link" href="'+ (vP.url ? vP.url : fmtLink(kS,kP)) +'">'+ d2 +'</a>'+ (j===l?'.':', ');
                //     });
                // }
                html += '</p></div>';
                html += (i % 4 === 3 ? '</div>' : '');

                i += 1;
            });

            this.section_description = request.page_data[request.keys[1]].description;
        }

        this.$('#index_content').append('<section id="section-menu"></section>').html(html);
    }
})
