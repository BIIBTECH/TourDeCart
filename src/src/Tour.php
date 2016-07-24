<?php

namespace biibtech\tdc;

class Tour extends TourObject {

	private $races;
	private $date;
	private $engine;

	function __construct($engine) {
		$this->races = new \Nette\Utils\ArrayHash;
		$this->engine = $engine;
	}

	/*
	 * vrati poradi lidi dle jejich nejrychlejsiho casu napric jizdama
	 */

	public function getClassification() {
		$r = array();
		foreach ($this->getUsers() as $user) {
			//dump($user->getId());
			//dump(sec2time($user->getBestResult($this)->getTime()));

			$r[] = $user->getBestResultForTour($this);
		}
		$this->sort($r, array('time'));
		$r = \Nette\Utils\ArrayHash::from($r);
		return $r;
	}

	/*
	 * vrati nejlepsi cas v dane akci
	 */

	public function getBestTime() {
		$min = array();
		foreach ($this->races as $race) {
			$min[] = $race->getBestTime();
		}
		return min($min);
	}

	/*
	 * vrati nejhorsi cas v dane akci
	 */

	public function getWorstTime() {
		$max = array();
		foreach ($this->races as $race) {
			$max[] = $race->getWorstTime();
		}
		return max($max);
	}

	/* vrati seznam lidi, kteri se ucastnili dany den
	 */

	function getUsers() {
		$users = new \Nette\Utils\ArrayHash;
		foreach ($this->races as $race) {
			foreach ($race->getUsers() as $user) {
				if (!$users->offsetExists($user->getId()))
					$users->offsetSet($user->getId(), $user);
			}
		}
		return $users;
	}

	public function setDate($date) {
		$this->date = $date;
	}

	public function getRaces() {
		return $this->races;
	}

	public function getEngine() {
		return $this->engine;
	}

	function getDate() {
		return $this->date;
	}

	function setRaces($races) {
		$this->races = $races;
	}

	function setEngine($engine) {
		$this->engine = $engine;
	}

}
