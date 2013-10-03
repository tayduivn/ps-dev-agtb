<?php
/**
 *
 * This is a hack needed because in 6.5 SugarMerge tried to load upgraders from new path
 * but new upgraders are not compatible with old code
 *
 */
class SugarMerge7 extends SugarMerge
{
    function getNewPath() {
        // HACK, see above
        return '';
    }
}


