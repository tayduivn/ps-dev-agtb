({
    favRowTemplate: Handlebars.compile(
      '{{#each models}}<li><a tabindex="-1" class="favoriteLink actionLink" href="#{{modelRoute this}}"><i class="icon-favorite active"></i>{{getFieldValue this "name"}}</a></li>{{/each}}'
    ),
    recentRowTemplate: Handlebars.compile(
        '{{#each models}}<li><a tabindex="-1" class="recentLink actionLink" href="#{{modelRoute this}}"><i class="icon-time active"></i>{{getFieldValue this "name"}}</a></li>{{/each}}'
    ),
    events: {
        'click #module_list li a.route': 'onModuleTabClicked',
        'click .dtoggle': 'toggleDropdown',
        'click .more': 'showMore',
        'mouseleave .more-drop-container' : 'hideMore',
        'mouseleave .dropdown-menu': 'hideMenu',
        'click .actionLink' : 'handleMenuEvent'
    },
    handleMenuEvent:function (evt) {
        var $currentTarget = this.$(evt.currentTarget);
        if ($currentTarget.data('event')) {
            var module = $currentTarget.closest('li.dropdown').data('module');
            app.events.trigger($currentTarget.data('event'), module, evt);
        }
    },
    handleCreateLink:function (module, eventObj) {
        app.router.navigate(
            app.router.buildRoute(module, false, false),
            {
                trigger:true
            }
        );

        app.drawer.open({
            layout:'create',
            context:{
                create:true
            }
        }, _.bind(function (refresh) {
            if (refresh) {
                var collection = app.controller.context.get('collection');
                if (collection) {
                    collection.fetch();
                }
            }
        }, this));

    },
    hideMenu: function(event) {
        this.$(event.target).dropdown('toggle').closest('li.dropdown.open').removeClass('open');
    },
    showMore: function(event) {
        this.$('.more-drop-container').show();
    },
    hideMore: function(event) {
        this.$('.more-drop-container').hide();
    },
    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:view:change", this.render, this);
        app.user.on("change:module_list", this.render, this);
        app.view.View.prototype.initialize.call(this, options);
        var resizeFn = _.debounce(this.resize, 300);
        app.events.register("megamenu:create:click", this);
        app.events.on("megamenu:create:click", this.handleCreateLink, this);
        if (this.layout) {
            this.layout.on("view:resize", resizeFn, this);
        }
    },
    /**
     * toggles dropdowns on mouseover
     * @param event
     */
    toggleDropdown:function (event) {
        var $currentTarget = $(event.currentTarget);
        if ($currentTarget.next('.dropdown-menu').is(":visible")) {
            $currentTarget.next('.dropdown-menu').dropdown('toggle');
            $currentTarget.closest('.btn-group').closest('li.dropdown').toggleClass('open');
            return false;
        }
        if (!$currentTarget.parent().parent().hasClass('more-drop-container') && !$currentTarget.hasClass('actionLink')) {
            // clear any open dropdown styling
            this.$('.open').toggleClass('open');
            var module = $currentTarget.parent().parent().data('module');
            var moduleMeta = app.metadata.getModule(module);
            if (moduleMeta && moduleMeta.fields && !_.isArray(moduleMeta.fields)) {
                this.populateFavorites(module);
                this.populateRecents(module);
            }
            // NOTE: this is a workaround for bootstrap dropdowns lack of support for events
            // we manually turn this into a dropdown and get rid of its events and reapply our own
            $currentTarget.attr("data-toggle", "dropdown").dropdown('toggle');
            $currentTarget.off();
            this.delegateEvents();

            $currentTarget.closest('.btn-group').closest('li.dropdown').toggleClass('open');
        }
    },
    /**
     * Populates favorites on open menu
     * @param module
     */
    populateFavorites: function(module) {
        var self = this;
        var rowCollection = app.data.createBeanCollection(module);
        rowCollection.fetch({
            favorites:true,
            limit:3,
            success:function (collection) {
               if (collection.models && collection.models.length >  0) {
                   self.$('[data-module=' + module + '] .favoritesAnchor').show();
                   self.$('[data-module=' + module + '] .favoritesContainer').html(self.favRowTemplate(collection));
               }
           }
        });
    },
    /**
     * Populates recents on open menu
     * @param module
     */
    populateRecents:function (module) {
        var self = this;
        var filter = {
            "filter":[
                {
                    "date_modified":{
                        "$tracker":"-7 DAY"
                    }
                }
            ],
            "max_num":3
        };
        var url = app.api.buildURL(module, 'read', {id:"filter"});
        app.api.call('create', url, filter, {
            success:function (data) {
                if (data.records && data.records.length > 0) {
                    var beans = [];
                    _.each(data.records, function (recordData) {
                        beans.push(app.data.createBean(module, recordData));
                    });
                    var collection = app.data.createBeanCollection(module, beans);
                    self.$('[data-module=' + module + '] .recentAnchor').show();
                    self.$('[data-module=' + module + '] .recentContainer').html(self.recentRowTemplate(collection));
                }

            }});
    },
    /**
     * Render list of modules
     * @private
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        // loadAdditionalComponents fires render before the private metadata is ready, check for this
        if( !(_.isEmpty(app.metadata.getStrings("mod_strings"))) ) {
            var self = this;
            this.module_list = {};
            if (app.metadata.getModuleNames(true, "read")) {
                _.each(app.metadata.getModuleNames(true, "read"), function(val) {
                    self.module_list[val] = app.lang.get('LBL_MODULE_NAME', val);
                });
            }
            this.module_list = this.completeMenuMeta(this.module_list);
            app.view.View.prototype._renderHtml.call(this);
            this.resetMenu();
            this.activeModule.set(app.controller.context.get("module"));
        }
    },

    completeMenuMeta: function(module_list) {
        var actions, meta, returnList = [];
        _.each(module_list, function(value, key) {
            actions = {
                label: value,
                name: key
            };
            meta = app.metadata.getModule(key);
            if (meta && meta.menu && meta.menu.header) {
                actions.menu = meta.menu.header.meta;
            } else {
                actions.menu = [];
            }
            returnList.push(actions);

        });
        return returnList;
    },

    /**
     * When user clicks tab navigation in header
     */
    onModuleTabClicked: function(evt) {
        var module = this.$(evt.currentTarget).closest('li').data('module');
        if (module) {
            this.activeModule.set(module);
            app.router.navigate(module, {trigger: true});
        }

    },

    /**
     * Reset the module list to the full list
     */
    resetMenu: function() {
        this.$('.more').before(this.$('#module_list .more-drop-container').children());
        this.$('.dropdown.open').removeClass('open');
    },

    /**
     * Resize the module list to the specified width and move the extra module names to the dropdown.
     * We first clone the module list, make adjustments, and then replace.
     * @param width
     */
    resize: function (width) {
        if (width <= 0) {
            return;
        }

        var $moduleList = this.$el.find('#module_list'),
            $moduleListClone = $moduleList.clone(),
            $cloneContainer = $('<div></div>');

        // make the cloned module list visible but away from user's view to accurately calculate width
        $cloneContainer
            .css({
                position: 'absolute',
                top: '-9999px',
                display: 'block'
            })
            .append($moduleListClone);

        this.$el.append($cloneContainer);

        //TODO: ie Compatible, scrollable dropdown for low-res. window
        //TODO: Theme Compatible, Filtered switching menu
        //TODO: User preferences maximum menu count
        if($moduleListClone.outerWidth(true) >= width){
            this.removeModulesFromList($moduleListClone, width);
        } else {
            this.addModulesToList($moduleListClone, width);
        }

        // replace the module list with the modified cloned list
        $moduleList.remove();
        this.$el.append($moduleListClone);
        $cloneContainer.remove();
    },

    /**
     * Move modules from the dropdown to the list to fit the specified width
     * @param $modules
     * @param width
     */
    addModulesToList: function($modules, width) {
        var $dropdown = $modules.find('.more-drop-container'),
            $moduleToInsert = $dropdown.children("li:first"),
            $more = $modules.find('.more'),
            $lastModuleInList, $nextModule,
            currentWidth = $modules.outerWidth(true);

        while ((currentWidth < width) && ($dropdown.children().length > 0)){
            $nextModule = $moduleToInsert.next();

            //show the drop down toggle and hide the more link
            $moduleToInsert.find('.btn-group').show();
            $moduleToInsert.find('.moreLink').hide();

            // add the modules in order
            $lastModuleInList = $more.prev();
            if (this.activeModule.isActive($lastModuleInList) && !this.activeModule.isNext($moduleToInsert)) {
                $lastModuleInList.before($moduleToInsert);
            } else {
                $more.before($moduleToInsert);
            }

            currentWidth = $modules.outerWidth(true);
            $moduleToInsert = $nextModule;

            // remove the last added module if the width is wider than desired
            if (currentWidth >= width) {
                this.removeModulesFromList($modules, width);
                break;
            }
        }

        if( $dropdown.children().length === 0 && $modules.find('.dropdown').is(":visible") ) {
            this.$('.more').hide();
        }
    },

    /**
     * Move modules from the list to the dropdown to fit the specified width
     * @param $modules
     * @param width
     */
    removeModulesFromList: function($modules, width) {
        var $dropdown = $modules.find('.more-drop-container'),
            $module = $modules.find('.more').prev(),
            $next, currentWidth = $modules.outerWidth(true),

            // If we have an active module, # of persistent tabs = active module + sugarcube + "more" button
            persistentTabs = this.activeModule.isActive($module) ? 3 : 2;

        while (currentWidth >= width && ($modules.children().length - persistentTabs) > 0) {
            // home and currently active module should not be removed from the list
            if (this.activeModule.isActive($module) || $module.hasClass('Home')) {
                $module = $module.prev();
            }

            $next = $module.prev();
            $dropdown.prepend($module);
            //hide the drop down toggle and show the more link
            $module.find('.btn-group').hide();
            $module.find('.moreLink').show();

            currentWidth = $modules.outerWidth(true);
            $module = $next;
        }
        if( $dropdown.children().length !== 0 && $modules.find('.dropdown').is(":visible") ) {
            this.$('.more').show();
        }
    },

    activeModule: {
        _class: 'active', //class to indicate the active module
        _next: null, //the module next to the active module
        _moduleList: this,

        /**
         * Set the specified module as the active module
         * @param module
         */
        set: function(module) {
            var $modules, $module, $next;
            if (module) {
                this.reset();

                $modules = this._moduleList.$('#module_list');
                $module = $modules.find("[data-module='" + module+"']");
                $module.addClass(this._class);

                // remember which module is supposed to be next to the active module so that
                // ordering can be preserved while modules are removed and added to the list
                if (!this._next) {
                    $next = $module.next();
                    if ($next.hasClass('more')) {
                        $next = $modules.find('.more-drop-container li:first');
                    }
                    this._next = $next.attr('class');
                }
            }
        },

        /**
         * Is this module the active module?
         * @param $module
         * @return {Boolean}
         */
        isActive: function($module) {
            return $module.hasClass(this._class);
        },

        /**
         * Is this module supposed to be next to the the active module?
         * @param $module
         * @return {Boolean}
         */
        isNext: function($module) {
            return (this._next === $module.attr('class'));
        },

        /**
         * Clear active modules
         */
        reset: function() {
            this._next = null;
            this._moduleList.$('#module_list').children(this._class).removeClass(this._class);
        }
    }
})