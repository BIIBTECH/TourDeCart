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
			usort($objs, function($a, $b) use ($props, $asc) {
				$fce0 = 'get' . ucfirst($props[0]);
				$fce1 = 'get' . ucfirst($props[1]);

				if ($a->$fce0() == $b->$fce0()) {
					return $a->$fce1() > $b->$fce1() ? 1 : -1;
				} else {
					if ($asc) {
						return $a->$fce0() > $b->$fce0() ? 1 : -1;
					} else {
						return $a->$fce0() < $b->$fce0() ? 1 : -1;
					}
				}
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
