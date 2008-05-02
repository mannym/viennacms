<?php
/**
* Default template functions for viennaCMS.
* 
* @package viennaCMS
* @author viennacms.nl
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

class tpl_basic {
	public function links($location, $links) {
		$return = '';
		
		foreach ($links as $link) {
			$return .= '<li' . (($link['class']) ? ' class="' . $link['class'] . '"': '') . '>' . "\n";
			$return .= "\t" . '<a href="' . $link['href'] . '">';
			$return .= $link['text'];
			$return .= '</a>' . "\n";
			$return .= '</li>' . "\n";
		}
		
		return $return;
	}
	
	public function breadcrumbs($nodes) {
		$page = page::getnew(false);
		$crumbs = '';
		$i = 0;
		$max = count($nodes) - 1;
		
		foreach ($nodes as $node) {
			$class = '';
			switch ($i) {
				case $max:
					$class = ' class="last"';
				break;
				case ($max - 1):
					$class = ' class="onelast"';
				break;
			}
			
			$newcrumb  = ' &laquo; <a href="' .  $page->get_link($node) . '"' . $class . '>';
			$newcrumb .= $node->title;
			$newcrumb .= '</a>';  

			$crumbs = $crumbs . $newcrumb;
			$i++;
		}

		$crumbs = substr($crumbs, 9);
		
		return $crumbs;
	}
}