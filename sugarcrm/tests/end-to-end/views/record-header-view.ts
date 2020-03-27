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

import BaseView from './base-view';
import {seedbed} from '@sugarcrm/seedbed';

/**
 * Represents header view PageObject.
 *
 * @class RecordHeaderView
 * @extends BaseView
 */
export default class RecordHeaderView extends BaseView {

    constructor(options) {
        super(options);

        this.selectors = this.mergeSelectors({
            $: '.headerpane',
            buttons: {
                // common buttons and actions
                'actions': '.actions:not([style*="display: none"]) a.btn.dropdown-toggle',
                'add': 'a[name="link_button"]:not(.hide)',
                'auditlog': 'a[name="audit_button"]:not(.hide)',
                'cancel': 'a[name="cancel_button"]:not(.hide)',
                'close': 'a[name="close"]:not(.hide)',
                'closebutton': 'a[name="close_button"]:not(.hide)',
                'copy': 'a[name="duplicate_button"]:not(.hide)',
                'create': 'a[name="create_button"]:not(.hide)',
                'delete': 'a[name="delete_button"]:not(.hide)',
                'done': 'a[name="done_button"]:not(.hide)',
                'edit': 'a[name="edit_button"]:not(.hide)',
                'historicalsummary': 'a[name="historical_summary_button"]:not(.hide)',
                'save': 'a[name="save_button"]:not(.hide)',
                'select': 'a[name="select_button"]:not(.hide)',
                'togglesidepanel': '.btn.btn-invisible.sidebar-toggle',
                'viewpersonalinfo': 'a[name="view_pii_button"]:not(.hide)',
                'share': 'a[name="share"]:not(.hide)',
                'findduplicates': 'a[name="find_duplicates_button"]:not(.hide)',
                'mergeduplicates': 'a[name="merge_duplicates_button"]:not(.hide)',

                // Calls, Meetings, and Tasks
                'closeandcreatenew': 'a[name="record-close-new"]:not(.hide)',
                'closecall': 'a[name="record-close"]:not(.hide)',
                'closemeeting': 'a[name="record-close"]:not(.hide)',
                'closetask': 'a[name="record-close"]:not(.hide)',

                // Cases module controls
                'createarticle': 'a[name="create_button"]:not(.hide)',

                // Data Privacy module controls
                'complete': 'a[name="complete_button"]:not(.hide)',
                'eraseandcomplete': 'a[name="erase_complete_button"]:not(.hide)',
                'markforerasure': 'a[name="mark_for_erasure_button"]:not(.hide)',
                'reject': 'a[name="reject_button"]:not(.hide)',

                // Emails module controls
                'reply': 'a[name="reply_button"]:not(.hide)',

                // Forecasts module controls
                'assignquota': 'a[name="assign_quota"]:not(.hide)',
                'commit': 'a[name="commit_button"]:not(.hide)',
                'exportcsv': 'a[name="export_button"]:not(.hide)',
                'savedraft': 'a[name="save_draft_button"]:not(.hide)',
                'settings': 'a[name="settings_button"]:not(.hide)',

                // Knowledge Base controls
                'createcategory': 'a[name="add_node_button"]:not(.hide)',
                'createlocalization': 'a[name="create_localization_button"]:not(.hide)',
                'createrevision': 'a[name="create_revision_button"]:not(.hide)',

                // Leads module controls
                'convert': 'a[name="lead_convert_button"]:not(.hide)',

                // ProductTemplates module controls
                'add2quote': 'a[name="add_to_quote_button"]:not(.hide)',

                // Prospects (Targets) module controls
                'converttarget': 'a[name="convert_button"]:not(.hide)',

                // Quotes module controls
                'createopportunity': 'a[name="convert_to_opportunity_button"]:not(.hide)',
                'emailquote': '.dropdown-inset a[data-action="email"]',

                // Revenue Line Items module controls (ENT+ only)
                'generatequote': 'a[name="convert_to_quote_button"]:not(.hide)',

                // Sugar BPM controls
                'design_pbr': 'a[name="design_businessrules"]:not(.hide)',
                'design_pet': 'a[name="design_emailtemplates"]:not(.hide)',
                'bpm_cancel_button': 'a[name="project_cancel_button"]:not(.hide)',
                'bpm_import_button': 'a[name="project_finish_button"]:not(.hide)',
                'approve': 'a[name="approve_button"]:not(.hide)',
                'route': 'a[name="reject_button"]:not(.hide)',
                'status': 'a[name="status"]:not(.hide)',
                'addnotes': 'a[name="add-notes"]:not(.hide)',
                'selectnewprocessuser': 'a[name="duplicate_button"]:not(.hide)',

                // Process Email template
                'email_template_cancel_button': 'a[name="emailtemplates_cancel_button"]:not(.hide)',
                'email_template_import_button': 'a[name="emailtemplates_finish_button"]:not(.hide)',

                // Business Rules
                'business_rules_cancel_button': 'a[name="businessrules_cancel_button"]:not(.hide)',
                'business_rules_import_button': 'a[name="businessrules_finish_button"]:not(.hide)',

                // Home Dashboard control
                'addbutton': 'a[name="add_button"]:not(.hide)',

                // Tile View Create button
                'tileviewcreate': 'a[name="pipeline_create_button"]:not(.hide)',

                // Serve Console and Renewal Console
                'edit_overview_tab_button': 'a[name="edit_overview_tab_button"]:not(.hide)',
                'edit_module_tabs_button': 'a[name="edit_module_tabs_button"]:not(.hide)',
            },

            title: {
                'old': 'h1 [data-name="title"] span.list-headerpane',
                'new': 'h1 [data-name="title"] span.list-headerpane div'
            }
        });
    }

    public async checkIsButtonActive(buttonName) {
        let isDisabled = await this.driver.isExisting(this.$(`buttons.${buttonName.toLowerCase()}`) + '.disabled');
        return !isDisabled;
    }
}
