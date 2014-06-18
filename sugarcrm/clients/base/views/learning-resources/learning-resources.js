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
 * Learning resources dashlet that will contain the content and links to
 * SugarCRM training material.
 *
 * The resources information is populated through metadata. More can be added:
 * ```
 * // ...
 * 'resources' => array(
 *     'resource_id' => array(
 *         'css_class' => 'resource-class',
 *         'color' => 'resource-color',
 *         'icon' => 'icon-resource-icon',
 *         'url' => 'http://url.for.resource.com/',
 *         'link' => 'LBL_LEARNING_RESOURCES_RESOURCE_LINK',
 *         'teaser' => 'LBL_LEARNING_RESOURCES_RESOURCE_TEASER',
 *     ),
 *     //...
 * ),
 * //...
 * ```
 *
 * @class View.Views.Base.LearningResourcesView
 * @alias SUGAR.App.view.views.BaseLearningResourcesView
 * @extends View.View
 */
({
    tagName: 'ul',
    className: 'resource-list',

    plugins: ['Dashlet'],

    /**
     * The resources map that are metadata driven.
     */
    resources: {},

    /**
     * @inheritDoc
     *
     * Define the {@link #resources} directly from the metadata.
     *
     * FIXME this is on `_renderHtml` instead of `initialize` because
     * `dashletConfig` (metadata) is only available at this time.
     * This needs to be reviewed after the SC-1373 refactor goes in.
     */
    _renderHtml: function() {
        this.resources = this.dashletConfig.resources;
        this._super('_renderHtml');
    }
})
