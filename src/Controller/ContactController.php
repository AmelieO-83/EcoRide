<?php
// src/Controller/ContactController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private string          $mailFrom,
        private string          $mailReplyTo,
        private ValidatorInterface $validator
    ) {}

    #[Route('/contact/send', name: 'contact_send', methods: ['POST'])]
    public function send(Request $request): Response
    {
        // on lit les champs
        $data = [
            'name'    => $request->request->get('name', ''),
            'email'   => $request->request->get('email', ''),
            'subject' => $request->request->get('subject', ''),
            'message' => $request->request->get('message', ''),
        ];

        $errors = $this->validator->validate($data, [
            new Assert\Collection([
                'name'    => [new Assert\NotBlank()],
                'email'   => [new Assert\NotBlank(), new Assert\Email()],
                'subject' => [new Assert\NotBlank()],
                'message' => [new Assert\NotBlank(), new Assert\Length(['min'=>10])],
            ])
        ]);

        if (0 === count($errors)) {
            // envoi du mail
            $mail = (new Email())
                ->from($this->mailFrom)
                ->to($this->mailReplyTo)
                ->replyTo($data['email'])
                ->subject('[Contact EcoRide] '.$data['subject'])
                ->text(implode("\n", [
                    "Nom   : {$data['name']}",
                    "Email : {$data['email']}",
                    "Message:",
                    $data['message']
                ]));

            $this->mailer->send($mail);
            $this->addFlash('success', 'Message envoyé !');

            return $this->redirectToRoute('contact');
        }

        // en cas d’erreurs, on réaffiche le form avec erreurs
        return $this->render('contact.html.twig', [
            'data'   => $data,
            'errors' => $errors,
        ]);
    }
}
