<?php
// src/Service/Frais.php
namespace App\Service;

class Frais
{
    public function __construct(
        private int $fraisPlateforme
    ) {}

    /**
     * Retourne le montant fixe prélevé par la plateforme (en crédits).
     */
    public function getPlateforme(): int
    {
        return $this->fraisPlateforme;
    }
}
