<?php
/**
 * The routeMap variable 'maps' a route name to an array of variables and/or strings
 * that comprise that route. The first element in the array is always the HTTP verb
 * for the route, and the subsequent elements in the array are the various pieces of 
 * the path of the route.
 *
 * Creating a routeMap for a route allows you to use variable substitution during
 * route construction. Any element in a route preceeded by a '$' will take its value
 * from the main config variable of the job. So '$module' is replaced by 
 * $this->config['module'] in your job. Note that you need to set these variables
 * before the job's run() method is called.
 *
 * Do not make changes to this file unless you want them checked into the main repo.
 *
 * If you need a custom route, or a route that isn't supported here, add it to
 * custom/lib/routeMap.php. That file's contents overwrite this one's.
 */
$routeMap = array(
    'list'                    => array('GET', '$module'),
    'listFilter'              => array('GET', '$module', 'filter'),
    'createRecord'            => array('POST', '$module'),
    'deleteRecord'            => array('DELETE', '$module', '$bean_id'),
    'retrieveRecord'          => array('GET', '$module', '$bean_id'),
    'updateRecord'            => array('PUT', '$module', '$bean_id'),
    'viewChangeLog'           => array('GET', '$module', '$bean_id', 'audit'),
    'unsetFavorite'           => array('DELETE', '$module', '$bean_id', 'favorite'),
    'setFavorite'             => array('PUT', '$module', '$bean_id', 'favorite'),
    'setFavoriteByCustomId'   => array('PUT', '$module', '$bean_id', 'favorite', 'id_field', '$id_field'),
    'unsetFavoriteByCustomId' => array('DELETE', '$module', '$bean_id', 'favorite', 'id_field', '$id_field'),
    'getFileList'             => array('GET', '$module', '$bean_id', 'file'),
    'updateRecordByCustomId'  => array('PUT', '$module', '$bean_id', 'id_field', '$id_field'),
    'deleteRecordByCustomId'  => array('DELETE', '$module', '$bean_id', 'id_field', '$id_field'),
    'retrieveRecordByCustomId'=> array('PUT', '$module', '$bean_id', 'id_field', '$id_field'),
    'createRelatedLinks'      => array('POST', '$module', '$bean_id', 'link'),
    'createRelatedRecord'     => array('POST', '$module', '$bean_id', 'link', '$linkName'),
    'filterRelated'           => array('GET', '$module', '$bean_id', 'link', '$linkName'),
    'getRelatedRecord'        => array('GET', '$module', '$bean_id', 'link', '$linkName', '$linkedBean_id'),
    'localeOptions'           => array('GET', 'locale'),
    'retrieveCurrentUser'     => array('GET', 'me'),
    'updateCurrentUser'       => array('PUT', 'me'),
    'getMyFollowedRecords'    => array('GET', 'me', 'following'),
    'updatePassword'          => array('PUT', 'me', 'password'),
    'verifyPassword'          => array('POST', 'me', 'password'),
    'userPreferenceUpdate'    => array('PUT', 'me', 'preference', '$prefName'),
    'userPreferenceDelete'    => array('DELETE', 'me', 'preference', '$prefName'),
    'userPreferenceSave'      => array('POST', 'me', 'preference', '$prefName'),
    'userPreference'          => array('GET', 'me', 'preference', '$prefName'),
    'userPreferences'         => array('GET', 'me', 'preferences'),
    'search'                  => array('GET', 'search'),
    'duplicateCheck'          => array('POST', '$module', 'duplicateCheck'),
    'allMetadata'             => array('GET', 'metadata'),
    'filterMetadata'          => array('POST', 'metadata'),
    'listDashboards'          => array('GET', 'Dashboards', '$module'),
    'getEnumValues'           => array('GET', '$module', 'enum', '$field'),
);