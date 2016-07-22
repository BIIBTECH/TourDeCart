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

		// nejlepsi cas cloveka v jizde
		dump($this->users->offsetGet('rosmuller')->getBestTime($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
		// nejhorsi cas cloveka v jizde
		dump($this->users->offsetGet('rosmuller')->getWorstTime($this->tours->offsetGet(1)->getRaces()->offsetGet(1)));
	
		// nejlepsi cas v jizde
		dump($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getBestTime());
		// nejhorsi cas v jizde
		dump($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getWorstTime());



		/*
		//
		foreach($this->tours->offsetGet(1)->getRaces()->offsetGet(1)->getLaps()->offsetGet(1)->getClassification() as $result) {
			print sprintf("%s %s<br>",$result->getUser()->getId(), $result->getTime());
		}

		// test vraceni vysledku podle kola, jizdy a akce
		foreach($this->tours as $tour) {
			$tourresult = $this->users->offsetGet('rosmuller')->getTourResult($tour);
			$_tourresult = 0;
			foreach ($tourresult as $k1=>$v1) {
				foreach ($v1 as $k2=>$v2) {
					$_tourresult.="$v2,";
				}
			}

			print sprintf("%s (tour: %s)<br>",$tour->getId(), $_tourresult);
			foreach($tour->getRaces() as $race) {
				$raceresult = $this->users->offsetGet('rosmuller')->getRaceResult($race);
				$_raceresult = 0;
				foreach ($raceresult as $k=>$v) {
					$_raceresult.="$v,";
				}
				print sprintf("%s %s (race: %s)<br>",$tour->getId(), $race->getId(), $_raceresult);
				foreach($race->getLaps() as $lap) {
					$lapresult = $this->users->offsetGet('rosmuller')->getLapResult($lap);
					print sprintf("%s %s %s (lap:%s)<br>",$tour->getId(), $race->getId(), $lap->getId(), $lapresult);
				}
			}
		}
		*/
#		dump($this);
	}

	private function init() {
		$users = parse_ini_file($this->dir.'/users.ini',true);
		foreach($users as $username=>$data) {
			if (!$this->users->offsetExists($username)) {
				$user = new User;
				$user->setId($username);
				$user->name = $data['name'];
				$user->birth = $data['birth'];
				$user->surname = $data['surname'];
				$this->users->offsetSet($username, $user);
			}
		}

		$categories = parse_ini_file($this->dir.'/cat.ini',true);
		foreach($categories as $cat=>$data) {
			if (!$this->categories->offsetExists($cat)) {
				$category = new Category;
				$category->setId($cat);
				$category->icon = $data['icon'];
				$category->title = $data['title'];
				$this->categories->offsetSet($cat, $category);
			}
		}
	}

	private function loadinis() {
		$files = glob($this->dir.'/tours/*.ini', GLOB_BRACE);
		foreach($files as $file) {
			$data = parse_ini_file($file,true);
			$config = $data['config'];
			$tourId = $config['id'];
			$date = new \DateTime($config['date']);
			if (!$this->tours->offsetExists($tourId)) {
				$tour = new Tour();
				$tour->setId($tourId);
				$tour->date = $date;
				$this->tours->offsetSet($tourId, $tour);
			} else $tour = $this->tours->offsetGet($tourId);

			$files = glob($this->dir."/tours/$tourId/race*.ini", GLOB_BRACE);
			foreach($files as $file) {
				$data = parse_ini_file($file,true);
				$config = $data['config'];
				$raceId = $config['id'];
				unset($data['config']);

				if (!$tour->getRaces()->offsetExists($raceId)) {
					$race = new Race();
					$race->setId($raceId);
					$tour->getRaces()->offsetSet($raceId, $race);
				} else $race = $tour->getRaces()->offsetGet($raceId);

				foreach($data as $username=>$laps) {
					if ($this->users->offsetExists($username)) {

						foreach($laps as $lapId=>$time) {
							if (!$race->getLaps()->offsetExists($lapId)) {
								$lap = new Lap();
								$lap->race = $race;
								$lap->setId($lapId);
								$race->getLaps()->offsetSet($lapId, $lap);
							} else {
								$lap = $race->getLaps()->offsetGet($lapId);
							}
							#print "$raceId / $lapId / $username / $time<br>";
							$r = new Result();
							$r->race = $race;
							$r->lap = $lap;
							$r->user = $this->users->offsetGet($username);
							$r->time = time2sec($time);
							$lap->getResults()->offsetSet($lap->getResults()->count(), $r);
							$this->users->offsetGet($username)->getResults()->offsetSet($lap->getResults()->count(), $r);
						}
					} else {
						throw new Exception("$username neni registrovan");
					}
				}
			}
		}
	}


}
