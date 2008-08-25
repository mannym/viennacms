<?php
/**
* viennaCMS2 loader file
* 
* @package viennaCMS2
* @version $Id$
* @copyright (c) 2008 viennaCMS group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

require('framework/common.php');

$manager = new Manager($global);
$manager->run();