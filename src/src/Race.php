<?php
namespace biibtech\tdc;

class Race  extends TourObject {
	public $laps; // seznam kol

	public function __construct() {
		$this->laps = new \Nette\Utils\ArrayHash;
	}

	/*
	 * vrati poradi lidi dle jejich nejrychlejsiho casu napric koly
	 */
	public function getClassification () {
	}

	/*
	 * vrati nejlepsi cas v dane jizde
	*/
	public function getBestTime() {
		$min = array();
		foreach($this->laps as $lap) {
			$min[]=$lap->getBestTime();
		}
		return min($min);
	}

	/*
	 * vrati nejhorsi cas v dane jizde
	*/
	public function getWorstTime() {
		$max = array();
		foreach($this->laps as $lap) {
			$max[]=$lap->getWorstTime();
		}
		return max($max);
	}

	/* vrati seznam lidi, kteri se ucastnili jizdy
	 */
	function getUsers() {
		$users = new \Nette\Utils\ArrayHash;
		foreach ($this->laps as $lap) {
			foreach ($lap->getUsers() as $user) {
				if (!$users->offsetExists($user->getId())) $users->offsetSet($user->getId(),$user);
			}
		}
		return $users;
	}

	public function getLaps() { return $this->laps; }
}
