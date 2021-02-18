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
    private $keycloakURL;
    private $http;


    /**
     * ApiController constructor.
     * @param string $keycloakClientId
     * @param string $keycloakClientSecret
     * @param HttpClientInterface $http
     * @param string $keycloakURL
     */
    public function __construct(string $keycloakClientId, string $keycloakClientSecret, HttpClientInterface $http, string $keycloakURL)
    {
        $this->keycloakClientId = $keycloakClientId;
        $this->keycloakClientSecret = $keycloakClientSecret;
        $this->keycloakURL = $keycloakURL;
        $this->http = $http;
    }

    public function loadUserFromKeycloak($token) {
        $response = $this->http->request('POST', $this->keycloakURL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        if($response->getStatusCode() == Response::HTTP_OK) {
            return $response;

        } else {
            // Error zebi
        }


        return $response;

        //return $response->toArray();
        // dd($response->toArray());
    }
}
