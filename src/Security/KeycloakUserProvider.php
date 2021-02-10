<?php


namespace App\Security;


use App\Entity\Personne;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class KeycloakUserProvider
{
    private $keycloakClientId;
    private $keycloakClientSecret;
    private $http;


    /**
     * ApiController constructor.
     * @param $keycloakClientId
     * @param $keycloakClientSecret
     * @param HttpClientInterface $http
     */
    public function __construct($keycloakClientId, $keycloakClientSecret, HttpClientInterface $http)
    {
        $this->keycloakClientId = $keycloakClientId;
        $this->keycloakClientSecret = $keycloakClientSecret;
        $this->http = $http;
    }

    public function loadUserFromKeycloak($token) {
        $url = "http://localhost:8080/auth/realms/imt-mines-ales/protocol/openid-connect/userinfo";
        $response = $this->http->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        if($response->getStatusCode() == Response::HTTP_OK) {

        } else {
            // Error zebi
        }


        return $response;

        //return $response->toArray();
        // dd($response->toArray());
    }
}
