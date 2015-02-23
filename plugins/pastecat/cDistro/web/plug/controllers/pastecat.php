<?php
//pastecat
$title="Pastecat";
$pcpath="/opt/pastecat/";

$pcutils=dirname(__FILE__)."/../resources/pastecat/pcontroller";

function index(){
	global $paspath,$title;
	global $staticFile;

	$page=hlc(t($title));
	$page .= hl(t("Minimalist pastebin engine written in Go"),4);
	$page .= par(t("A simple and self-hosted pastebin service written in Go").' '.t("Can use a variety of storage backends").' '.t(" Designed to optionally remove pastes after a certain period of time.").' '.("If using a persistent storage backend, pastes will be kept between runs.").' '.t("This software runs the").' '."<a href='http://paste.cat'>".t("paste.cat")."</a>". t(" public service."));

	if ( ! isPCInstalled() ) {                                                                                                                                        
		$page .= "<div class='alert alert-error text-center'>".t("Pastecat is not installed")."</div>\n";
		$page .= par(t("Click on the button to install Pastecat"));
		$buttons .= addButton(array('label'=>t("Install Pastecat"),'class'=>'btn btn-success', 'href'=>$staticFile.'/pastecat/install'));
		$page .= $buttons;
	} else {
		$page .= "<div class='alert alert-success text-center'>".t("Pastecat is installed")."</div>\n"; 
	}


	return(array('type' => 'render','page' => $page));
}

function isPCInstalled(){
	global $pcpath;

	return(file_exists($pcpath) && is_dir($pcpath));
}

function install(){
	global $pcutils,$staticFile;

	$ret = execute_program($pcutils." install");
	$output = ptxt(implode("\n",$ret['output']));                                                                                                                     

	setFlash($output);

	return(array('type'=>'redirect','url'=>$staticFile.'/pastecat'));
}
