<?php

namespace biibtech\tdc;

class Tour extends TourObject {

	private $races;
	private $date;
	private $engine;
	private $trash;

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
			case Classification::TYPE_CRASHES:
				// klasifikace podle poctu bodu za zpozdena kola
				foreach ($this->getUsers() as $user) {
					$sum = 0;
					$points = $user->getCrashesForTour($this); 
					$new_result = new Result();
					$new_result->setUser($user);
					$new_result->setRace($this);
					$new_result->setTime($points);
					$r[] = $new_result;
					$this->sort($r, array('time'), false);
				}
				break;
			case Classification::TYPE_ELIMINATION:
				$tmp = array();

				foreach($this->getRaces() as $race) {

					$_r = $race->getClassification(Classification::TYPE_ELIMINATION); 
					foreach ($_r as $result) {
						if (!isset($tmp[$result->getUser()->getId()])) $tmp[$result->getUser()->getId()]=0;
						$tmp[$result->getUser()->getId()]+=$result->getTime();
					}
				}
				foreach ($tmp as $k=>$v) {
					$new_result = new Result();
					$new_result->setUser($this->getEngine()->getUsers()->offsetGet($k));
					$new_result->setTime($v);
					$r[] = $new_result;
				}


				$this->sort($r, array('time'), false);
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

	/* 
	 * vrati seznam lidi, kteri se ucastnili dany den
	 * udelat alg X jizd a vyse (napr x=3)
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

	public function getRaces($all = false) {
		return $this->races;
		if ($all)
			return $this->races;
		$tmp = array_filter($this->races->getIterator()->getArrayCopy(), function($obj) {
			return $obj->getValid();
		});
		return \Nette\Utils\ArrayHash::from($tmp);
	}

	/*
	 * zneaktivni cele jizdy
	 */
	public function disableRace($race) {
		$this->trash[$race->getId()]=$race;
		$this->removeRace($race);
		
	}
	/*
	 * zaktivni cele jizdy
	 */
	public function enableRace($race) {
		$this->addRace($this->trash[$race->getId()]);
		unset($this->trash[$race->getId()]);
	}


	public function addRace($race) {
		return $this->races->offsetSet($race->getId(), $race);
	}

	public function removeRace($race) {
		return $this->races->offsetUnset($race->getId());
	}

	public function getEngine() {
		return $this->engine;
	}

	function getTrash() {
		return $this->trash;
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
