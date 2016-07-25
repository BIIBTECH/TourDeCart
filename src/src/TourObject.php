<?php

namespace biibtech\tdc;

class TourObject {

	protected $id;

	function sort(&$objs, $props, $asc = true) {
		if (count($props) == 1) {
			usort($objs, function($a, $b) use ($props, $asc) {
				$fce = 'get' . ucfirst($props[0]);
				if ($asc) {
					return $a->$fce() > $b->$fce() ? 1 : -1;
				} else {
					return $a->$fce() < $b->$fce() ? 1 : -1;
				}
				return 0;
			});
		} else if (count($props) == 2) {
			usort($objs, function($a, $b) use ($props) {
				if ($a->$props[0] == $b->$props[0])
					return $a->$props[1] > $b->$props[1] ? 1 : -1;
				return $a->$props[0] > $b->$props[0] ? 1 : -1;
			});
		}
	}

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

}
