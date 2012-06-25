<?php
$buildFiles = array(
    'sidecar' => array(
        'toMinifyAndConcat' => array(
            'lib/handlebars/handlebars-1.0.0.beta.6.js',
            'lib/sugarapi/sugarapi.js',
            'src/app.js',
            'src/utils/utils.js',
            'src/core/cache.js',
            'src/core/events.js',
            'src/core/error.js',
            'src/view/template.js',
            'src/core/context.js',
            'src/core/controller.js',
            'src/core/router.js',
            'src/core/language.js',
            'src/core/metadata-manager.js',
            'src/core/acl.js',
            'src/core/user.js',
            'src/utils/logger.js',
            'src/data/bean.js',
            'src/data/bean-collection.js',
            'src/data/data-manager.js',
            'src/data/validation.js',
            'src/view/hbt-helpers.js',
            'src/view/view-manager.js',
            'src/view/component.js',
            'src/view/view.js',
            'src/view/field.js',
            'src/view/layout.js',
            'src/view/layouts/list-layout.js',
            'src/view/layouts/fluid-layout.js',
            'src/view/alert.js',
            'lib/sugar/sugar.searchahead.js',
            'lib/sugar/sugar.timeago.js',
        ),
        'toConcat' => array(
            'lib/jquery/jquery.min.js',
            'lib/jquery-ui/js/jquery-ui-1.8.18.custom.min.js',
            'lib/backbone/underscore.js',
            'lib/backbone/backbone.js',
            'lib/stash/stash.js',
            'lib/async/async.js',
            'lib/chosen/chosen.jquery.js',
        )
    ),
    'portal' => array(
        'toMinifyAndConcat' => array(
            'extensions/portal/error.js',
            'extensions/portal/user.js',
            'extensions/portal/views/header-view.js',
            'extensions/portal/views/alert-view.js',
            'extensions/portal/views/footer-view.js',
            'extensions/portal/portal.js',
            'extensions/portal/lib/twitterbootstrap/js/bootstrap-button.js',
            'extensions/portal/lib/twitterbootstrap/js/bootstrap-tooltip.js',
            'extensions/portal/lib/twitterbootstrap/js/bootstrap-popover.js',
            'extensions/portal/lib/twitterbootstrap/js/bootstrap-dropdown.js',
            'extensions/portal/lib/twitterbootstrap/js/bootstrap-modal.js',
            'extensions/portal/lib/twitterbootstrap/js/bootstrap-alert.js',
            'extensions/portal/portal-ui.js',
            'lib/jquery/jquery.iframe.transport.js'
        ),
        'toConcat' => array(

        )
    ),
);
