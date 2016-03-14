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
 * @deprecated Since 7.9. Use {@link View.Layouts.Base.CreateLayout} instead.
 *
 * @class View.Layouts.Base.Emails.ComposeLayout
 * @alias SUGAR.App.view.views.BaseEmailsComposeLayout
 */
({
    plugins: ['ShortcutSession'],

    shortcuts: [
        'Sidebar:Toggle',
        'Record:Cancel',
        'Compose:Action:More',
        'DragdropSelect2:SelectAll'
    ]
})
