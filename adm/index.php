<?php
/**
* ACP index for viennaCMS.
* 
* @package viennaCMS
* @author viennainfo.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('IN_viennaCMS', true);
include('../start.php');
$user = user::getnew();
$user->checkacpauth();

$display_admin_tree = (empty($_GET['display_admin_tree']) ) ?  1 : 0;
$page_title = __('viennaCMS ACP');
include('./header.php');

include('./footer.php');
?>