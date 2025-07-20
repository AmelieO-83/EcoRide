<?php
// src/EventListener/LoginSuccessListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginSuccessListener
{
    public function __construct(
        private RouterInterface $router,
        private RequestStack   $requestStack,
    ) {}

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (in_array('ROLE_EMPLOYE', $user->getRoles(), true)) {
            $response = new RedirectResponse($this->router->generate('employe'));
            $this->requestStack->getCurrentRequest()->getSession()->set('_security.main.target_path', '/employe');
            $event->getRequest()->attributes->set('_security.main.target_path', '/employe');
        }
    }
}
