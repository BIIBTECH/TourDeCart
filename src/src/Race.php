<?php

namespace biibtech\tdc;

class Race extends TourObject {

	private $laps; // seznam kol

	public function __construct() {
		$this->laps = new \Nette\Utils\ArrayHash;
	}

	/*
	 * vrati vysledky v kolech pro zadanouosobu
	 */

	public function getLapResultsForUser($user) {
		$r = new \Nette\Utils\ArrayHash;

		foreach ($this->laps as $lap) {
			$r->offsetSet($r->count(), $lap->getResultForUser($user));
		}
		return $r;
	}

	/*
	 * vrati poradi lidi dle jejich nejrychlejsiho casu napric koly
	 */

	public function getClassification() {
		$r = array();
		foreach ($this->getUsers() as $user) {
			//dump($user->getId());
			//dump(sec2time($user->getBestResult($this)->getTime()));

			$r[] = $user->getBestResult($this);
		}
		$this->sort($r, array('time'));
		$r = \Nette\Utils\ArrayHash::from($r);
		return $r;
	}

	/*
	 * vrati zaznamy s nejlepsim casem v dane jizde
	 */

	public function getBestResults() {
		$r = new \Nette\Utils\ArrayHash;
		$time = $this->getBestTime();
		foreach ($this->laps as $lap) {
			foreach ($lap->getBestResults() as $record) {
				if ($record->getTime() == $time) {
					$r[] = $record;
				}
			}
		}
		return $r;
	}

	/*
	 * vrati zaznamy s nejhorsim casem v dane jizde
	 */

	public function getWorstResults() {
		$r = array();
		$time = $this->getWorstTime();
		foreach ($this->laps as $lap) {
			foreach ($lap->getWorstResults() as $record) {
				if ($record->getTime() == $time) {
					$r[] = $record;
				}
			}
		}
		return $r;
	}

	/*
	 * vrati nejlepsi cas v dane jizde
	 */

	public function getBestTime() {
		$min = array();
		foreach ($this->laps as $lap) {
			$min[] = $lap->getBestTime();
		}
		return min($min);
	}

	/*
	 * vrati nejhorsi cas v dane jizde
	 */

	public function getWorstTime() {
		$max = array();
		foreach ($this->laps as $lap) {
			$max[] = $lap->getWorstTime();
		}
		return max($max);
	}

	/* vrati seznam lidi, kteri se ucastnili jizdy
	 */

	function getUsers() {
		$users = new \Nette\Utils\ArrayHash;
		foreach ($this->laps as $lap) {
			foreach ($lap->getUsers() as $user) {
				if (!$users->offsetExists($user->getId()))
					$users->offsetSet($user->getId(), $user);
			}
		}
		return $users;
	}

	public function getLaps() {
		return $this->laps;
	}

	function setLaps($laps) {
		$this->laps = $laps;
	}

}