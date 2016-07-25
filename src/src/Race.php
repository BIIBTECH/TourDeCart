<?php

namespace biibtech\tdc;

class Race extends TourObject {

	private $laps; // seznam kol
	private $tour;

	public function __construct($tour) {
		$this->laps = new \Nette\Utils\ArrayHash;
		$this->tour = $tour;
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
	 * vrati poradi lidi napric koly
	 */

	public function getClassification($type = Classification::TYPE_TIME) {
		$r = array();
		switch ($type) {
		case Classification::TYPE_TIME:
			// klasifikace podle nejrychlejsiho casu kola
			foreach ($this->getUsers() as $user) {
				//dump($user->getId());
				//dump(sec2time($user->getBestResult($this)->getTime()));

				$r[] = $user->getBestResult($this);
			}
			$this->sort($r, array('time'));
			break;
		case Classification::TYPE_AVG:
			// klasifikace podle prumerneho casu
			$t = 0;
			$i = 0;
			foreach ($this->getUsers() as $user) {
				$t = 0;
				$i = 0;
				foreach ($this->getLapResultsForUser($user) as $result) {
					if ($result != null) {
						++$i;
						$time = $result->getTime();
						//	echo $user->getId().' + '.$time
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
		case Classification::TYPE_DELTA:
			// klasifikace podle delt
			foreach ($this->getUsers() as $user) {
				$sum = 0;
				$deltas = $user->getDeltas($this, true); // chceme skrnout nejhorsi deltu, a tak TRUE
				foreach ($deltas as $delta) {
					$sum+=$delta;
				}
				$new_result = new Result();
				$new_result->setUser($user);
				$new_result->setRace($this);
				$new_result->setTime($sum);
				$r[] = $new_result;
			}
			$this->sort($r, array('time'));
			break;
		case Classification::TYPE_POSITION_POINTS:
			// klasifikace podle poctu bodu za umisteni
			foreach ($this->getUsers() as $user) {
				$sum = 0;
				$points = $user->getPositionPoints($this); 
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
				$points = $user->getCrashes($this); 
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
			$do = 6;

			foreach($this->getLaps() as $k=>$lap) {
				$_r = $lap->getClassification(); // count = 5
				if ($k>1) {
					--$do;
				} 
				#print "$k => $do<br>";
				for ($i=0;$i<min($do,count($_r));++$i) {
					$result = $_r->offsetGet($i);
					if (!isset($tmp[$result->getUser()->getId()])) $tmp[$result->getUser()->getId()]=0;
					$tmp[$result->getUser()->getId()]+=1;


				}
			}
			foreach ($tmp as $k=>$v) {
				$new_result = new Result();
				$new_result->setUser($this->getTour()->getEngine()->getUsers()->offsetGet($k));
				$new_result->setRace($this);
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

	/*
	 * aktivuje vsechny vysledky v kolech
	 */

	public function enableAllResults() {
		//echo "!!! aktivuju vsechny vysledky pro race:" . $this->id . "<br>";

		foreach ($this->getLaps() as $lap) {
			foreach ($lap->getResults(true) as $result) {
				$result->setValid(true);
			}
		}
	}

	/*
	 * zneaktivni nejhorsi vysledek kazdeho cloveka v jizde
	 */

	public function disableWorstResults() {
		$this->enableAllResults();

		//echo "!!! rusim nejhorsi vysledky pro race:" . $this->id . "<br>";

		foreach ($this->getUsers() as $user) {
			$user->disableWorstResult($this);
		}

//		echo "<b>vypis vypnutych vysledku:</b><br>";
//		foreach ($this->getLaps() as $lap) {
//			foreach ($lap->getResults(true) as $r) {
//				if (!$r->getValid())
//					echo $r->getUser()->getId() . " v kole " . $r->getLap()->getId() . "<br>";
//			}
//		}
	}

	/*
	 * zneaktivni nejlepsi vysledek kazdeho cloveka v jizde
	 */

	public function disableBestResults() {
		$this->enableAllResults();
		//echo "!!! rusim nejlepsi vysledky pro race:" . $this->id . "<br>";

		foreach ($this->getUsers() as $user) {
			$user->disableBestResult($this);
		}

//		echo "<b>vypis vypnutych vysledku:</b><br>";
//		foreach ($this->getLaps() as $lap) {
//			foreach ($lap->getResults(true) as $r) {
//				if (!$r->getValid())
//					echo $r->getUser()->getId() . " v kole " . $r->getLap()->getId() . "<br>";
//			}
//		}
	}

	public function setTour($tour) {
		$this->tour = $tour;
	}

	public function getTour() { 
		return $this->tour;
	}

}
