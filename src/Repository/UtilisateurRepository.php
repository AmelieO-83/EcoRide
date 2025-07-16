<?php
// src/Repository/UtilisateurRepository.php
namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\{
    PasswordAuthenticatedUserInterface,
    PasswordUpgraderInterface
};
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UtilisateurRepository
    extends ServiceEntityRepository
    implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $r)
    {
        parent::__construct($r, Utilisateur::class);
    }

    /**
     * Réhash automatiquement le mot de passe si besoin.
     */
    public function upgradePassword(
        PasswordAuthenticatedUserInterface $user,
        string $newHashedPassword
    ): void {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(
                sprintf('Expected Utilisateur, got %s.', get_class($user))
            );
        }
        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * Recherche un utilisateur par token API.
     */
    public function findOneByApiToken(string $token): ?Utilisateur
    {
        return $this->findOneBy(['apiToken' => $token]);
    }

    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->getQuery()
            ->getResult();
    }
}
