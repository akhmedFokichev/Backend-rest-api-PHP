<?php
namespace App\Application\Reference\Country;

use App\Domain\Reference\Country\CountryRepositoryInterface;

class UpdateCountryUseCase { 
    public function __construct(private CountryRepositoryInterface $r) {} 
    public function execute(string $uuid, ?string $code=null, ?string $name=null, ?string $parentUuid=null, ?bool $isCatalog=null, ?int $sortOrder=null): void { 
        $this->r->update($uuid, $code, $name, $parentUuid, $isCatalog, $sortOrder); 
    } 
}

