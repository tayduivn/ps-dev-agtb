{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
{if empty({{sugarvar key='value' string=true}})}
{assign var="value" value={{sugarvar key='default_value' string=true}} }
{else}
{assign var="value" value={{sugarvar key='value' string=true}} }
{/if}  

<!--//BEGIN SUGARCRM flav=richtext ONLY -->
{{if empty($displayParams.textonly)}}
<div class="yui-skin-sam">
{{/if}}
<!--//END SUGARCRM flav=richtext ONLY -->

{{capture name=idname assign=idname}}{{sugarvar key='name'}}{{/capture}}
{{if !empty($displayParams.idName)}}
    {{assign var=idname value=$displayParams.idName}}
{{/if}}


<textarea  id='{{$idname}}' name='{{$idname}}'
rows="{{if !empty($displayParams.rows)}}{{$displayParams.rows}}{{elseif !empty($vardef.rows)}}{{$vardef.rows}}{{else}}{{4}}{{/if}}" 
cols="{{if !empty($displayParams.cols)}}{{$displayParams.cols}}{{elseif !empty($vardef.cols)}}{{$vardef.cols}}{{else}}{{60}}{{/if}}" 
title='{{$vardef.help}}' tabindex="{{$tabindex}}" {{$displayParams.field}}>{$value}</textarea>


<!--//BEGIN SUGARCRM flav=richtext ONLY -->
{{if empty($displayParams.textonly)}}
</div>
{{/if}}
<!--//END SUGARCRM flav=richtext ONLY -->

<!--//BEGIN SUGARCRM flav=richtext ONLY -->
{{if empty($displayParams.textonly)}}
<link rel="stylesheet" type="text/css" href="include/javascript/yui/build/assets/skins/sam/editor.css"/>
<script type="text/javascript" language="javascript">
var {{$idname}}_loader = new YAHOO.util.YUILoader({ldelim}
    require : ["editor", "resize"],
    loadOptional: false,
   
    onSuccess: function() {ldelim}
		var myEditor = new YAHOO.widget.Editor('{{$idname}}', {ldelim}
		    height: '{$RICH_TEXT_EDITOR_HEIGHT}',
		    width: '{$RICH_TEXT_EDITOR_WIDTH}',
		    dompath: true,
		    animate: true,
		    handleSubmit: true,
		    {literal}
		    toolbar: {
			    buttons: [
				    { group: 'fontstyle', label: 'Font Name and Size',
				        buttons: [
				            { type: 'select', label: 'Arial', value: 'fontname', disabled: false,
				                menu: [
				                    { text: 'Arial', checked: true },
				                    { text: 'Arial Black' },
				                    { text: 'Comic Sans MS' },
				                    { text: 'Courier New' },
				                    { text: 'Lucida Console' },
				                    { text: 'Tahoma' },
				                    { text: 'Times New Roman' },
				                    { text: 'Trebuchet MS' },
				                    { text: 'Verdana' }
				                ]
				            },
				            { type: 'spin', label: '13', value: 'fontsize', range: [ 9, 75 ], disabled: false }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'textstyle', label: 'Font Style',
				        buttons: [
				            { type: 'push', label: 'Bold CTRL + SHIFT + B', value: 'bold' },
				            { type: 'push', label: 'Italic CTRL + SHIFT + I', value: 'italic' },
				            { type: 'push', label: 'Underline CTRL + SHIFT + U', value: 'underline' },
				            { type: 'separator' },
				            { type: 'color', label: 'Font Color', value: 'forecolor', disabled: true },
				            { type: 'color', label: 'Background Color', value: 'backcolor', disabled: true }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'indentlist', label: 'Lists',
				        buttons: [
				            { type: 'push', label: 'Create an Unordered List', value: 'insertunorderedlist' },
				            { type: 'push', label: 'Create an Ordered List', value: 'insertorderedlist' }
				        ]
				    },
				    { type: 'separator' },
				    { group: 'insertitem', label: 'Insert Item',
				        buttons: [
				            { type: 'push', label: 'HTML Link CTRL + SHIFT + L', value: 'createlink', disabled: true }
				        ]
				    }
				]
			}
		    {/literal}		    
		{rdelim});
		
		{literal}
		myEditor.saveEditorPreferences = function(data) {
            ajaxStatus.hideStatus();                
	    };
		
        myEditor.on('editorContentLoaded', function() {
            resize = new YAHOO.util.Resize(myEditor.get('element_cont').get('element'), {
                handles: ['br'],
                autoRatio: false,
                status: true,
                proxy: true,
                setSize: true
            });
            resize.on('startResize', function() {
                this.hide();
                this.set('disabled', true);
            }, myEditor, true);
            resize.on('resize', function(args) {
                var h = args.height;
                var th = (this.toolbar.get('element').clientHeight + 2); //It has a 1px border..
                var dh = (this.dompath.clientHeight + 1); //It has a 1px top border..
                var newH = (h - th - dh);
                this.set('width', args.width + 'px');
                this.set('height', newH + 'px');
                this.set('disabled', false);
                url = 'index.php?module=UserPreferences&action=save_rich_text_preferences&width=' + args.width + 'px&height=' + newH + 'px';
				ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_SAVING'));
				YAHOO.util.Connect.asyncRequest('GET', url, {success: myEditor.saveEditorPreferences, failure: myEditor.saveEditorPreferences});	
                this.show();
            }, myEditor, true);
        });
		{/literal}
		
		
		myEditor.render();    
    {rdelim},
    allowRollup: true,
    base: "include/javascript/yui/build/"
{rdelim});
{{$idname}}_loader.insert();
</script>
{{/if}}
<!--//END SUGARCRM flav=richtext ONLY -->
