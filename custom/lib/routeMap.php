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
 * In this file, you can set custom route maps that don't appear in the lib/routeMap.php
 * file. Changes here will not be checked into git. Changes here WILL overwrite
 * the values in lib/routeMap.php.
 *
 * For examples, see lib/routeMap.php and any jobs that set 'routeMap' in their
 * config.
 */
$routeMap = array(
    //'getRelatedRecord'        => array('GET', '$module', '$bean_id', 'link', '$linkName', '$linkedBean_id'),
);
