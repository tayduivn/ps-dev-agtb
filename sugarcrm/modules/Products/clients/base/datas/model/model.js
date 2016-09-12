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
 * @class Model.Datas.Base.ProductsModel
 * @alias SUGAR.App.model.datas.BaseProductsModel
 * @extends Model.Bean
 */
({
    /**
     * @inheritdoc
     */
    isNew: function() {
        if (this.get('_notSaved') === true) {
            return true;
        }
        return !this.has(this.idAttribute);
    }
})
