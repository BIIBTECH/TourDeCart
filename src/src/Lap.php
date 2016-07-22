<?php
namespace biibtech\tdc;

class Lap  extends TourObject {
	public $results;
	public $race;

	public function __construct() {
		$this->results = new \Nette\Utils\ArrayHash;
	}

	/*
	 * vrati nejlepsi cas v danem kole
	*/
	public function getBestTime() {
		$min = array();
		foreach ($this->getResults() as $result) {
			$min[]=$result->getTime();
		}
		return min($min);

	}

	/*
	 * vrati nejhorsi cas v danem kole
	*/
	public function getWorstTime() {
		$max = array();
		foreach ($this->getResults() as $result) {
			$max[]=$result->getTime();
		}
		return max($max);
	}

	/*
	 * vrati seznam lidi, kteri se ucastnili kola
	 */
	function getUsers() {
		$users = new \Nette\Utils\ArrayHash;
		foreach ($this->getResults() as $r) {
			if (!$users->offsetExists($r->getUser()->getId())) $users->offsetSet($r->getUser()->getId(),$r->getUser());
		}
		return $users;
	}

	/*
	 * vrati poradi lidi dle casu jizdy v kole
	 */
	public function getClassification () {
		$tmp = $this->getResults()->getIterator()->getArrayCopy();
		$this->sort($tmp, array('time'));
		$r = \Nette\Utils\ArrayHash::from($tmp);
		return $r;
	}

	private function getUser() { return $this->user; }
	public function getResults() { return $this->results; }

}
