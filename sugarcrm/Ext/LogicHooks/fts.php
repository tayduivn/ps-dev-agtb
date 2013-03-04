<?php

// Full text search.
$hook_array['after_save'][] = array(
    1,
    'fts',
    'include/SugarSearchEngine/SugarSearchEngineQueueManager.php',
    'SugarSearchEngineQueueManager',
    'populateIndexQueue'
);
