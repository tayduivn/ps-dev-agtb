/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/**
 *
 * @class View.Views.Base.ExternalAppView
 * @alias SUGAR.App.view.views.BaseAppView
 * @extends View.View
 */
({
    app: null,
    mounted: false,
    rendered: false,
    className: 'external-app-interface',

    /**
     * Initializing the SingleSpa, using systemJs getting hold of the needed information of the MFE.
     * singleSpa Bootstrap function is called during component initialize.
     * @inheritdoc
     */
    initialize: function(options) {
        singleSpa.start();
        this._super('initialize', arguments);
        var serverInfo = app.metadata.getServerInfo();

        if (this.meta.src) {
            var url = this.meta.src;
            if (this.meta.appendVersion && serverInfo.version) {
                url += (url.indexOf('?') ? '&' : '?') + 'sugar_version=' + serverInfo.version;
            }

            System.import(url).then(function(mod) {
                if (!mod) {
                    app.log.error('Unable to load external module from ' + url);
                }

                //Check if the export was under 'default' rather than at the top level of the module
                for (var i = 0; i < 3; i++) {
                    var props = Object.getOwnPropertyNames(mod).filter(function(name) {
                        return name.substr(0, 2) !== '__';
                    });

                    if (mod.default && (props.length === 1 || mod.__useDefault)) {
                        mod = mod.default;
                    } else {
                        break;
                    }
                }

                this.parcelApp = mod;
                //If we haven't been asked to render yet, don't force a render.
                //If we have been rendered, mount the app into our element.
                if (this.rendered) {
                    this._mountApp();
                }
            }.bind(this));
        }
    },

    /**
     * singleSpa Mount function is called during the initial render
     */
    render: function() {
        this.rendered = true;
        this._mountApp();
    },

    /**
     * singleSpa Update function is called when the component in render is called after the initial render
     * @private
     */
    _mountApp: function() {
        if (!this.mounted && this.parcelApp) {
            var root = document.createElement('div');
            //Since we can't use a shadow dom, we can at least reset the css to isolate styling.
            this.el.appendChild(root);
            this.parcelParams = {
                domElement: root
            };
            this.parcel = singleSpa.mountRootParcel(this.parcelApp, this.parcelParams);
            this.mounted = true;
        }
        if (this.mounted && this.parcel && this.parcel.update) {
            this.parcel.update(this.parcelParams);
        }
    },

    /**
     * singleSpa Unmount function is called when dispose is called on the sidecar view
     * @inheritdoc
     * @private
     */
    _dispose: function() {
        if (this.parcel && this.parcel.unmount) {
            this.parcel.unmount();
        }
        this._super('_dispose', arguments);
    }
})
