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
                (p ? '' : 'index-') +
                s.replace(/[\s\,]+/g,'-').toLowerCase() +
                (p ? '-' + p : '');
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
