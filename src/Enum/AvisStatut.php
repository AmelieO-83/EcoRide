<?php
// src/Enum/AvisStatut.php
namespace App\Enum;

enum AvisStatut: string
{
    case EnAttente = 'en_attente';   // l’avis est déposé, en attente de validation
    case Valide    = 'valide';       // l’avis a été validé par un employé
    case Refuse    = 'refuse';       // l’avis a été refusé par un employé
}
