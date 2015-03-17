<?php
// plug/avahi/pastecat.avahi.php

addAvahi('pastecat','fpcserver');

function fpcserver($dates){
	global $staticFile;

	return ("<a class='btn' href='".$staticFile."/pastecat/commit" . "?ip=" . $dates['ip'] ."&port=".$dates['port']."'>Go to server</a>  ");
}
