<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\MobileUser;
use App\Entity\Passenger;
use App\Entity\Taxi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $mobileUser = new MobileUser();

        $mobileUser->setEmail($request->request->get('email'))
            ->setFullName($request->request->get('full_name'))
            ->setMobileNumber($request->request->get('mobile_number'))
            ->setUserType($request->request->get('user_type'))
            ->setPassword($passwordEncoder->encodePassword(
                $mobileUser,
                $request->request->get('password')
            ))
        ;

        $entityManager->persist($mobileUser);
        $entityManager->flush();
        switch ($mobileUser->getUserType()) {
            case MobileUser::PASSENGER:
                $this->storePassenger($mobileUser);

                break;
            case MobileUser::DRIVER:
                $this->storeDriver($mobileUser, $request);

                break;
            default:
        }

        return new JsonResponse(['status' => 'User Created Successfuly'], Response::HTTP_CREATED);
    }

    public function api()
    {
        return new Response(sprintf('Logged in as %s', $this->getUser()->getUsername()));
    }

    /**
     * @Route("/check_email_unique", methods={"GET"})
     */
    public function checkEmailIsUnique(Request $request): JsonResponse
    {
        $emailToCheck = $request->query->get('email');

        $mobileUser = $this->getDoctrine()->getRepository(MobileUser::class)
            ->findOneByEmail($emailToCheck)
        ;

        if ($mobileUser) {
            return new JsonResponse(['email_unique' => false], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['email_unique' => true], Response::HTTP_FOUND);
    }

    /**
     * @Route("/check_mobile_number_unique", methods={"GET"})
     */
    public function checkMobileNumberIsUnique(Request $request): JsonResponse
    {
        $mobileNumberToCheck = $request->query->get('mobile_number');

        $mobileUser = $this->getDoctrine()->getRepository(MobileUser::class)
            ->findOneByMobileNumber($mobileNumberToCheck)
        ;

        if ($mobileUser) {
            return new JsonResponse(['mobile_number_unique' => false], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['mobile_number_unique' => true], Response::HTTP_FOUND);
    }

    private function storePassenger($mobileUser)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $passenger = new Passenger();
        $passenger->setUser($mobileUser);
        $entityManager->persist($passenger);
        $entityManager->flush();
    }

    private function storeDriver($mobileUser, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $driver = new Driver();
        $taxi = new Taxi();
        $taxi->setImage($request->request->get('image'))
            ->setModelName($request->request->get('model_name'))
            ->setType($request->request->get('type'))
            ->setYear($request->request->get('year'))
        ;

        $driver->setUser($mobileUser)
            ->setCin($request->request->get('cin'))
            ->setTaxi($taxi)
        ;
        $taxi->setDriver($driver);
        $entityManager->persist($taxi);
        $entityManager->persist($driver);
        $entityManager->flush();
    }
}
