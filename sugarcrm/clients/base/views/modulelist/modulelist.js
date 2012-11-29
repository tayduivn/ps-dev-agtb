({
    events: {
        'click #module_list li a': 'onModuleTabClicked'
    },

    initialize: function(options) {
        app.events.on("app:sync:complete", this.render, this);
        app.events.on("app:view:change", this.render, this);

        app.view.View.prototype.initialize.call(this, options);

        if (this.layout) {
            this.layout.on("view:resize", this.resize, this);
        }
    },

    /**
     * Render list of modules
     * @private
     */
    _renderHtml: function() {
        if (!app.api.isAuthenticated() || app.config.appStatus == 'offline') return;

        var self = this;
        this.module_list = {};
        if (app.metadata.getModuleNames(true, true)) {
            _.each(app.metadata.getModuleNames(true, true), function(val, key) {
                    self.module_list[val] = app.lang.get('LBL_MODULE_NAME',val);
            });
        }

        app.view.View.prototype._renderHtml.call(this);
        this.resetMenu();
        this.activeModule.set(app.controller.context.get("module"));
    },

    /**
     * When user clicks tab navigation in header
     */
    onModuleTabClicked: function(evt) {
        var $target = this.$(evt.currentTarget),
            moduleHref = $target.attr('href');

        if(moduleHref.match(/^#/)) {
            this.activeModule.set($target.closest('li').attr('class'));
            app.router.navigate(moduleHref, {trigger: true});
            return false;
        }
    },

    /**
     * Reset the module list to the full list
     */
    resetMenu: function() {
        this.$('.more').before(this.$('#module_list .dropdown-menu').children());
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
        var $dropdown = $modules.find('.dropdown-menu'),
            $moduleToInsert = $dropdown.children("li:first"),
            $more = $modules.find('.more'),
            $lastModuleInList, $nextModule,
            currentWidth = $modules.outerWidth(true);

        while ((currentWidth < width) && ($dropdown.children().length > 0)){
            $nextModule = $moduleToInsert.next();

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
    },

    /**
     * Move modules from the list to the dropdown to fit the specified width
     * @param $modules
     * @param width
     */
    removeModulesFromList: function($modules, width) {
        var $dropdown = $modules.find('.dropdown-menu'),
            $module = $modules.find('.more').prev(),
            $next, currentWidth = $modules.outerWidth(true);

        while (currentWidth >= width) {
            // home and currently active module should not be removed from the list
            if (this.activeModule.isActive($module) || $module.hasClass('Home')) {
                $module = $module.prev();
            }

            $next = $module.prev();
            $dropdown.prepend($module);

            currentWidth = $modules.outerWidth(true);
            $module = $next;
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
                $module = $modules.find('.' + module);

                $module.addClass(this._class);

                // remember which module is supposed to be next to the active module so that
                // ordering can be preserved while modules are removed and added to the list
                if (!this._next) {
                    $next = $module.next();
                    if ($next.hasClass('more')) {
                        $next = $modules.find('.dropdown-menu li:first');
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