<?php
namespace biibtech\tdc;

class Category extends TourObject {
	public $icon;
	public $title;
	public function getIcon() { return $this->icon; }
	public function getTitle() { return $this->title; }
}
