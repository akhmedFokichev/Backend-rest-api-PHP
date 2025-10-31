<?php
namespace App\Application\Reference\Country;

use App\Domain\Reference\Country\CountryRepositoryInterface;

class CreateCountryUseCase { 
    public function __construct(private CountryRepositoryInterface $r) {} 
    public function execute(string $code, string $name, bool $isCatalog=false, ?string $parentUuid=null, int $sortOrder=0): string { 
        return $this->r->create($code, $name, $isCatalog, $parentUuid, $sortOrder); 
    } 
}

