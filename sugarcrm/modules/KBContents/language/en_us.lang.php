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
$mod_strings = array (
    'LBL_MODULE_NAME' => 'Knowledge Base',
    'LBL_MODULE_NAME_SINGULAR' => 'Knowledge Base Article',
    'LBL_MODULE_TITLE' => 'Knowledge Base Article',
    'LNK_NEW_ARTICLE' => 'Create Article',
    'LBL_LIST_ARTICLES' => 'View Articles',
    'LBL_KNOWLEDGE_BASE_ADMIN_MENU' => 'Settings',
    'LBL_EDIT_LANGUAGES' => 'Edit languages',
    'LBL_ADMIN_LABEL_LANGUAGES' => 'Available languages',
    'LBL_CONFIG_LANGUAGES_TITLE' => 'Available languages',
    'LBL_CONFIG_LANGUAGES_TEXT' => 'Conﬁgure languages that will be used in the Knowledge Base module.',
    'LBL_CONFIG_LANGUAGES_LABEL_KEY' => 'Language Code',
    'LBL_CONFIG_LANGUAGES_LABEL_NAME' => 'Language Label',
    'ERR_CONFIG_LANGUAGES_DUPLICATE' => 'It is not allowed to add language with the key that duplicates existing one.',
    'ERR_CONFIG_LANGUAGES_EMPTY' => 'It is not allowed to add language with empty key.',
    'LBL_SET_ITEM_PRIMARY' => 'Set Value as Primary',
    'LBL_ITEM_REMOVE' => 'Remove Item',
    'LBL_ITEM_ADD' => 'Add Item',
    'LBL_MODULE_ID'=> 'KBContents',
    'LBL_DOCUMENT_REVISION_ID' => 'Revision ID',
    'LBL_DOCUMENT_REVISION' => 'Revision',
    'LBL_NUMBER' => 'Number',
    'LBL_TEXT_BODY' => 'Body',
    'LBL_LANG' => 'Language',
    'LBL_PUBLISH_DATE' => 'Publish Date',
    'LBL_EXP_DATE' => 'Expiration Date',
    'LBL_DOC_ID' => 'Document ID',
    'LBL_APPROVED' => 'Approved',
    'LBL_REVISION' => 'Revision',
    'LBL_ACTIVE_REV' => 'Active Revision',
    'LBL_IS_EXTERNAL' => 'External Article',
    'LBL_KBDOCUMENT_ID' => 'KBDocument ID',
    'LBL_KBDOCUMENTS' => 'KBDocuments',
    'LBL_KBDOCUMENT' => 'KBDocument',
    'LBL_KBARTICLE' => 'Article',
    'LBL_KBARTICLES' => 'Articles',
    'LBL_KBARTICLE_ID' => 'Article Id',
    'LBL_USEFUL' => 'Useful',
    'LBL_NOT_USEFUL' => 'Not Useful',
    'LBL_RATING' => 'Rating',
    'LBL_VIEWED_COUNT' => 'Frequency',
    'LBL_DOWNLOAD_ALL' => 'Download All',
    'LBL_DOWNLOAD_ONE' => 'Download',
    'LBL_ATTACHMENTS' => 'Attachments',
    'LBL_ADD_ATTACHMENT' => 'Browse',
    'LBL_CATEGORIES' => 'Knowledge Base Categories',
    'LBL_CATEGORY_NAME' => 'Category',
    'LBL_USEFULNESS' => 'Usefulness',
    'LBL_CATEGORY_ID' => 'Category Id',
    'LBL_KBSAPPROVERS' => 'Approvers',
    'LBL_KBSAPPROVER_ID' => 'Approved By',
    'LBL_KBSAPPROVER' => 'Approved By',
    'LBL_KBSCASES' => 'Cases',
    'LBL_KBSCASE_ID' => 'Related Case',
    'LBL_KBSCASE' => 'Related Case',
    'LBL_MORE_MOST_USEFUL_ARTICLES' => 'More most useful published knowledge base articles...',
    'LBL_KBSLOCALIZATIONS' => 'Localizations',
    'LBL_LOCALIZATIONS_SUBPANEL_TITLE' => 'Localizations',
    'LBL_KBSREVISIONS' => 'Revisions',
    'LBL_REVISIONS_SUBPANEL_TITLE' => 'Revisions',
    'LBL_LISTVIEW_FILTER_ALL' => 'All Articles',
    'LBL_LISTVIEW_FILTER_MY' => 'My Articles',
    'LBL_CREATE_LOCALIZATION_BUTTON_LABEL' => 'Create Localization',
    'LBL_CREATE_REVISION_BUTTON_LABEL' => 'Create Revision',
    'LBL_CANNOT_CREATE_LOCALIZATION' =>
        'Unable to create a new localization as a localization version exists for all available languages.',
    'LBL_SPECIFY_PUBLISH_DATE' => 'Schedule this article to be published by specifying the Publish Date. Do you wish to continue without entering a Publish Date?',
    'LBL_PANEL_INMORELESS' => 'Usefulness',
    'LBL_MORE_OTHER_LANGUAGES' => 'More Other Languages...',
    'EXCEPTION_VOTE_USEFULNESS_NOT_AUTHORIZED' => 'You are not authorized to vote useful/not useful {moduleName}. Contact your administrator if you need access.',
    'LNK_NEW_KBCONTENT_TEMPLATE' => 'Create Template',
    'LNK_LIST_KBCONTENT_TEMPLATES' => 'View Templates',
    'LNK_LIST_KBCATEGORIES' => 'View Categories',
    'LBL_TEMPLATES' => 'Templates',
    'LBL_TEMPATE_LOAD_MESSAGE' => 'The template will overwrite all contents.' .
        ' Are you sure you want to use this template?',
    'LNK_IMPORT_KBCONTENTS' => 'Import Articles',
    'LBL_DELETE_CONFIRMATION_LANGUAGE' => 'All documents with this language will be deleted! Are you sure you want to delete this language?',
    'LBL_ASSIGNED_TO_ID' => 'Author ID',
    'LBL_ASSIGNED_TO' => 'Author',
    'LBL_CREATE_CATEGORY_PLACEHOLDER' => 'Press Enter to create or Esc to cancel',
    'LBL_KB_NOTIFICATION' => 'Document has been published.',
    'LBL_KB_PUBLISHED_REQUEST' => 'has assigned a Document to you for approval and publishing.',
    'LBL_KB_STATUS_BACK_TO_DRAFT' => 'Document status has been changed back to draft.',
    'LBL_OPERATOR_CONTAINING_THESE_WORDS' => 'containing these words',
    'LBL_OPERATOR_EXCLUDING_THESE_WORDS' => 'excluding these words',
    'ERROR_EXP_DATE_LOW' => 'The expiration date can not be before date of publishing.',
    'ERROR_ACTIVE_DATE_APPROVE_REQUIRED' => 'The Approved status requires publishing date.',
    'ERROR_ACTIVE_DATE_LOW' => 'The publish date should be more than current date.',
    'LBL_RECORD_SAVED_SUCCESS' => 'You successfully created the {{moduleSingularLower}} article <a href="#{{buildRoute model=this}}">{{name}}</a>.', // use when a model is available
    'TPL_SHOW_MORE_MODULE' => 'More {{module}} articles...',
);
