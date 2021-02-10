<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ApiController extends AbstractController
{

    private $keycloakClientId;

    /**
     * @var Security
     */
    private $security;

    /**
     * ApiController constructor.
     * @param $keycloakClientId
     * @param Security $security
     */
    public function __construct($keycloakClientId, Security $security)
    {
        $this->keycloakClientId = $keycloakClientId;
        $this->security = $security;
    }

    /**
     * @Route("/unauthorized", name="unauth")
     */
    public function test() {
        return new JsonResponse(["test" => 'test'],
            Response::HTTP_OK);
    }

    /**
     * @Route("/profil", name="profile")
     */
    public function profil() {
        // dd($this->security->getUser());
        return new JsonResponse([$this->security->getUser()],
            Response::HTTP_OK);
    }
}
