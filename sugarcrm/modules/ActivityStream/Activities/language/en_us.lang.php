<?php

$mod_strings = array(
    'TPL_ACTIVITY_CREATE' => 'Added {{str "TPL_ACTIVITY_RECORD" "Activities" object}} {{object.type}}.',
    'TPL_ACTIVITY_POST' => '{{value}}{{str "TPL_ACTIVITY_ON" "Activities" this}}',
    'TPL_ACTIVITY_UPDATE' => 'Updated {{#if updateStr}}{{{updateStr}}} on {{/if}}{{str "TPL_ACTIVITY_RECORD" "Activities" object}}.',
    'TPL_ACTIVITY_UPDATE_FIELD' => '<a rel="tooltip" title="Changed: {{before}} To: {{after}}">{{field_label}}</a>',
    'TPL_ACTIVITY_LINK' => 'Related {{str "TPL_ACTIVITY_RECORD" "Activities" subject}} to {{str "TPL_ACTIVITY_RECORD" "Activities" object}}.',
    'TPL_ACTIVITY_UNLINK' => 'Unrelated {{str "TPL_ACTIVITY_RECORD" "Activities" subject}} to {{str "TPL_ACTIVITY_RECORD" "Activities" object}}.',
    'TPL_ACTIVITY_ATTACH' => 'Added file <a class="dragoff" target="sugar_attach" href="{{{url}}}">{{{filename}}}</a>{{str "TPL_ACTIVITY_ON" "Activities" this}}.',
    'TPL_ACTIVITY_DELETE' => 'Deleted {{str "TPL_ACTIVITY_RECORD" "Activities" object}} {{object.type}}.',
    'TPL_ACTIVITY_UNDELETE' => 'Restored {{str "TPL_ACTIVITY_RECORD" "Activities" object}} {{object.type}}.',
    'TPL_ACTIVITY_RECORD' => '<a href="#{{module}}/{{id}}">{{name}}</a>',
    'TPL_ACTIVITY_ON' => '{{#if object}} on {{{str "TPL_ACTIVITY_RECORD" "Activities" object}}}.{{/if}}{{#if object_type}} on {{object_type}}.{{/if}}',
    'TPL_COMMENT' => '{{value}}',
    'TPL_MORE_COMMENT' => '{{this}} more comment&hellip;',
    'TPL_MORE_COMMENTS' => '{{this}} more comments&hellip;',
);
