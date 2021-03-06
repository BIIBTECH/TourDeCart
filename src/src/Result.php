<?php

namespace biibtech\tdc;

class Result {

    const INVALIDATION_LAP = 1;
    const INVALIDATION_RACE = 2;

    private $lap;
    private $race;
    private $user;
    private $time;
    private $valid = true;
	private $sort;

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

    public function getSort() {
        return $this->sort;
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

	function setSort($sort) {
        $this->sort = $sort;
    }

    function format() {
        return sec2time($this->time);
    }

}
