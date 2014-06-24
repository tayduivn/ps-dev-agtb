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
/**
 * Link action used in Subpanels.
 *
 * It needs to be sticky so that we keep things lined up nicely.
 *
 * @class View.Fields.Base.LinkActionField
 * @alias SUGAR.App.view.fields.BaseLinkActionField
 * @extends View.Fields.Base.StickyRowactionField
 */
({
    extendsFrom: 'StickyRowactionField',
    events: {
        'click a[name=select_button]': 'openSelectDrawer'
    },
    /**
     * Event handler for the select button that opens a link selection dialog in a drawer for linking
     * an existing record
     */
    openSelectDrawer: function() {
        if (this.isDisabled()) {
            return;
        }
        var parentModel = this.context.get('parentModel'),
            linkModule = this.context.get('module'),
            link = this.context.get('link'),
            self = this;

        app.drawer.open({
            layout: 'selection-list',
            context: {
                module: linkModule,
                recParentModel: parentModel,
                recLink: link,
                recContext: this.context,
                recView: this.view
            }
        }, function(model) {
            if (!model) {
                return;
            }
            var relatedModel = app.data.createRelatedBean(parentModel, model.id, link),
                options = {
                    //Show alerts for this request
                    showAlerts: true,
                    relate: true,
                    success: function(model) {
                        //We've just linked a related, however, the list of records from
                        //loadData will come back in DESC (reverse chronological order with
                        //our newly linked on top). Hence, we reset pagination here.
                        self.context.get('collection').resetPagination();
                        self.context.resetLoadFlag();
                        self.context.set('skipFetch', false);
                        //Reset limit on context so we don't "over fetch" (lose pagination)
                        var collectionOptions = self.context.get('collectionOptions') || {};
                        if (collectionOptions.limit) self.context.set('limit', collectionOptions.limit);
                        self.context.loadData({
                            success: function() {
                                self.view.layout.trigger('filter:record:linked');
                            },
                            error: function(error) {
                                app.alert.show('server-error', {
                                    level: 'error',
                                    messages: 'ERR_GENERIC_SERVER_ERROR'
                                });
                            }
                        });
                    },
                    error: function(error) {
                        app.alert.show('server-error', {
                            level: 'error',
                            messages: 'ERR_GENERIC_SERVER_ERROR'
                        });
                    }
                };
            relatedModel.save(null, options);
        });
    },
    /**
     * A side effect of linking an existing record is that in the process, we could be deleting an existing
     * required relationship.
     * So here we prevent user from doing this by disabling the action.
     *
     * Returns false if relationship is required otherwise calls parent for additional ACL checks
     * @return {Boolean} true if allow access, false otherwise
     * @override
     */
    isDisabled: function() {
        if (this._super('isDisabled')) {
            return true;
        }
        var link = this.context.get('link'),
            parentModule = this.context.get('parentModule'),
            required = app.utils.isRequiredLink(parentModule, link);
        return required;
    }
})
