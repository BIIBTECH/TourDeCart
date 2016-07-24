<?php

namespace biibtech\tdc;

class User extends TourObject {

    public $name;
    public $surname;
    public $birth;

    //public $results;

    /*
     * vrati serazene delty uzivatele v dane jizde
     */
    public function getDeltas($race) {
        $prev = null;
        $i = 0;
        $delta = array();
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
        sort($deltas);
        array_pop($deltas);
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

}
