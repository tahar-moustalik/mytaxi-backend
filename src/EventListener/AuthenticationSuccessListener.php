<?php


namespace App\EventListener;

use App\Entity\MobileUser;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof MobileUser) {
            return;
        }

        $data['data'] = array(
            'full_name' => $user->getFullName(),
            'user_type' => $user->getUserType(),
            'email' => $user->getUsername(),
            'mobile_number' => $user->getMobileNumber()
        );


        $event->setData($data);
    }
}