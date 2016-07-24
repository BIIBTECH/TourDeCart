<?php

namespace biibtech\tdc;

class User extends TourObject {

	public $name;
	public $surname;
	public $birth;
	public $results;

	public function __construct() {
		$this->results = new \Nette\Utils\ArrayHash;
	}

	/*
	 * vrati nejhorsi cas v dane jizde
	 */

	public function getWorstResult($race) {
		$r = null;
		$time = null;

		foreach ($this->getRaceResults($race) as $result) {
			if ($result->getTime() > $time) {
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
			if ($result!=null && $result->getTime() < $time) {
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
	 * vrati vysledky pro konkretni kolo
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

}
