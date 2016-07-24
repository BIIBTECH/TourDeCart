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
	 * vrati poradi lidi napric jizdama
	 */

	public function getClassification($type = Classification::TYPE_TIME) {
		$r = array();
		switch ($type) {
			case Classification::TYPE_TIME:
				// klasifikace podle nejrychlejsiho casu kola
				foreach ($this->getUsers() as $user) {
					//dump($user->getId());
					//dump(sec2time($user->getBestResult($this)->getTime()));

					$r[] = $user->getBestResultForTour($this);
				}
				break;
			case Classification::TYPE_AVG:
				// klasifikace podle prumerneho casu
				$t = 0;
				$i = 0;
				foreach ($this->getUsers() as $user) {

					foreach ($this->getRaceResultsForUser($user) as $result) {
						if ($result != null) {
							++$i;
							$time = $result->getTime();
							$t+=$time;
						}
					}
					$new_result = new Result();
					$new_result->setUser($user);
					$new_result->setTime($t / $i);
					$r[] = $new_result;
				}
				break;
		}
		$this->sort($r, array('time'));

		$r = \Nette\Utils\ArrayHash::from($r);
		return $r;
	}

	/*
	 * vrati vysledky vsech kol pro zadanouosobu
	 */

	public function getRaceResultsForUser($user) {
		$r = new \Nette\Utils\ArrayHash;

		foreach ($this->races as $race) {
			foreach ($race->getLapResultsForUser($user) as $result) {
				$r->offsetSet($r->count(), $result);
			}
		}
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

	/*
	 * aktivuje vsechny vysledky v zavodech
	 */

	public function enableAllResults() {

		foreach ($this->getRaces() as $race) {
			$race->enableAllResults();
		}
	}

	/*
	 * zneaktivni nejhorsi vysledek kazdeho cloveka v zavodech
	 */

	public function disableWorstResults() {
		foreach ($this->getRaces() as $race) {
			$race->disableWorstResults();
		}
	}

	/*
	 * zneaktivni nejlepsi vysledek kazdeho cloveka v zavodech
	 */

	public function disableBestResults() {

		foreach ($this->getRaces() as $race) {
			$race->disableBestResults();
		}
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
