<?php

namespace biibtech\tdc;

class Result {

	private $lap;
	private $race;
	private $user;
	private $time;
	private $valid = true;

	public function getUser() {
		return $this->user;
	}

	public function getLap() {
		return $this->lap;
	}

	public function getRace() {
		return $this->race;
	}

	public function getTime() {
		return $this->time;
	}
	
	function setLap($lap) {
		$this->lap = $lap;
	}

	function setRace($race) {
		$this->race = $race;
	}

	function setUser($user) {
		$this->user = $user;
	}

	function setTime($time) {
		$this->time = $time;
	}
	
	function getValid() {
		return $this->valid;
	}

	function setValid($valid) {
		$this->valid = $valid;
	}
	
	function format() {
		return sec2time($this->time);
	}
	





}
