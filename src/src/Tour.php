<?php
namespace biibtech\tdc;

class Tour extends TourObject {
	public $races;
	public $date;

	function __construct() {
		$this->races = new \Nette\Utils\ArrayHash;
	}

	/* vrati seznam lidi, kteri se ucastnili dany den
	 */
	function getUsers() {
		$users = new \Nette\Utils\ArrayHash;
		foreach ($this->races as $race) {
			foreach ($race->getUsers() as $user) {
				if (!$users->offsetExists($user->getId())) $users->offsetSet($user->getId(),$user);
			}
		}
		return $users;
	}

	public function getRaces() { return $this->races; }

}
