<?php
// src/Enum/EnergieType.php
namespace App\Enum;

enum EnergieType: string
{
    case Electrique = 'electrique';
    case Essence    = 'essence';
    case Diesel     = 'diesel';
    case Hybride    = 'hybride';
}
