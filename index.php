<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require_once("cushion.php");
	
	$cushion 					= new Cushion();
	$cushion->conf["server"] 	= "twentysixmedias.iriscouch.com";
	$cushion->conf["port"] 		= "6984";
	$cushion->db_create("test");
	$cushion->db_list();
	
?>