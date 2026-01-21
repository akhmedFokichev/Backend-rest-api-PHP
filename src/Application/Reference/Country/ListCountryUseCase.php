<?php
namespace App\Application\Reference\Country;

use App\Domain\Reference\Country\CountryRepositoryInterface;

class ListCountryUseCase { 
    public function __construct(private CountryRepositoryInterface $r) {} 
    public function execute(?string $parentUuid=null, ?bool $isCatalog=null): array { 
        return $this->r->list($parentUuid, $isCatalog); 
    } 
}

