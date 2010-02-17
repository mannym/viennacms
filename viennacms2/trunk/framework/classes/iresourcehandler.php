<?php
interface IResourceHandler {
	function get_parents($resource);
	function get_default_rights($resource);
}