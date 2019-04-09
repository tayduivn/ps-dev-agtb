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
(function(app) {
    var addCompToLayout = function(metadata, targetModule, targetLayout, def) {
        var modMeta = metadata.modules[targetModule];
        if (modMeta) {
            modMeta.layouts = modMeta.layouts || {};
            if (!modMeta.layouts[targetLayout]) {
                // Merge with the global metadata before modifying if the module
                // didn't originally specify a config for this layout.
                modMeta.layouts[targetLayout] = {
                    meta: app.metadata.getLayout('', targetLayout) || {components: []}
                };
            }
            if (!modMeta.layouts[targetLayout].meta) {
                modMeta.layouts[targetLayout].meta = {};
            }
            if (!modMeta.layouts[targetLayout].meta.components) {
                modMeta.layouts[targetLayout].meta.components = [];
            }

            modMeta.layouts[targetLayout].meta.components.push(def);
        }
    };

    app.metadata.addSyncTask(function(metadata, options) {
        if (options.getPublic) {
            console.log('skipping external app sync for public metadata');
            return Promise.resolve();
        }

        if (app.config.externalManifestUrl && app.config.externalManifestUrl !== '') {
            return new Promise(function(r, e) {

                fetch(app.config.externalManifestUrl, {mode: 'cors'}).then(
                    function(response) {
                        response.json().then(function(manifest) {
                            _.each(manifest.layouts, function(def) {
                                if (def.module && def.layout) {
                                    addCompToLayout(metadata, def.module, def.layout, {
                                        'view': {
                                            'type': 'external-app',
                                            'name': manifest.name,
                                            'src': manifest.src
                                        }
                                    });
                                }
                                ;
                            });
                            r();
                        }).catch(function(error) {
                            e(error);
                        });
                    }
                ).catch(function(error) {
                    console.log(error, 'error fetching external app manifest');
                    r();
                });

            });
        } else {
            return Promise.resolve();
        }
    });
})(SUGAR.App);
