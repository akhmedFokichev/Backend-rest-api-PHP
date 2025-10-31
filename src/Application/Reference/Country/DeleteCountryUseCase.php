<?php
namespace App\Application\Reference\Country;

use App\Domain\Reference\Country\CountryRepositoryInterface;

class DeleteCountryUseCase { 
    public function __construct(private CountryRepositoryInterface $r) {} 
    public function execute(string $uuid): void { 
        $this->r->delete($uuid); 
    } 
}

