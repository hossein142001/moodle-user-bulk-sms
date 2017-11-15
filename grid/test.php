<?php
	
	include_once("./includes/class.xtemplate.php");
	$xtpl=new XTemplate("test.xtpl");
	
	$xtpl->assign("HTML", "Test Program");
	$xtpl->parse("main.headerHTML");
	$xtpl->assign("HTML", "Test Program 2");
	$xtpl->parse("main.headerHTML");
	
	$xtpl->parse("main");
	
	echo $xtpl->out("main");
?>
