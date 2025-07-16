<?php
// src/Service/NotificationService.php
namespace App\Service;

use App\Enum\NotificationType;
use App\Repository\NotificationRepository;
use App\Entity\Utilisateur;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Twig\Environment;
use LogicException;

/**
 * Service de déclenchement d'envoi de notifications par e-mail
 */
class NotificationService
{
    private string $fromEmail;
    private string $replyTo;

    public function __construct(
        private NotificationRepository  $notificationRepository,
        private MailerInterface         $mailer,
        private Environment             $twig,
        string                          $fromEmail,
        string                          $replyTo
    ) {
        $this->fromEmail = $fromEmail;
        $this->replyTo = $replyTo;
    }

    /**
     * Envoie un e-mail basé sur le modèle de Notification (template) et le contexte fourni.
     *
     * @param NotificationType $type         Type de notification (doit correspondre à un template en base)
     * @param Utilisateur      $destinataire Utilisateur qui recevra l'e-mail
     * @param array            $context      Variables à injecter dans le corps (ex. ['passager'=>..., 'trajet'=>...])
     *
     * @throws LogicException Si aucun modèle trouvé pour le type donné
     */
    public function trigger(
        NotificationType $type,
        Utilisateur $destinataire,
        array $context = []
    ): void {
        // Récupérer le template en base
        $modele = $this->notificationRepository->findOneBy(['type' => $type]);
        if (!$modele) {
            throw new LogicException(sprintf(
                'Aucun modèle de notification trouvé pour le type "%s".',
                $type->value
            ));
        }

        // Générer le corps HTML via Twig
        $html = $this->twig
            ->createTemplate($modele->getContenu())
            ->render(array_merge($context, [
                'destinataire' => $destinataire,
            ]));

        // Préparer et envoyer l'e-mail avec expéditeur
        $email = (new TemplatedEmail())
            ->from($this->fromEmail)
            ->replyTo($this->replyTo)
            ->to($destinataire->getEmail())
            ->subject($modele->getTitre())
            ->html($html);

        $this->mailer->send($email);
    }
}
