//FILE SUGARCRM flav=ent ONLY
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

({
    extendsFrom: 'CreateView',

    initialize: function(options) {
        app.view.invokeParent(this, {type: 'view', name: 'create', method: 'initialize', args: [options]});
        this.template = app.template.getView('record');
    },

    /**
     * Used by the alert openRLICreate method
     */
    createdModel: undefined,

    /**
     * Used by the openRLICreate method
     */
    listContext: undefined,

    /**
     * The original success message to call from the new one we set in the getCustomSaveOptions method
     */
    originalSuccess: undefined,

    /**
     * @override
     */
    getCustomSaveOptions: function(options) {
        this.createdModel = this.model;
        // since we are in a drawer
        this.listContext = this.context.parent || this.context;
        this.originalSuccess = options.success;

        var success = _.bind(function() {
            this.originalSuccess();
            this.showRLIWarningMessage(this.listContext.get('module'));
        }, this);

        return {
            success: success
        };
    },

    /**
     * Display the warning message about missing RLIs
     * @param string module     The module that we are currently on.
     */
    showRLIWarningMessage: function(module) {
        var alert = app.alert.show('opp-rli-create', {
            level: 'warning',
            autoClose: false,
            title: app.lang.get('LBL_ALERT_TITLE_WARNING') + ':',
            messages: app.lang.get('TPL_RLI_CREATE', module)
        });
        alert.$el.find('a[href]').on('click.open', _.bind(function() {
            // remove the event handler
            alert.$el.find('a[href]').off('click.open');
            app.alert.dismiss('opp-rli-create');
            this.openRLICreate();
        }, this));
    },

    /**
     * Open a new Drawer with the RLI Create Form
     */
    openRLICreate: function() {
        var model = this.createLinkModel(this.createdModel || this.model, 'revenuelineitems');

        app.drawer.open({
            layout: 'create-actions',
            context: {
                create: true,
                module: model.module,
                model: model
            }
        }, _.bind(function(model) {
            if (!model) {
                return;
            }

            var ctx = this.listContext || this.context;

            ctx.resetLoadFlag();
            ctx.set('skipFetch', false);
            ctx.loadData();
        }, this));
    },

    /**
     * Create a new linked Bean model which is related to the parent bean model
     * It populates related fields from the parent bean model attributes
     * All related fields are defined in the relationship metadata
     *
     * If the related field contains the auto-populated fields,
     * it also copies the auto-populate fields
     *
     * @param {Model} Parent Bean Model
     * @param {String} name of relationship link
     */
    createLinkModel: function(parentModel, link) {
        var model = app.data.createRelatedBean(parentModel, null, link),
            relatedFields = app.data.getRelateFields(parentModel.module, link);

        if (!_.isEmpty(relatedFields)) {
            model._defaults = model._defaults || {};

            _.each(relatedFields, function(field) {
                model.set(field.name, parentModel.get(field.rname));
                model.set(field.id_name, parentModel.get("id"));
                model._defaults[field.name] = model.get(field.name);
                model._defaults[field.id_name] = model.get(field.id_name);

                if (field.populate_list) {
                    _.each(field.populate_list, function(target, source) {
                        source = _.isNumber(source) ? target : source;
                        if (!_.isUndefined(parentModel.get(source)) && app.acl.hasAccessToModel('edit', model, target)) {
                            model.set(target, parentModel.get(source));
                            model._defaults[target] = model.get(target);
                        }
                    }, this);
                }
            }, this);
        }

        return model;
    }

})
