<?php

namespace biibtech\tdc;

class Engine {

	public $users;
	public $categories;
	public $tours;
	private $dir;

	function __construct($dir) {
		$this->dir = $dir;
		$this->users = new \Nette\Utils\ArrayHash;
		$this->tours = new \Nette\Utils\ArrayHash;
		$this->categories = new \Nette\Utils\ArrayHash;

		$this->init();
		$this->loadinis();

		$this->test();
	}

	public function test() {
		// vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v danem zavode
		//zahajsky = 2 5 = 1:29.469
		//rosmuller = 3 4 = 1:30.422
		//strop = 3 1 = 1:31.344
		//fuchs = 2 5 = 1:33.500
		//krasl = 3 4 = 1:37.500
		//kraslova = 3 5 = 1:44.812
		//foreach ($this->tours->offsetGet(1)->getClassification() as $result) {
		//	print sprintf("%s %s race:%s lap:%s<br>", $result->getUser()->getId(), $result->format(), $result->getRace()->getId(), $result->getLap()->getId());
		//}
		// vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v dane jizde
		//zahajsky = 4 = 1:32.610
		//rosmuller = 3 = 1:33.531
		//strop = 4 = 1:34.531
		//fuchs = 5 = 1:39.703
		//foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification() as $result) {
		//	print sprintf("%s %s lap:%s<br>", $result->getUser()->getId(), $result->format(), $result->getLap()->getId());
		//}
		// vysledky daneho cloveka v danem kole  1:33.859 
		//dump(sec2time($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLaps()->offsetGet(1)->getResultForUser($this->users->offsetGet('rosmuller'))->getTime()));
		// vysledky daneho cloveka v dane jizde
		//dump($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLapResultsForUser($this->users->offsetGet('rosmuller')));
		// nejlepsi cas cloveka v jizde 3 = 1:33.531
		//dump($this->users->offsetGet('rosmuller')->getBestTime($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
		// nejhorsi cas cloveka v jizde 5 = 1:38.422
		//dump(sec2time($this->users->offsetGet('rosmuller')->getWorstTime($this->tours->offsetGet(1)->getRaces()->offsetGet(1))));
		// nejlepsi cas v jizde 4 = 1:32.610
		//dump(sec2time($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getBestTime()));
		// nejhorsi cas v jizde 1 = 1:58.156
		// dump(sec2time($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getWorstTime()));
		// vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v danem kole
		//rosmuller = 1:33.859
		//zahajsky = 1:35.219
		//strop = 1:36.250
		//fuchs = 1:58.156
		//foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLaps()->offsetGet(1)->getClassification() as $result) {
		//	print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		//}
		/*
		  // test vraceni vysledku podle kola, jizdy a akce pro daneho cloveka
		  foreach($this->tours as $tour) {
		  $tourresult = $this->users->offsetGet('rosmuller')->getTourResult($tour);
		  $_tourresult = 0;
		  foreach ($tourresult as $k1=>$v1) {
		  foreach ($v1 as $k2=>$v2) {
		  $_tourresult.=$v2->format().',';
		  }
		  }

		  print sprintf("%s (tour: %s)<br>",$tour->getId(), $_tourresult);
		  foreach($tour->getRaces() as $race) {
		  $raceresult = $this->users->offsetGet('rosmuller')->getRaceResults($race);
		  $_raceresult = 0;
		  foreach ($raceresult as $k=>$v) {
		  $_raceresult.=$v->format().',';
		  }
		  print sprintf("%s %s (race: %s)<br>",$tour->getId(), $race->getId(), $_raceresult);
		  foreach($race->getLaps() as $lap) {
		  $lapresult = $this->users->offsetGet('rosmuller')->getLapResult($lap);
		  print sprintf("%s %s %s (lap:%s)<br>",$tour->getId(), $race->getId(), $lap->getId(), ($lapresult!=null?$lapresult->format():''));
		  }
		  }
		  }
		 */
#		dump($this);
	}

	private function init() {
		$users = parse_ini_file($this->dir . DIRECTORY_SEPARATOR . 'users.ini', true);
		foreach ($users as $username => $data) {
			if (!$this->users->offsetExists($username)) {
				$user = new User;
				$user->setId($username);
				$user->name = $data['name'];
				$user->birth = $data['birth'];
				$user->surname = $data['surname'];
				$this->users->offsetSet($username, $user);
			}
		}

		$categories = parse_ini_file($this->dir . DIRECTORY_SEPARATOR . 'cat.ini', true);
		foreach ($categories as $cat => $data) {
			if (!$this->categories->offsetExists($cat)) {
				$category = new Category;
				$category->setId($cat);
				$category->setIcon($data['icon']);
				$category->setTitle($data['title']);
				$this->categories->offsetSet($cat, $category);
			}
		}
	}

	private function loadinis() {
		$files = glob($this->dir . DIRECTORY_SEPARATOR . 'tours' . DIRECTORY_SEPARATOR . '*.ini', GLOB_BRACE);
		foreach ($files as $file) {
			$data = parse_ini_file($file, true);
			$config = $data['config'];
			$tourId = $config['id'];
			$date = new \DateTime($config['date']);
			if (!$this->tours->offsetExists($tourId)) {
				$tour = new Tour($this);
				$tour->setId($tourId);
				$tour->setDate($date);
				$this->tours->offsetSet($tourId, $tour);
			} else
				$tour = $this->tours->offsetGet($tourId);

			$files = glob($this->dir . DIRECTORY_SEPARATOR . "tours" . DIRECTORY_SEPARATOR . "$tourId" . DIRECTORY_SEPARATOR . "race*.ini", GLOB_BRACE);
			foreach ($files as $file) {
				$data = parse_ini_file($file, true);
				$config = $data['config'];
				$raceId = $config['id'];
				unset($data['config']);

				if (!$tour->getRaces()->offsetExists($raceId)) {
					$race = new Race();
					$race->setId($raceId);
					$tour->getRaces()->offsetSet($raceId, $race);
				} else
					$race = $tour->getRaces()->offsetGet($raceId);

				foreach ($data as $username => $laps) {
					if ($this->users->offsetExists($username)) {

						foreach ($laps as $lapId => $time) {
							if (!$race->getLaps()->offsetExists($lapId)) {
								$lap = new Lap();
								$lap->setRace($race);
								$lap->setId($lapId);
								$race->getLaps()->offsetSet($lapId, $lap);
							} else {
								$lap = $race->getLaps()->offsetGet($lapId);
							}
							#print "$raceId / $lapId / $username / $time<br>";
							$r = new Result();
							$r->setRace($race);
							$r->setLap($lap);
							$r->setUser($this->users->offsetGet($username));
							$r->setTime(time2sec($time));
							$lap->addResult($r);
							//$this->users->offsetGet($username)->getResults()->offsetSet($lap->getResults()->count(), $r);
						}
					} else {
						throw new Exception("$username neni registrovan");
					}
				}
			}
		}
	}

}
