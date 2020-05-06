<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/my-profile", name="api_my_profile", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function profile(Request $request)
    {
        $user = $this->getUser();

        return $this->json($user);
    }
}
