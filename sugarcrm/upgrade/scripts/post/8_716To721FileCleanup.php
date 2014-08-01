<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

class SugarUpgrade716To721FileCleanup extends UpgradeScript
{
    public $order = 8501;
    public $type = self::UPGRADE_CORE;

    public function run()
    {
        // if the from version is 7.1.6, 7.2.0, we should delete these files
        // as long as the to_version is 7.2.1 or newer.
        if ((version_compare($this->from_version, '7.1.5', '>') &&
                version_compare($this->from_version, '7.2.1', '<')) &&
            version_compare($this->to_version, '7.2.1', '>=')
        ) {
            $files = array(
                'clients/base/fields/listeditable',
                'clients/base/fields/date/detail.hbs',
                'clients/base/fields/date/list.hbs',
                'clients/base/fields/datetimecombo/detail.hbs',
                'clients/base/fields/datetimecombo/list.hbs',
                'clients/base/fields/shareaction/detail.hbs',
                'clients/base/views/detail',
                'clients/base/views/edit',
                'clients/base/views/raw',
                'clients/base/views/activitystream-bottom/activitystream-bottom.php',
                'clients/base/views/dnb-account-create/dnb-config.hbs',
                'clients/base/views/modulelist/favorites.hbs',
                'clients/base/views/modulelist/modulelist.hbs',
                'clients/base/views/modulelist/modulelist.js',
                'clients/base/views/modulelist/singlemenuPartial.hbs',
                'include/SugarObjects/templates/basic/clients/base/layouts',
                'include/SugarSearchEngine/SugarSearchEngineMappingHelper.php',
                'include/SugarSearchEngine/SugarSearchEngineSyncIndexer.php',
                'include/VarDefHandler/listvardefoverride.php',
                'jssource/src_files/clients/base/fields/listeditable',
                'jssource/src_files/clients/base/views/detail',
                'jssource/src_files/clients/base/views/edit',
                'jssource/src_files/clients/base/views/modulelist',
                'jssource/src_files/clients/base/views/raw',
                'jssource/src_files/modules/Forecasts/clients/base/plugins/DisableMassdelete.js',
                'jssource/src_files/modules/Notifications/clients/base/views',
                'jssource/src_files/modules/Notifications/clients/base/fields/datetimecombo',
                'jssource/src_files/modules/Styleguide/clients/base/views/docs',
                'modules/Accounts/clients/base/views/detail',
                'modules/Accounts/clients/base/views/edit',
                'modules/Bugs/clients/portal/layouts',
                'modules/Cases/clients/portal/layouts',
                'modules/Emails/clients/base/views/panel-top/panel-top.js',
                'modules/Forecasts/clients/base/plugins/DisableMassdelete.js',
                'modules/Home/index.php',
                'modules/KBDocuments/clients/portal/layouts/detail',
                'modules/Notifications/clients/base/layouts',
                'modules/Notifications/clients/base/fields/datetimecombo',
                'modules/Notifications/clients/base/views/raw',
                'modules/Opportunities/metadata/portal',
                'modules/Products/clients/base/views/list-headerpane',
                'modules/ProjectTask/clents',
                'modules/RevenueLineItems/clients/base/views/list-headerpane',
                'modules/Styleguide/clients/base/views/content',
                'modules/Styleguide/clients/base/views/docs',
                'modules/Styleguide/clients/base/views/list/list_doc.hbs',
                'modules/WebLogicHooks/clients/base/layouts',
                'modules/WebLogicHooks/clients/base/views/list-headerpane',
                'styleguide/assets/css/nvd3.css',
                'styleguide/content/css',
                'styleguide/content/wizard-modal.html',
                'styleguide/content/charts/sankey.html',
                'styleguide/content/charts/data/bubble_data.js',
                'styleguide/content/charts/data/flare.js',
                'styleguide/content/charts/data/funnel_data.js',
                'styleguide/content/charts/data/gauge_data.js',
                'styleguide/content/charts/data/horizbar_data.js',
                'styleguide/content/charts/data/line_data.js',
                'styleguide/content/charts/data/multibar_data.js',
                'styleguide/content/charts/data/pareto_data.js',
                'styleguide/content/charts/data/pie_data.js',
                'styleguide/content/charts/data/sankey-wonlost-source.json',
                'styleguide/content/charts/data/tree_data.js',
                'styleguide/content/charts/data/treemap_data.js',
                'styleguide/content/js/backbone-min.js',
                'styleguide/content/js/chart-utils.js',
                'styleguide/content/js/datatable-data.js',
                'styleguide/content/js/jquery-1.7.2.min.js',
                'styleguide/content/js/jquery-ui-1.10.0.custom.min.js',
                'styleguide/content/js/jquery-ui-1.8.18.custom.min.js',
                'styleguide/content/js/jquery.form.js',
                'styleguide/content/js/less-1.3.3.min.js',
                'styleguide/content/js/underscore-min.js',
                'styleguide/content/js/wizard.js',
                'styleguide/less/clients/mobile',
                'styleguide/less/clients/portal/alerts.less',
                'styleguide/less/modules/nvd3.less',
                'styleguide/less/sugar-specific/widgets.less',
                'styleguide/themes/clients/base/custom chart colors',
                'vendor/Elastica/Searchable.php',
                'vendor/Elastica/Exception/Abstract.php',
                'vendor/Elastica/Exception/BulkResponse.php',
                'vendor/Elastica/Exception/Client.php',
                'vendor/Elastica/Exception/Invalid.php',
                'vendor/Elastica/Exception/NotFound.php',
                'vendor/Elastica/Exception/NotImplemented.php',
                'vendor/Elastica/Exception/Response.php',
                'vendor/Elastica/Facet/Abstract.php',
                'vendor/Elastica/Filter/Abstract.php',
                'vendor/Elastica/Filter/And.php',
                'vendor/Elastica/Filter/Not.php',
                'vendor/Elastica/Filter/Or.php',
                'vendor/Elastica/Query/Abstract.php',
                'vendor/Elastica/Query/Array.php',
                'vendor/Elastica/Query/Field.php',
                'vendor/Elastica/Query/Text.php',
                'vendor/Elastica/Transport/Abstract.php',
                'vendor/Elastica/Type/Abstract.php'
            );

            $this->fileToDelete($files);
        }
    }
}
