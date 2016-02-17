{*
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
*}

//BEGIN SUGARCRM flav=een ONLY
{if strval($parentFieldArray.$col) !== "DF_HIDDEN"}
//END SUGARCRM flav=een ONLY
    {if strval($parentFieldArray.$col) == "1" || strval($parentFieldArray.$col) == "yes" || strval($parentFieldArray.$col) == "on"}
{assign var="checked" value="CHECKED"}
{else}
{assign var="checked" value=""}
{/if}
<input type="checkbox" class="checkbox" disabled="true" {$checked}>
//BEGIN SUGARCRM flav=een ONLY
{/if}
//END SUGARCRM flav=een ONLY