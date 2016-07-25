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

		//$this->test();
	}

	public function test() {
		foreach ($this->tours->offsetGet(1)->getClassification(Classification::TYPE_CRASHES) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->getTime());
		}
exit;


		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getUsers() as $user) {
			echo $user->getId()." => ". $this->users->offsetGet($user->getId())->getMedian($this->tours->offsetGet(1)->getRaces()->offsetGet(1))->format()."<br>";
		}

		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification(Classification::TYPE_CRASHES) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->getTime());
		}

		dump($this->users->offsetGet('rosmuller')->getCrashes($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
		dump($this->users->offsetGet('rosmuller')->getCrashes($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
		dump($this->users->offsetGet('rosmuller')->getMedian($this->tours->offsetGet(1)->getRaces()->offsetGet(1))->format());
		dump($this->users->offsetGet('rosmuller')->getMedianForTour($this->tours->offsetGet(1))->format());
		exit;

		echo "<h1>User</h1>";
		echo "<br>nejlepsi cas cloveka (rosmuller) v jizde 3 = 1:33.531<br>";
		dump($this->users->offsetGet('rosmuller')->getBestTime($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
		echo "<br>nejhorsi cas cloveka (rosmuller) v jizde 5 = 1:38.422<br>";
		dump(sec2time($this->users->offsetGet('rosmuller')->getWorstTime($this->tours->offsetGet(1)->getRaces()->offsetGet(1))));
		echo "<br>vysledky daneho cloveka (rosmuller) v danem kole (1) v dane jizde (1) 1:33.859 <br>";
		dump(sec2time($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLaps()->offsetGet(1)->getResultForUser($this->users->offsetGet('rosmuller'))->getTime()));
		echo "<br>vysledky daneho cloveka (rosmuller) v dane jizde (1) <br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLapResultsForUser($this->users->offsetGet('rosmuller')) as $result) {
			print sprintf("%s %s lap:%s race:%s<br>", $result->getUser()->getId(), $result->format(), $result->getLap()->getId(), $result->getRace()->getId());
		}
		echo "rosmuller 16<br>";
		dump($this->users->offsetGet('rosmuller')->getPositionPointsForLap($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLaps()->offsetGet(1)));
		echo "rosmuller 8<br>";
		dump($this->users->offsetGet('rosmuller')->getPositionPointsForLap($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLaps()->offsetGet(2)));
		echo "rosmuller 56<br>";
		dump($this->users->offsetGet('rosmuller')->getPositionPoints($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
		echo "pocet posunu v poradi napric koly<br>";
		dump($this->users->offsetGet('rosmuller')->getMovements($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
		dump($this->users->offsetGet('rosmuller')->getMovements($this->tours->offsetGet(1)->getRaces()->offsetGet(2)));
		dump($this->users->offsetGet('rosmuller')->getMovements($this->tours->offsetGet(1)->getRaces()->offsetGet(3)));
		dump($this->users->offsetGet('rosmuller')->getMovementsForTour($this->tours->offsetGet(1)));



		echo "<h1>Lap</h1>";
		echo "<br>vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v danem kole<br>";
		echo "--rosmuller = 1:33.859<br>";
		echo "--zahajsky = 1:35.219<br>";
		echo "--strop = 1:36.250<br>";
		echo "--fuchs = 1:58.156<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLaps()->offsetGet(1)->getClassification() as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}
		echo "<h1>Race</h1>";
		echo "<br>vypis poradi lidi dle delt v dane jizde<br>";

		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification(Classification::TYPE_DELTA) as $result) {
			print sprintf("%s %s race:%s<br>", $result->getUser()->getId(), $result->format(), $result->getRace()->getId());
		}
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(2)->getClassification(Classification::TYPE_DELTA) as $result) {
			print sprintf("%s %s race:%s<br>", $result->getUser()->getId(), $result->format(), $result->getRace()->getId());
		}
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(3)->getClassification(Classification::TYPE_DELTA) as $result) {
			print sprintf("%s %s race:%s<br>", $result->getUser()->getId(), $result->format(), $result->getRace()->getId());
		}
		echo "<br>vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v dane jizde<br>";
		echo "--zahajsky = 4 = 1:32.610<br>";
		echo "--rosmuller = 3 = 1:33.531<br>";
		echo "--strop = 4 = 1:34.531<br>";
		echo "--fuchs = 5 = 1:39.703<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification() as $result) {
			print sprintf("%s %s lap:%s<br>", $result->getUser()->getId(), $result->format(), $result->getLap()->getId());
		}

		$this->tours->offsetGet(1)->disableBestResults();

		echo "<br>vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v dane jizde po vyhozeni nejlepsich casu<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification() as $result) {
			print sprintf("%s %s lap:%s<br>", $result->getUser()->getId(), $result->format(), $result->getLap()->getId());
		}


		echo "<br>vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v dane jizde po znovuaktivneni zaznamu<br>";
		echo "--zahajsky = 4 = 1:32.610<br>";
		echo "--rosmuller = 3 = 1:33.531<br>";
		echo "--strop = 4 = 1:34.531<br>";
		echo "--fuchs = 5 = 1:39.703<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification() as $result) {
			print sprintf("%s %s lap:%s<br>", $result->getUser()->getId(), $result->format(), $result->getLap()->getId());
		}

		echo "<br>nejlepsi cas v jizde 4 = 1:32.610<br>";
		dump(sec2time($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getBestTime()));
		echo "<br>nejhorsi cas v jizde 1 = 1:58.156<br>";
		dump(sec2time($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getWorstTime()));

		echo "<br>AVG vypis poradi lidi dle prumerneho casu (vysledkova listina) v dane jizde<br>";
		echo "<b>vysledek</b>:<br>";
		echo "--AVG jizda1 zahajsky = 1:33.604<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}
		echo "--AVG jizda2 zahajsky =  1:31.115<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(2)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}
		echo "--AVG jizda3 zahajsky =  1:37.823<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(3)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}

		$this->tours->offsetGet(1)->disableWorstResults();


		echo "<br> vypis poradi lidi dle prumerneho casu (vysledkova listina) v dane jizde<br>";
		echo "--AVG jizda1 zahajsky =  1:33.281<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}
		echo "--AVG jizda2 zahajsky =  1:30.534<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(2)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}
		echo "--AVG jizda3 zahajsky =  1:33.372<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getRaces()->offsetGet(3)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}

		echo "<h1>Tour</h1>";

		foreach ($this->tours->offsetGet(1)->getClassification(Classification::TYPE_POSITION_POINTS) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->getTime());
		}


		echo "rosmuller 137<br>";
		dump($this->users->offsetGet('rosmuller')->getPositionPointsForTour($this->tours->offsetGet(1)));

		echo "<br>vypis poradi lidi dle delt v danem zavode<br>";

		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getClassification(Classification::TYPE_DELTA) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}

		echo "<br>vypis poradi lidi dle nejryhlejsiho casu (vysledkova listina) v danem zavode<br>";
		echo "--zahajsky = 2 5 = 1:29.469<br>";
		echo "--rosmuller = 3 4 = 1:30.422<br>";
		echo "--strop = 3 1 = 1:31.344<br>";
		echo "--fuchs = 2 5 = 1:33.500<br>";
		echo "--krasl = 3 4 = 1:37.500<br>";
		echo "--kraslova = 3 5 = 1:44.812<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getClassification() as $result) {
			print sprintf("%s %s race:%s lap:%s<br>", $result->getUser()->getId(), $result->format(), $result->getRace()->getId(), $result->getLap()->getId());
		}

		echo "<br>AVG vypis poradi lidi dle prumerneho casu (vysledkova listina) v danem zavode<br>";
		echo "--zahajsky = 1:34.180<br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}

		$this->tours->offsetGet(1)->disableWorstResults();


		echo "<br>AVG vypis poradi lidi dle prumerneho casu (vysledkova listina) v danem zavode<br>";
		echo "--zahajsky = 1:32.395  <br>";
		echo "<b>vysledek</b>:<br>";
		foreach ($this->tours->offsetGet(1)->getClassification(Classification::TYPE_AVG) as $result) {
			print sprintf("%s %s<br>", $result->getUser()->getId(), $result->format());
		}


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
		exit;
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

	public function enableAllResults() {
		foreach ($this->getTours() as $tour) {
			foreach ($tour->getRaces() as $race) {
				foreach ($race->getLaps() as $lap) {
					foreach ($lap->getResults(true) as $result) {
						$result->setValid(true);
					}
				}
			}
		}
	}

	function getUsers() {
		return $this->users;
	}

	function getCategories() {
		return $this->categories;
	}

	function getTours() {
		return $this->tours;
	}

	function getDir() {
		return $this->dir;
	}

	function setUsers($users) {
		$this->users = $users;
	}

	function setCategories($categories) {
		$this->categories = $categories;
	}

	function setTours($tours) {
		$this->tours = $tours;
	}

	function setDir($dir) {
		$this->dir = $dir;
	}

	public function disableWorstLapInRace() {
		
	}

	public function disableWorstLapInTour() {
		
	}

	public function disableBestLapInRace() {
		
	}

	public function disableBestLapInTour() {
		
	}

}
