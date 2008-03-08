<?php
define('NODES_TABLE', $db->prefix . 'nodes');
define('NODE_REVISIONS_TABLE', $db->prefix . 'node_revisions');
define('NODE_OPTIONS_TABLE', $db->prefix . 'node_options');
define('USER_TABLE', $db->prefix . 'users');
define('UPLOADS_TABLE', $db->prefix . 'uploads');
define('DOWNLOADS_TABLE', $db->prefix . 'downloads');

// node types types
define('NODE_NO_REVISION', 0);
define('NODE_CONTENT', 1);
define('NODE_MODULES', 2);
?>