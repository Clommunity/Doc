<?php
//pastecat
$title="Pastecat";
$pcpath="/opt/pastecat/";
$pcprogram="pastecat";

$avahi_type="pastecat";

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
		$page .= addButton(array('label'=>t('Go to a Server'),'href'=>$staticFile.'/pastecat/commit'));
		$page .= addButton(array('label'=>t('Create a Pastecat server'),'href'=>$staticFile.'/pastecat/publish'));
	}

	return(array('type' => 'render','page' => $page));
}


function publish_get() {
	global $pcpath,$title;
    global $staticFile;

	$page = hlc(t($title));
    $page .= hlc(t('Publish a pastecat server'),2);
    $page .= par(t("Write the port to publish your Pastecat service"));
    $page .= createForm(array('class'=>'form-horizontal'));
    $page .= addInput('port',t('Port Address'));
    $page .= addInput('description',t('Describe this server'));
    $page .= addSubmit(array('label'=>t('Publish'),'class'=>'btn btn-primary'));
    $page .= addButton(array('label'=>t('Cancel'),'href'=>$staticFile.'/peerstreamer'));

    return(array('type' => 'render','page' => $page));
}

function publish_post() {
    $port = $_POST['port'];
    $description = $_POST['description'];
    $ip = "";

    $page = "<pre>";
    $page .= _pcsource($port,$description);
    $page .= "</pre>";

    return(array('type' => 'render','page' => $page));                                                                                                
}

function commit_get() {
	global $pcpath,$title;
    global $staticFile;

	$page = hlc(t($title));

	$page .= createForm(array('class'=>'form-horizontal'));
	$page .= addInput('ip',t('Server IP'));
	$page .= addInput('port',t('Server port:'));

	//Place textbox here

	$page .= addSubmit(array('label'=>t('Publish'),'class'=>'btn btn-primary'));
    $page .= addButton(array('label'=>t('Cancel'),'href'=>$staticFile.'/pastecat'));	
	return(array('type' => 'render','page' => $page));
}

function commit_post() {
		//Go to pastecat server page (ez?)
		//goto $ip:$port or something similar
	
}

function _pcsource($port,$description) {
	global $pcpath,$pcprogram,$title,$pcutils,$avahi_type;

	$page = "";
    $device = getCommunityDev()['output'][0];
	$ipserver = getCommunityIP()['output'][0];

    if ($description == "") $description = $type;

	$cmd = $pcutils." publish '$port' '$description' '$ipserver'";
	execute_program_detached($cmd);

	$page .= t($ipserver);
	$page .= par(t('Published this server.'));
	$description = str_replace(' ', '', $description);
	$temp = avahi_publish($avahi_type, $description, $port, "");
	$page .= ptxt($temp);

	$page .= addButton(array('label'=>t('Back'),'href'=>$staticFile.'/pastecat'));

    return($page);
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
