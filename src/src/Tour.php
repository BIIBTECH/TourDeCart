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
				$this->sort($r, array('time'));

				break;
			case Classification::TYPE_TIME_DESC:
				// klasifikace podle nejpomalejsiho casu kola
				foreach ($this->getUsers() as $user) {
					//dump($user->getId());
					//dump(sec2time($user->getBestResult($this)->getTime()));

					$r[] = $user->getWorstResultForTour($this);
				}
				$this->sort($r, array('time'), false);

				break;
			case Classification::TYPE_AVG:
				// klasifikace podle prumerneho casu
				foreach ($this->getUsers() as $user) {
					$i = 0;
					$t = 0;

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
				$this->sort($r, array('time'));

				break;
			case Classification::TYPE_AVG_DESC:
				// klasifikace podle nejpomalejsiho prumerneho casu 
				foreach ($this->getUsers() as $user) {
					$i = 0;
					$t = 0;

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
				$this->sort($r, array('time'), false);

				break;
			case Classification::TYPE_DELTA:
				// klasifikace podle delt
				foreach ($this->getUsers() as $user) {
					$sum = 0;
					foreach ($this->getRaces() as $race) {
						$deltas = $user->getDeltas($race, true); // chceme skrnout nejhorsi deltu, a tak TRUE
						foreach ($deltas as $delta) {
							$sum+=$delta;
						}
					}
					$new_result = new Result();
					$new_result->setUser($user);
					$new_result->setRace($this);
					$new_result->setTime($sum);
					$r[] = $new_result;
				}
				$this->sort($r, array('time'));
				$this->sort($r, array('time'));

				break;
			case Classification::TYPE_MOVEMENTS_LAPS:
				// klasifikace podle zlepseni v casech mezi koly
				//
				$lion = $this->getClassification(Classification::TYPE_AVG_DESC);

				foreach ($this->getUsers() as $user) {
					//dump($user->getId());
					//dump(sec2time($user->getBestResult($this)->getTime()));

					$new_result = new Result();
					$new_result->setUser($user);
					$new_result->setTime($user->getAdvancementsForTour($this));
					$i = 1;
					foreach ($lion as $result) {
						if ($result->getUser()->getId()==$user->getId()) {
							$new_result->setSort($i);
						}
						++$i;
					}
					$r[] = $new_result;
				}
				$this->sort($r, array('time','sort'), false);
				break;
			case Classification::TYPE_MOVEMENTS_LAPS_POSITIONS:
				// klasifikace podle posunu mezi koly
				foreach ($this->getUsers() as $user) {
					//dump($user->getId());
					//dump(sec2time($user->getBestResult($this)->getTime()));

					$new_result = new Result();
					$new_result->setUser($user);
					$new_result->setTime($user->getMovementsForTour($this));
					$r[] = $new_result;
				}
				$this->sort($r, array('time'), false);

				break;
			case Classification::TYPE_POSITION_POINTS:
				// klasifikace podle poctu bodu za umisteni
				foreach ($this->getUsers() as $user) {
					$sum = 0;
					$points = $user->getPositionPointsForTour($this); 
					$new_result = new Result();
					$new_result->setUser($user);
					$new_result->setRace($this);
					$new_result->setTime($points);
					$r[] = $new_result;
					$this->sort($r, array('time'), false);
				}
				break;


		}

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
