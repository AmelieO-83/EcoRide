<?php
// src/Enum/NotificationType.php
namespace App\Enum;

enum NotificationType: string
{
    case NouveauPassager        = 'nouveau_passager';
    case AnnulationPassager     = 'annulation_passager';
    case AnnulationConducteur   = 'annulation_conducteur';
    case AvisADonner            = 'avis_a_donner';
    case NouvelAvis             = 'nouvel_avis';
    case AvisAValider           = 'avis_a_valider';
}
