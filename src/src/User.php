<?php

namespace biibtech\tdc;

class User extends TourObject {

	public $name;
	public $surname;
	public $birth;
	public $diff = 5;

	//public $results;
	//
	//
	public function getCrashesForTour($tour) {
		$points = 0;
		$median = $this->getMedianForTour($tour);
		foreach($tour->getRaces() as $race) {
			foreach($this->getRaceResults($race) as $result) {
				if ($result->getTime() > ($median->getTime()+$this->diff) ) {
					$points+=1;
				}
			}
		}
		return $points;
	}


	public function getCrashes($race) {
		$points = 0;
		$median = $this->getMedian($race);
		foreach($this->getRaceResults($race) as $result) {
			if ($result->getTime() > ($median->getTime()+$this->diff) ) {
				$points+=1;
			}
		}
		return $points;
	}

	/*
	 * funkce vrati casovy median
	 */
	public function getMedian($race) {
		$r = $this->getRaceResults($race)->getIterator()->getArrayCopy();
		$this->sort($r, array('time'));
		foreach($r as $_r) {
			#print $this->getId().' = '.$_r->format()."<br>";
		}
		$median = round(count($r)/2)-1;
		return $r[$median];
	}

	/*
	 * funkce vrati casovy median
	 */
	public function getMedianForTour($tour) {
		$r = $this->getRaceResultsForTour($tour)->getIterator()->getArrayCopy();
		$this->sort($r, array('time'));
		foreach($r as $_r) {
			#print $this->getId().' = '.$_r->format()."<br>";
		}
		$median = round(count($r)/2)-1;
		return $r[$median];
	}


	/*
	 * vrati pocet posunu nahoru v ramci jizdy
	 */
	public function getMovementsForTour($tour) {
		$pos = 0;
		foreach ($tour->getRaces() as $race) {
			$pos+=$this->getMovements($race);
		}
		return $pos;
	}

	/*
	 * vrati pocet posunu nahoru v ramci jizdy
	 */

	public function getMovements($race) {
		$pos = 0;
		$v = array();

		foreach ($race->getLaps() as $lap) {
			foreach ($lap->getClassification() as $i=>$result) {
				if ($result->getUser()->getId() == $this->id) {
					$v[]=$i;
				}
			}
		}
		$i = 0;
		$prev = null;
		foreach($v as $p) {
			if ($i>0) {
				if ($p<$prev) {
					$pos+=1;
				}
			}
			++$i;
			$prev = $p;

		}
		return $pos;
	}

	/*
	 * vrati pocet bodu za poizici v jednotlivych kolech  v ramci jizdy
	 */

	public function getPositionPointsForTour($tour) {
		$points = 0;
		foreach($tour->getRaces() as $race) {
			$points+=$this->getPositionPoints($race);
		}
		return $points;
	}



	/*
	 * vrati pocet bodu za poizici v jednotlivych kolech  v ramci jizdy
	 */

	public function getPositionPoints($race) {
		$points = 0;
		foreach($race->getLaps() as $lap) {
			$points+=$this->getPositionPointsForLap($lap);
		}
		return $points;
	}

	/*
	 * vrati pocet bodu za poizici v jednotlivych kolech  v ramci kola
	 */

	public function getPositionPointsForLap($lap) {
		foreach ($lap->getClassification() as $i=>$result) {
			if ($result->getUser()->getId()==$this->id) {
				return (isset(Lap::$points[$i])?Lap::$points[$i]:0);
			}
		}
		return 0;
	}



	/*
	 * vrati pocet zlepseni v case v ramci jizdy
	 */

	public function getAdvancementsForTour($tour) {
		$pos = 0;
		foreach ($tour->getRaces() as $race) {
			$pos+=$this->getAdvancements($race);
		}
		return $pos;
	}

	/*
	 * vrati pocet zlepseni v case v ramci jizdy
	 */

	public function getAdvancements($race) {
		$pos = 0;
		$i = 1;
		$prev = null;
		foreach ($this->getRaceResults($race) as $result) {
			if ($i > 1) {
				if ($result->getTime() < $prev->getTime()) {
					$pos+=1;
				}
			}
			$prev = $result;
			++$i;
		}
		return $pos;
	}

	/*
	 * vrati serazene delty uzivatele v dane jizde
	 * $disableWorst urcuje, zda se ma skrtnout nejhorsi delta
	 */

	public function getDeltas($race, $disableWorst = false) {
		$prev = null;
		$i = 0;
		$deltas = array();
		foreach ($race->getLaps() as $lap) {

			foreach ($lap->getResults() as $result) {
				if ($result->getUser()->getId() == $this->id) {
					if ($i > 0) {
						$delta = abs($result->getTime() - $prev->getTime());
						$deltas[] = $delta;
					}
					$prev = $result;
					++$i;
				}
			}
		}
		if ($deltas != null) {
			sort($deltas);
			if ($disableWorst)
				array_pop($deltas);
		}
		return \Nette\Utils\ArrayHash::from($deltas);
	}

	public function __construct() {
		$this->results = new \Nette\Utils\ArrayHash;
	}

	/*
	 * zneaktivni nejhorsi vysledek cloveka v jizde
	 */

	public function disableWorstResult($race) {
		$result = $this->getWorstResult($race);

		if ($result != null) {
			$result->setValid(false);
		}
	}

	/*
	 * zneaktivni nejlepsi vysledek cloveka v jizde
	 */

	public function disableBestResult($race) {
		// TODO, je treba pouzit nize funkci a vupnout nejlepsi vysledek v kazde jizde

		$result = $this->getBestResults($race);

		if ($result != null) {
			$result->setValid(false);
		}
	}

	/*
	 * vrati nejlepsi vysledky z kazde jizdy
	 */

	public function getBestResults($race) {
		$r = null;
		$time = 9999;

		// TODO je treba vratit pole, pro kazde kolo jeden vysledek

		foreach ($this->getRaceResults($race) as $result) {
			if ($result->getTime() < $time) {
				$r = $result;
				$time = $result->getTime();
			}
		}
		return $r;
	}

	/*
	 * vrati nejhorsi cas v dane jizde
	 */

	public function getWorstResult($race) {
		$r = null;
		$time = 0;

		foreach ($this->getRaceResults($race) as $result) {
			if ($result->getTime() > $time) {
				$r = $result;
				$time = $result->getTime();
			}
		}
		return $r;
	}

	/*
	 * vrati nejhorsi cas v danem zavode
	 */

	public function getWorstResultForTour($tour) {
		$r = null;
		$time = 0;

		foreach ($tour->getRaces() as $race) {
			$result = $this->getWorstResult($race);
			if ($result != null && $result->getTime() > $time) {
				$r = $result;
				$time = $result->getTime();
			}
		}

		return $r;
	}

	/*
	 * vrati nejlepsi cas v danem zavode
	 */

	public function getBestResultForTour($tour) {
		$r = null;
		$time = 9999;

		foreach ($tour->getRaces() as $race) {
			$result = $this->getBestResult($race);
			if ($result != null && $result->getTime() < $time) {
				$r = $result;
				$time = $result->getTime();
			}
		}

		return $r;
	}

	/*
	 * vrati nejlepsi cas v dane jizde
	 */

	public function getBestResult($race) {
		$r = null;
		$time = 9999;
		foreach ($this->getRaceResults($race) as $result) {
			if ($result->getTime() < $time) {
				$r = $result;
				$time = $result->getTime();
			}
		}
		return $r;
	}

	public function getBestTime($race) {
		return $this->getBestResult($race)->getTime();
	}

	/*
	 * vrati nejhorsi cas v dane jizde
	 */

	public function getWorstTime($race) {
		return $this->getWorstResult($race)->getTime();
	}

	/*
	 * vrati vysledky pro konkretni akci
	 */

	public function getTourResult($tour) {
		$r = array();
		foreach ($tour->getRaces() as $race) {
			foreach ($this->getRaceResults($race) as $result) {
				$r[$race->getId()][] = $result;
			}
		}
		return \Nette\Utils\ArrayHash::from($r);
	}

	/*
	 * vrati vysledky pro konkretni tour
	 */

	public function getRaceResultsForTour($tour) {
		$r = new \Nette\Utils\ArrayHash;
		foreach ($tour->getRaces() as $race) {
			foreach ($race->getLaps() as $lap) {
				$result = $this->getLapResult($lap);
				if ($result != null && $result->getTime() > 0) {
					$r[$r->count()] = $result;
				}
			}
		}
		return $r;
	}


	/*
	 * vrati vysledky pro konkretni jizdu
	 */

	public function getRaceResults($race) {
		$r = new \Nette\Utils\ArrayHash;
		foreach ($race->getLaps() as $lap) {
			$result = $this->getLapResult($lap);
			if ($result != null && $result->getTime() > 0) {
				$r[$r->count()] = $result;
			}
		}
		return $r;
	}

	/*
	 * vrati vysledek pro konkretni kolo
	 */

	public function getLapResult($lap) {
		foreach ($lap->getResults() as $r) {
			if ($r->getUser()->getId() == $this->id) {
				return $r;
			}
		}
		return null;
	}

	/*
	 * vrati ofiltrovane vysledky
	 */

	public function getRows($race) {
		$filteredResults = $this->getResults();
	}

	public function getName() {
		return $this->name;
	}

	public function getSurname() {
		return $this->surname;
	}

	public function getBirth() {
		return $this->birth;
	}

	function setName($name) {
		$this->name = $name;
	}

	function setSurname($surname) {
		$this->surname = $surname;
	}

	function setBirth($birth) {
		$this->birth = $birth;
	}

	function setResults($results) {
		$this->results = $results;
	}

	public function getResults() {

		$tmp = array_filter($this->results->getIterator()->getArrayCopy(), function($obj) {
			return $obj->getValid();
		});

		return \Nette\Utils\ArrayHash::from($tmp);
	}

	public function __toString() {
		return $this->id;
	}

	public function toString($bit = 0b000) {
		$s = '';
		if( $bit & (1 << 2) ) { $s.=' '.$this->surname; }
		if( $bit & (1 << 1) ) { $s.=' '.$this->name; }
		if( $bit & (1 << 0) ) { $s.=' '.($this->birth?(date("Y")-$this->birth):''); }
		return $s;
	}

}
