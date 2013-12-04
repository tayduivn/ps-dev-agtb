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
    pageData: {},
    section: {},
    page: {},
    section_page: false,
    parent_link: '',
    file: '',
    keys: [],
    content_init: null,
    content_render: null,
    content_dispose: null,

    initialize: function(options) {
        var self = this,
            keys = [];

        app.view.View.prototype.initialize.call(this, options);

        // load page data from content view
        this.pageData = app.metadata.getView(this.module, 'content').page_data;

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
                }
                this.section_page = true;
                this.file = 'index';
            } else if (keys.length > 1) {
                // section page call
                this.section = this.pageData[keys[0]];
                this.page = this.section.pages[keys[1]];
                this.parent_link = '_' + keys[0];
                if (this.page.js) {
                    $.ajax({
                        url: 'modules/Styleguide/clients/base/views/content/' + this.file + '.js',
                        async: false,
                        context: self
                    })
                    .done(function() {
                        if (typeof _init_content !== 'undefined') {
                            this.content_init = _init_content;
                        }
                        if (typeof _render_content !== 'undefined') {
                            this.content_render = _render_content;
                        }
                        if (typeof _dispose_content !== 'undefined') {
                            this.content_dispose = _dispose_content;
                        }
                    });
                }
            } else {
                // general page call
                this.section = this.pageData[keys[0]];
            }
        }

        // intitialize data needed for content page handlebars template
        if (this.content_init) {
            this.content_init(this, app);
        }
    },

    getSelect2Constructor: function($select) {
        var _ctor = {};
        _ctor.minimumResultsForSearch = 7;
        _ctor.dropdownCss = {};
        _ctor.dropdownCssClass = '';
        _ctor.containerCss = {};
        _ctor.containerCssClass = '';

        if ( $select.hasClass('narrow') ) {
            _ctor.dropdownCss.width = 'auto';
            _ctor.dropdownCssClass = 'select2-narrow ';
            _ctor.containerCss.width = '75px';
            _ctor.containerCssClass = 'select2-narrow';
            _ctor.width = 'off';
        }

        if ( $select.hasClass('inherit-width') ) {
            _ctor.dropdownCssClass = 'select2-inherit-width ';
            _ctor.containerCss.width = '100%';
            _ctor.containerCssClass = 'select2-inherit-width';
            _ctor.width = 'off';
        }

        return _ctor;
    },

    _render: function() {
        var self = this;

        // load handlebars content into variable
        var pageContent = app.template.getView('content.' + this.file, this.module);

        if (pageContent) {
            this.content = pageContent(self);
        }

        // render view
        app.view.View.prototype._render.call(this);

        if (this.keys[0] === 'index') {
            // build index pages
            this.render_index(this.keys[1]);
        } else {
            if (this.content_render) {
                this.content_render(self, app);
            }
            // prettify code blocks
            window.prettyPrint && prettyPrint();
        }
    },

    /* RENDER index page
    *******************/
    render_index: function(section) {

        var self = this,
            i = 0,
            m = '',
            l = 0;

        if (!section) {
            // index call
            $.each(this.pageData, function (kS,vS) {
                if (!vS.index) return;
                m += (i%3 === 0 ? '<div class="row-fluid">' : '');

                m += '<div class="span4"><h3>'+
                    '<a class="section-link" href="'+
                    (vS.url ? vS.url : fmtLink(kS)) +'">'+
                    vS.title +'</a></h3><p>'+ vS.description +'</p><ul>';
                if (vS.pages) {
                    $.each(vS.pages, function (kP,vP) {
                        m += '<li ><a class="section-link" href="'+
                            (vP.url ? vP.url : fmtLink(kS,kP)) +'">'+
                            vP.label +'</a></li>';
                    });
                }
                m += '</ul></div>';

                m += (i%3 === 2 ? '</div>' : '');
                i += 1;
            });
        } else {
            // section call
            $.each(this.pageData[section].pages, function (kP,vP) {
                m += (i%4 === 0 ? '<div class="row-fluid">' : '');

                m += '<div class="span3"><h3>'+
                    (!vP.items ?
                        ('<a class="section-link" href="'+ (vP.url ? vP.url : fmtLink(section,kP)) +'">'+ vP.label +'</a>') :
                        vP.label
                    ) +
                    '</h3><p>'+ vP.description;
                // if (vS.items) {
                //     l = vS.items.length-1;
                //     $.each(d.items, function (kP,vP) {
                //         m += ' <a class="section-link" href="'+ (vP.url ? vP.url : fmtLink(kS,kP)) +'">'+ d2 +'</a>'+ (j===l?'.':', ');
                //     });
                // }
                m += '</p></div>';

                m += (i%4 === 3 ? '</div>' : '');
                i += 1;
            });
        }

        $('#index-content').append('<section id="section-menu"></section>').html(m);

        function fmtLink(section, page) {
            return '#Styleguide/docs/' +
                (page?'':'index_') + section.replace(/[\s\,]+/g,'-').toLowerCase() + (page?'_'+page:'');
        }

        (function ($) {
            /* adapted from: http://papermashup.com/jquery-list-filtering/ */
            jQuery.expr[':'].Contains = function(a,i,m){
              return ( a.textContent || a.innerText || '').toUpperCase().indexOf(m[3].toUpperCase() ) >= 0;
            };

            function filterList($input, $list) {
              $input
                .on('change.styleguide', function () {
                  var filter = $(this).val();
                  if ( filter )
                  {
                    $list.find('p').hide();
                    var $matches = $list.find('ul').find('a:Contains('+ filter +')').parent();
                    $('li', $list).not($matches).slideUp();
                    $matches.slideDown();
                  }
                  else
                  {
                    $list.find('p').show();
                    $list.find('li').slideDown();
                  }
                  return false;
                })
                .on('keyup.styleguide', function () {
                  $(this).change();
                });
            }

            $(function () {
              filterList($('.filterinput'), $('#index-content'));
            });
        }(jQuery));
    },

    _dispose: function() {
        if (this.content_dispose) {
            this.content_dispose(this);
        }
        app.view.View.prototype._dispose.call(this);
    }
})
