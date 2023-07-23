<?php

class Profile {
	public $name = "";
	public $age = 33;
	
	public function toJson() {
		return array(
			'name' => $this->name,
			'age' => $this->age
		);
	}
}
