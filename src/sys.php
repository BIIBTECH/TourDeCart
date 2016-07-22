<?php
function time2sec($time) {
	if (preg_match("/^([0-9]+):([0-9]+).([0-9]+)$/",$time,$matches)) {
		$min = $matches[1];
		$sec = $matches[2];
		$mili = $matches[3];
		if (strlen($mili)==1) $mili*=100;
		else if (strlen($mili)==2) $mili*=10;
		$t = $min * 60 + $sec + $mili/1000;
#		print "$time // $t => ".date('Y-m-d H:i:s.'.sprintf("%03d",$mili), $t)."<br/>";
#		$s = new DateTime( date('Y-m-d H:i:s.'.sprintf("%03d",$mili), $t) );
		return $t;
	}
}

function sec2time($cas) {
	return date("i:s.".sprintf("%03d",1000*($cas-floor($cas))),$cas);
}
