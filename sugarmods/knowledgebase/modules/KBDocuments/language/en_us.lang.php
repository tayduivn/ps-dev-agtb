<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: en_us.lang.php 20847 2007-03-12 16:30:47 +0000 (Mon, 12 Mar 2007) vineet $
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$mod_strings = array (
	//module
	'LBL_MODULE_NAME' => 'KBDocuments',
	'LBL_MODULE_TITLE' => 'Knowledge Base Article',
	'LNK_NEW_ARTICLE' => 'Create Article',
	'LNK_KBDOCUMENT_LIST'=> 'Documents List',
	'LBL_DOC_REV_HEADER' => 'Document Revisions',
	'LBL_SEARCH_FORM_TITLE'=> 'Document Search',
	'LBL_KBDOC_TAGS' => 'Article Tags:',
	'LBL_KBDOC_BODY' => 'Article Body:',
	'LBL_KBDOC_APPROVED_BY' =>'Approver:',
	'LBL_KBDOC_ATTACHMENT' =>'Kbdoc_attachment',
	'LBL_KBDOC_ATTS_TITLE' =>'Download Attachments:',	
	'LBL_SEND_EMAIL'  => 'Send Email',
	'LBL_SELECT_TAG_BUTTON_TITLE' => 'Select',
	'LBL_ATTACHMENTS' => 'Attachments:',
	'LBL_EMBEDED_IMAGES' => 'Embedded Images:',
	'LBL_SHOW_ARTICLE_DETAILS' => 'Show More Details',
	'LBL_HIDE_ARTICLE_DETAILS' => 'Hide Details',
	'LBL_TAGS' => 'Tags:',
	'LBL_NOT_AN_ADMIN_USER' => 'Not an admin user',
	'LBL_NOT_A_VALID_FILE' => 'Not a valid file',
	
	//Tag tree related labels    
    'LBL_SELECT_A_NODE_FROM_TREE' => 'Select a node from tree',
    'LBL_SELECT_A_NODE_FROM_TREE' => 'Create New Tag',
    'LBL_SEARCH'  =>'Search',
    'LBL_NEW_TAG_NAME' => 'New Tag Name:',
	
	//vardef labels	
	'LBL_KBDOCUMENT_ID' => 'Document Id',
	'LBL_ARTICLE_TITLE' => 'Title:',
	'LBL_ARTICLE_AUTHOR' => 'Author:',
	'LBL_ARTICLE_APPROVED_BY' =>'Approver:',
	'LBL_ARTICLE_BODY' => 'Article Body:',
	'LBL_NAME' => 'Document Name:',
	'LBL_DESCRIPTION' => 'Description',
	'LBL_CATEGORY' => 'Category',
	'LBL_SUBCATEGORY' => 'Sub Category',
	'LBL_STATUS' => 'Status',
	'LBL_CREATED_BY'=> 'Created by',
	'LBL_DATE_ENTERED'=> 'Date Entered',
    'LBL_DATEENTERED'=> 'Date Entered:',
	'LBL_DATE_MODIFIED'=> 'Date Modified',
	'LBL_DELETED' => 'Deleted',
	'LBL_MODIFIED'=> 'Modified by id',
	'LBL_MODIFIED_USER' => 'Modified by',
	'LBL_CREATED'=> 'Created by',
	'LBL_RELATED_DOCUMENT_ID'=>'Related Dcocument Id',
	'LBL_RELATED_DOCUMENT_REVISION_ID'=>'Related Document Revision Id',
	'LBL_IS_TEMPLATE'=>'Is a Template',
	'LBL_TEMPLATE_TYPE'=>'Document Type',

	'LBL_REVISION_NAME' => 'Revision Number',
	'LBL_KBDOCUMENT_REVISION_NUMBER' => 'KBDocument Revision Number',
	'LBL_MIME' => 'Mime Type',
	'LBL_REVISION' => 'Revision',
	'LBL_DOCUMENT' => 'Related Document',
	'LBL_LATEST_REVISION' => 'Latest Revision Id',
	'LBL_CHANGE_LOG'=> 'Change Log',
	'LBL_ACTIVE_DATE'=> 'Publish Date',
	'LBL_EXPIRATION_DATE' => 'Expiration Date',
	'LBL_FILE_EXTENSION'  => 'File Extension',

    'LBL_KBDOC_TAGS' => 'Document Tags:',
	'LBL_KBDOC_BODY' => 'Document Body:',
	'LBL_KBDOC_APPROVED_BY' =>'Approved By:',
	'LBL_KBDOC_ATTACHMENT' =>'Kbdoc_attahment',
	'LBL_KBDOC_ATTS_TITLE' =>'Download Attachments:',

    'LBL_KNOWLEDGE_BASE_SEARCH' => 'KNOWLEDGE BASE',
	'LBL_KNOWLEDGE_BASE_ADMIN' => 'KNOWLEDGE BASE ADMIN',
    'LBL_KNOWLEDGE_BASE_ADMIN_MENU' => 'Knowledge Base Admin',
     
	'LBL_CAT_OR_SUBCAT_UNSPEC'=>'Unspecified',
	//document edit and detail view
	'LBL_KBDOC_TAGS' => 'Tags:',
	'LBL_KBDOC_BODY' => 'Body:',
	'LBL_KBDOC_APPROVED_BY' =>'Approved By:',
	'LBL_KBDOC_ATTACHMENT' =>'Kbdoc_attahment',
	'LBL_KBDOC_ATTS_TITLE' =>'Download Attachments:',
	'LBL_DOC_NAME' => 'Document Name:',
	'LBL_FILENAME' => 'File Name:',
	'LBL_DOC_VERSION' => 'Revision:',
	'LBL_CATEGORY_VALUE' => 'Category:',
	'LBL_SUBCATEGORY_VALUE'=> 'Sub Category:',
	'LBL_DOC_STATUS'=> 'Status:',
	'LBL_LAST_REV_CREATOR' => 'Revision Created By:',
	'LBL_LAST_REV_DATE' => 'Revision Date:',
	'LBL_DOWNNLOAD_FILE'=> 'Attachments:',
	'LBL_DET_RELATED_DOCUMENT'=>'Related Document:',
	'LBL_DET_RELATED_DOCUMENT_VERSION'=>"Related Document's Revision:",
	'LBL_DET_IS_TEMPLATE'=>'Template? :',
	'LBL_DET_TEMPLATE_TYPE'=>'Document Type:',
	'LBL_IS_EXTERNAL_ARTICLE' => 'External Article? :',
	'LBL_SHOW_TAGS' => 'Show More Tags',
    'LBL_HIDE_TAGS' => 'Hide Tags',
// BEGIN SUGARCRM flav=pro ONLY 
	'LBL_TEAM'=> 'Team:',
// END SUGARCRM flav=pro ONLY 
	'LBL_DOC_DESCRIPTION'=>'Description:',
	'LBL_KBDOC_SUBJECT' =>'Subject:',
	'LBL_DOC_ACTIVE_DATE'=> 'Publish Date:',
	'LBL_DOC_EXP_DATE'=> 'Expiration Date:',

	//document list view.
	'LBL_LIST_ARTICLES' => 'Articles',
	'LBL_LIST_FORM_TITLE' => 'Document List',
	'LBL_LIST_DOCUMENT' => 'Document',
	'LBL_LIST_CATEGORY' => 'Category',
	'LBL_LIST_SUBCATEGORY' => 'Sub Category',
	'LBL_LIST_REVISION' => 'Revision',
	'LBL_LIST_LAST_REV_CREATOR' => 'Published By',
	'LBL_LIST_LAST_REV_DATE' => 'Revision Date',
	'LBL_LIST_VIEW_DOCUMENT'=>'View',
    'LBL_LIST_DOWNLOAD'=> 'Download',
	'LBL_LIST_ACTIVE_DATE' => 'Publish Date',
	'LBL_LIST_EXP_DATE' => 'Expiration Date',
	'LBL_LIST_STATUS'=>'Status',
    'LBL_ARTICLE_AUTHOR_LIST' => 'Author',

	//document search form.
	'LBL_SF_DOCUMENT' => 'Document Name:',
	'LBL_SF_CATEGORY' => 'Category:',
	'LBL_SF_SUBCATEGORY'=> 'Sub Category:',
	'LBL_SF_ACTIVE_DATE' => 'Publish Date:',
	'LBL_SF_EXP_DATE'=> 'Expiration Date:',

	'DEF_CREATE_LOG' => 'Document Created',
    
    //kbdocument full text search form.
    'LBL_TAB_SEARCH' => 'Search',
    'LBL_TAB_BROWSE' => 'Browse',
    'LBL_TAB_ADVANCED' => 'Advanced',
    'LBL_MENU_FTS' => 'Full Text Search',
    'LBL_FTS_EMPTY_STRING' =>  'Cannot perform full text search on an empty string',
    'LBL_SEARCH_WITHIN' => 'Search within:',      
    'LBL_CONTAINING_THESE_WORDS' => 'Containing these words:',     
    'LBL_EXCLUDING_THESE_WORDS' => 'Excluding these words:',  
    'LBL_UNDER_THIS_TAG' => 'Using This Tag:',
    'LBL_PUBLISHED' => 'Published:',
    'LBL_EXPIRES' => 'Expires:',
    'LBL_TIMES_VIEWED' => 'Viewing Frequency:',    
    'LBL_ATTACHMENTS' => 'Attachments:',
    'LBL_SAVE_SEARCH_AS' => 'Save this search as:',
    'LBL_SAVE_SEARCH_AS_HELP' => 'This saves your specified entries as a Saved Search filter for Knowledge Base.',
    'LBL_PREVIOUS_SAVED_SEARCH' => 'Previous Saved Searches:',
    'LBL_PREVIOUS_SAVED_SEARCH_HELP' => 'Edit or Delete an existing Saved Search.',
    'LBL_UPDATE' => 'Update',
    'LBL_DELETE' => 'Delete',
    'LBL_SHOW_OPTIONS' => 'Show More Options',
    'LBL_HIDE_OPTIONS' => 'Hide More Options',
    'LBL_AND' => 'and',
    'LBL_SEARCH' => 'Search',
    'LBL_CLEAR' => 'Clear',
    'LBL_LIST_KBDOC_APPROVER_NAME' => 'Approver Name',
    'LBL_LIST_VIEWING_FREQUENCY' => 'Frequency',  
    'LBL_ARTICLE_PREVEW_UNAVAILABLE_NO_DOCUMENT' => 'Preview is not available, Document record was not found.',
    'LBL_ARTICLE_PREVEW_UNAVAILABLE_NO_CONTENT' => 'Preview is not available, Document exists, but no content has been created yet.',
    'LBL_UNTAGGED_ARTICLES_NODE' => 'Untagged Articles',
    'LBL_TOP_TEN_LIST_TITLE' => 'Top Ten Most Viewed Articles',
    'LBL_SHOW_SYNTAX_HELP' => 'Show Syntax Help',
    'LBL_HIDE_SYNTAX_HELP' => 'Hide Syntax Help',
    'LBL_MISMATCH_QUOTES_ERR' => 'Your query will not work as entered.  There must be a closing double quote for every opening double quote to make a pair.  If you wish to search for a string with a double quote, the escape it with a backslash (\")', 
    'LBL_SYNTAX_CHEAT_SHEET' => 
        '<B>Query syntax Cheat Sheet:</b><P>
        
        1.   "+"  means must have <br> 
        2.   "-" means should not have (not necessary as we have a text field for words to be excluded)<br>
        3.   Words within double quotes (" ") will be treated as a combination for grouping. Note that there must be an event amount of double quotes or search will not run.  If you must search for a double quote, then escape it with a backslash (\")<br> 
        4.   Single Quotes (\') will be treated literally as a single quote, not used for grouping.
        </p>

        <p><b>Example: </b><br>
        To query for all articles with the words "Sugar" or "CRM", that must also have the words "Knowledge Base", and "cool", but should not have the words "demo" or "past tense", you would enter the following string:<br>
        Sugar CRM +"Knowledge Base" +cool -demo -"Past Tense"</p>

        <p><b>Notes: </b><br>
        Case does not matter<br>
        Space or commas are acceptable delimiters<br>
        there is no space between "-" or "+" and words</p>',
        
    //for hovering over tree
    'LBL_CHILD_TAG_IN_TREE_HOVER' => 'Child Tag',
    'LBL_CHILD_TAGS_IN_TREE_HOVER' => 'Child Tags',
    'LBL_ARTICLE_IN_TREE_HOVER' => 'Article',
    'LBL_ARTICLES_IN_TREE_HOVER' => 'Articles',
    'LBL_THIS_TAG_CONTAINS_TREE_HOVER' => 'This tag contains',
    
	//error messages
	'ERR_DOC_NAME'=>'Document Name',
	'ERR_DOC_ACTIVE_DATE'=>'Publish Date',
	'ERR_DOC_EXP_DATE'=> 'Expiration Date',
	'ERR_FILENAME'=> 'File name',
	'ERR_DOC_VERSION'=> 'Document Version',
	'ERR_DELETE_CONFIRM'=> 'Do you want to delete this document revision?',
	'ERR_DELETE_LATEST_VERSION'=> 'You are not allowed to delete the latest revision of a document.',
	'LNK_NEW_MAIL_MERGE' => 'Mail Merge',
	'LBL_MAIL_MERGE_DOCUMENT' => 'Mail Merge Template:',

	'LBL_TREE_TITLE' => 'Documents',
	//sub-panel vardefs.
	'LBL_LIST_DOCUMENT_NAME'=>'Document Name',
	'LBL_CONTRACT_NAME'=>'Contract Name:',
	'LBL_LIST_IS_TEMPLATE'=>'Template?',
	'LBL_LIST_TEMPLATE_TYPE'=>'Document Type',
	'LBL_LIST_SELECTED_REVISION'=>'Selected Revision',
	'LBL_LIST_LATEST_REVISION'=>'Latest Revision',
	'LBL_CASES_SUBPANEL_TITLE'=>'Related Cases',
	'LBL_EMAILS_SUBPANEL_TITLE' => 'Related Emails',
    'LBL_CONTRACTS_SUBPANEL_TITLE'=>'Related Contracts',
	'LBL_LAST_REV_CREATE_DATE'=>'Last Revision Create Date',
    //'LNK_DOCUMENT_CAT'=>'Document Categories',
    'LBL_KEYWORDS' => 'Keywords:',
    'LBL_CASES' =>'Cases',
    'LBL_EMAILS' =>'Emails',
    
   //Admin screen messages
    'LBL_DEFAULT_ADMIN_MESSAGE' =>'Select an action from the dropdown list',
    'LBL_SELECT_PARENT_TAG_MESSAGE' =>'Select the parent tag from tree',
    'LBL_SELECT_TAG_TO_BE_DELETED_FROM_TREE' => 'Select tag(s) to be deleted from tree',
    'LBL_SELECT_TAG_TO_BE_RENAMED_FROM_TREE' =>'Select the tag to be renamed from tree',
       
     //Tag creation messages
    'LBL_TAG_ALREADY_EXISTS' => 'Tag already exists',
    'LBL_TYPE_THE_NEW_TAG_NAME' => 'Type the new tag name',
    'LBL_SAVING_THE_TAG' => 'Saving the Tag...',
    'LBL_CREATING_NEW_TAG' => 'Creating New Tag...',
    'LBL_TAGS_ROOT_LABEL' => 'Tags',            
    'LBL_FAQ_TAG_NOT_RENAME_MESSAGE' => 'FAQs Tag can not be renamed',
    'LBL_SELECT_ARTICLES_TO_BE_MOVED_TO_OTHER_TAG' => 'Select Articles First ',
    'LBL_SELECT_ARTICLES_TO_APPLY_TAGS' => 'Select Articles To Apply Tags',
    'LBL_SELECT_ARTICLES_TO_DELETE'  => 'Select Articles First ',
    'LBL_SELECT_TAGS_TO_DELETE'  => 'Select Tags To Delete',
    'LBL_SELECT_A_TAG_FROM_TREE' => 'Select A Tag From Tree',
    'LBL_SELECT_TAGS_FROM_TREE'  => 'Select Tags From Tree',
    'LBL_MOVING_ARTICLES_TO_TAG' =>'Moving articles to tag...',
    'LBL_APPLYING_TAGS_TO_ARTICLES' =>'Applying tags on articles ...',
    'LBL_ROOT_TAG_MESSAGE' => 'Root tag can not be selected/linked to article',
    'LBL_ROOT_TAG_CAN_NOT_BE_RENAMED' => 'Root tag can not be renamed',
    'LBL_SOURCE_AND_TARGET_TAGS_ARE_SAME' => 'Source and Target tags are same',
    
    //Tag button labels
    'LBL_DELETE_TAG'  => 'Delete Tag',
    'LBL_SELECT_TAG'  => 'Select Tag',
    'LBL_APPLY_TAG'  =>  'Apply Tag',
    'LBL_MOVE'  =>   'Move',
    'LBL_LAUNCHING_TAG_BROWSING' => 'Launching Tag Browsing ...',
    'LBL_THERE_WAS_AN_ERROR_HANDLING_TAGS' => 'There was an error handling this request for tags.',
    'LBL_ERROR_NOT_A_FILE_INPUT_ELEMENT' =>'Error: Not a file input element',
    'LBL_CREATE_NEW_TAG'  => 'Create New Tag',
    'LBL_SEARCH_TAG'  => 'Search',
    'LBL_TAG_NAME'  =>'Tag Name:',
    'LBL_SELECT_TAGS_TO_DELETE' => 'Select Tags to be deleted',
    'LBL_TYPE_TAG_NAME_TO_SEARCH' => 'Type tag name to be searched',
    'LBL_KB_NOTIFICATION' => 'Document has been published',
    'LBL_KB_PUBLISHED_REQUEST' => 'has assigned a Document to you for approval and publishing.',
    'LBL_KB_STATUS_BACK_TO_DRAFT' => 'Document status has been changed back to draft',    
);
?>