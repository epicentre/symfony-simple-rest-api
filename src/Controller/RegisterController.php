<?php

namespace App\Controller;

use App\Entity\User;
use App\Exceptions\ValidationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    /**
     * @Route("/api/auth/register", name="api_auth_register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param JWTTokenManagerInterface $jwt_manager
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        JWTTokenManagerInterface $jwt_manager,
        ValidatorInterface $validator)
    {

        $em = $this->getDoctrine()->getManager();

        $name = $request->get('name');
        $email = $request->get('email');
        $password = $request->get('password');

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setCreatedAt(new \DateTime('now'));
        $user->setUpdatedAt(new \DateTime('now'));

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errors_str = (string) $errors;

            throw new ValidationException($errors_str);
        }

        $em->persist($user);
        $em->flush();

        $token = $jwt_manager->create($user);

        return $this->json(['user' => $user, 'token' => $token]);
    }
}
