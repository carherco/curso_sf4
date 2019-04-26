<?php

// src/EventListener/loginListener.pdp
// Change the namespace according to the location of this class in your bundle

namespace App\Listener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LoginListener {

    protected $router;
    protected $dispatcher;
    protected $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, Router $router, EventDispatcherInterface $dispatcher) {
        $this->router = $router;
        $this->dispatcher = $dispatcher;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $this->dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponse'));
    }

    public function onKernelResponse(FilterResponseEvent $event) {
// Important: redirect according to user Role
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $event->setResponse(new RedirectResponse($this->router->generate("admin")));
        } elseif ($this->authorizationChecker->isGranted('ROLE_USER')) {
            $event->setResponse(new RedirectResponse($this->router->generate("user")));
        }
    }

}
