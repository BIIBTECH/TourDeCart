<?php

namespace biibtech\tdc;

class Lap extends TourObject {

	private $results;
	private $race;
	static public $points = array(13,10,8,6,5,4);

	public function __construct() {
		$this->results = new \Nette\Utils\ArrayHash;
	}


	/*
	 *
	 */
	public function xx() {
		$tmp = array();
		$r = $this->getClassification()->getIterator()->getArrayCopy();

		while(count($r)>7) {
			array_pop($r);
		}

		while(count($r)>1) {
			foreach ($r as $_r) {
				if (!isset($tmp[$_r->getUser()->getId()])) $tmp[$_r->getUser()->getId()]=0;
				$tmp[$_r->getUser()->getId()]+=1;
			}
			array_pop($r);
		}
		dump($tmp);
	}


	/*
	 * vrati poradi lidi dle jejich nejrychlejsiho casu
	 */

	public function getClassification() {
		$r = $this->getResults()->getIterator()->getArrayCopy();
		$this->sort($r, array('time'));
		$r = \Nette\Utils\ArrayHash::from($r);
		return $r;
	}

	/*
	 * vrati nejlepsi cas v danem kole
	 */

	public function getBestTime() {
		$min = array();
		foreach ($this->getResults() as $result) {
			$min[] = $result->getTime();
		}
		return min($min);
	}

	/*
	 * vrati nejhorsi cas v danem kole
	 */

	public function getWorstTime() {
		$max = array();
		foreach ($this->getResults() as $result) {
			$max[] = $result->getTime();
		}
		return max($max);
	}

	/*
	 * vrati seznam lidi, kteri se ucastnili kola
	 */

	function getUsers() {
		$users = new \Nette\Utils\ArrayHash;
		foreach ($this->getResults() as $r) {
			if (!$users->offsetExists($r->getUser()->getId()))
				$users->offsetSet($r->getUser()->getId(), $r->getUser());
		}
		return $users;
	}

	/*
	 * vrati vysledek pro cloveka
	 */

	public function getResultForUser($user) {
		foreach ($this->getResults() as $result) {
			if ($result->getUser()->getId() == $user->getId()) {
				return $result;
			}
		}
	}

	public function addResult($r) {
		return $this->results->offsetSet($this->results->count(), $r);
	}

	public function removeResult($k) {
		return $this->results->offsetUnsetSet($k);
	}

	/* 
	 * vrati aktivni vysledky kola
	 * @param all pokud je true, tak vrati aktivni i neaktivni vysledky
	 */
	public function getResults($all = false) {
		if ($all)
			return $this->results;
		$tmp = array_filter($this->results->getIterator()->getArrayCopy(), function($obj) {
			return $obj->getValid();
		});
		return \Nette\Utils\ArrayHash::from($tmp);
	}

	function getRace() {
		return $this->race;
	}

	function setRace($race) {
		$this->race = $race;
	}
	

}
