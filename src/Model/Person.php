<?php

class Person {
	public $name = "";
	public $login = "";
	public $lavel = 0;
	
	public function toJson() {
		return array(
			'name' => $this->name,
			'age' => $this->age
		);
	}
	
}

