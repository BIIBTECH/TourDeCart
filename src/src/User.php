<?php
namespace biibtech\tdc;

class User  extends TourObject {
	public $name;
	public $surname;
	public $birth;
	public $results;
	public function __construct() {
		$this->results = new \Nette\Utils\ArrayHash;
	}

	/*
	 * vrati nejlepsi cas v dane jizde
	*/
	public function getBestTime($race) {
		return min(array_values($this->getRaceResult($race)->getIterator()->getArrayCopy()));
	}

	/*
	 * vrati nejhorsi cas v dane jizde
	*/
	public function getWorstTime($race) {
		return max(array_values($this->getRaceResult($race)->getIterator()->getArrayCopy()));
	}


	/*
	 * vrati vysledky pro konkretni akci
	*/
	public function getTourResult($tour) {
		$r = new \Nette\Utils\ArrayHash;
		foreach($tour->getRaces() as $race) {
			$r[$race->getId()] = $this->getRaceResult($race);
		}
		return $r;
	}


	/*
	 * vrati vysledky pro konkretni jizdu
	*/
	public function getRaceResult($race) {
		$r = new \Nette\Utils\ArrayHash;
		foreach($race->getLaps() as $lap) {
			$time = $this->getLapResult($lap);
			if ($time>0) $r[$lap->getId()] = $time;
		}
		return $r;
	}

	/*
	 * vrati vysledky pro konkretni kolo
	*/
	public function getLapResult($lap) {
		foreach($lap->getResults() as $r) {
			if ($r->getUser()->getId()==$this->id) {
				return $r->getTime();
			}
		}
		return 0;
	}

	/*
	 * vrati ofiltrovane vysledky
	*/
	public function getRows($race) {
		$filteredResults = $this->getResults();
	}

	public function getResults() { return $this->results; }
	public function getName() { return $this->name; }
	public function getSurname() { return $this->surname; }
	public function getBirth() { return $this->birth; }

}
