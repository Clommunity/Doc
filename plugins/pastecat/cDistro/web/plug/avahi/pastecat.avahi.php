<?php
// plug/avahi/pastecat.avahi.php

addAvahi('pastecat','fpcserver');

function fpcserver($dates){
	global $staticFile;

	return ("<a class='btn' href='http://" .$dates['ip'] .":". $dates['port']."'>Go to server</a>  ");
}
