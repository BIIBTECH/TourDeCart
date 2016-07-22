<?php
namespace biibtech\tdc;

class Result {
	public $lap;
	public $race;
	public $user;
	public $time;
	public function getUser() { return $this->user; }
	public function getLap() { return $this->lap; }
	public function getRace() { return $this->race; }
	public function getTime() { return $this->time; }
}
