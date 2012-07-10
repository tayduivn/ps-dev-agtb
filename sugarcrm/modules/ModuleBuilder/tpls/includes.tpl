{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
<script type="text/javascript" src="{sugar_getjspath file='modules/ModuleBuilder/javascript/JSTransaction.js'}" ></script>
<script>
	var jstransaction = new JSTransaction();
	{literal}
	if (SUGAR.themes.tempHideLeftCol){
    	SUGAR.themes.tempHideLeftCol();
    }
    {/literal}
</script>

<link rel="stylesheet" type="text/css" href="{sugar_getjspath file="modules/ModuleBuilder/tpls/LayoutEditor.css"}" />
<link rel="stylesheet" type="text/css" href="{sugar_getjspath file="include/ytree/TreeView/css/folders/tree.css"}" />

<script type="text/javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/studio2.js'}' ></script>
<script type="text/javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/studio2PanelDD.js'}' ></script>
<script type="text/javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/studio2RowDD.js'}' ></script>
<script type="text/javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/studio2FieldDD.js'}' ></script>
<script type="text/javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/studiotabgroups.js'}'></script>
<script type="text/javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/studio2ListDD.js'}' ></script>

<!--end ModuleBuilder Assistant!-->
<script type="text/javascript" language="Javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/ModuleBuilder.js'}'></script>
<script type="text/javascript" language="Javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/SimpleList.js'}'></script>
<script type="text/javascript" src='{sugar_getjspath file ='modules/ModuleBuilder/javascript/JSTransaction.js'}' ></script>
<script type="text/javascript" src='{sugar_getjspath file ='include/javascript/tiny_mce/tiny_mce.js'}' ></script>


<!--TODO move this to the minified--->
<script src='sidecar/lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js'></script>
<script src='sidecar/lib/backbone/underscore.js'></script>
<script src='sidecar/lib/backbone/backbone.js'></script>
<script src='sidecar/lib/handlebars/handlebars-1.0.0.beta.6.js'></script>
<script src='sidecar/lib/stash/stash.js'></script>
<script src='sidecar/lib/async/async.js'></script>
<script src='sidecar/lib/chosen/chosen.jquery.js'></script>
<script src='sidecar/lib/sinon/sinon.js'></script>
<script src='sidecar/lib/sugarapi/sugarapi.js'></script>
<script src='sidecar/src/app.js'></script>
<script src='include/javascript/sugarAuthStore.js'></script>
<script src='sidecar/src/utils/utils.js'></script>
<script src='sidecar/src/core/cache.js'></script>
<script src='sidecar/src/core/events.js'></script>
<script src='sidecar/src/core/error.js'></script>
<script src='sidecar/src/view/template.js'></script>
<script src='sidecar/src/core/context.js'></script>
<script src='sidecar/src/core/controller.js'></script>
<script src='sidecar/src/core/router.js'></script>
<script src='sidecar/src/core/language.js'></script>
<script src='sidecar/src/core/metadata-manager.js'></script>
<script src='sidecar/src/core/acl.js'></script>
<script src='sidecar/src/core/user.js'></script>
<script src='sidecar/src/utils/logger.js'></script>
<script src='sidecar/src/data/bean.js'></script>
<script src='sidecar/src/data/bean-collection.js'></script>
<script src='sidecar/src/data/data-manager.js'></script>
<script src='sidecar/src/data/validation.js'></script>
<script src='sidecar/src/view/hbt-helpers.js'></script>
<script src='sidecar/src/view/view-manager.js'></script>
<script src='sidecar/src/view/component.js'></script>
<script src='sidecar/src/view/view.js'></script>
<script src='sidecar/src/view/field.js'></script>
<script src='sidecar/src/view/layout.js'></script>
<script src='sidecar/src/view/alert.js'></script>
<script type='text/javascript' src='include/SugarCharts/Jit/js/Jit/jit.js'></script>
<script type='text/javascript' src='include/SugarCharts/Jit/js/sugarCharts.js'></script>


<link rel="stylesheet" type="text/css" href="{sugar_getjspath file="modules/ModuleBuilder/tpls/MB.css"}" />