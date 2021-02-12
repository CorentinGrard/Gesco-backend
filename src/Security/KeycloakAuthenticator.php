<?php

namespace App\Security;

//use App\Entity\Candidate;
//use App\Entity\MyUser;
use App\Entity\Personne;
use App\Repository\PersonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class KeycloakAuthenticator extends AbstractGuardAuthenticator
{

    private $provider;

    private $personneRepository;

    private $entityManager;

    private $logger;

    /**
     * KeycloakAuthenticator constructor.
     * @param KeycloakUserProvider $provider
     * @param PersonneRepository $personneRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(KeycloakUserProvider $provider, PersonneRepository $personneRepository, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->provider = $provider;
        $this->personneRepository = $personneRepository;
        $this->entityManager = $em;
        $this->logger = $logger;
    }

    // Should we call auth process
    public function supports(Request $request)
    {
        // look for header "Authorization: Bearer <token>"
        return $request->headers->has('Authorization')
            && 0 === strpos($request->headers->get('Authorization'), 'Bearer ');
    }

    public function getCredentials(Request $request)
    {
        $authorizationHeader = $request->headers->get('Authorization');
        // skip beyond "Bearer "
        return substr($authorizationHeader, 7);
    }

    // Credentials = token

    /**
     * Ici flow d'authent
     *  => est appelé a chaque requête
     *  => On a récup le bearer token
     *  => Appelle keycloak pour récuperer l'user lié au token
     *  => Cet user est identifié par un 'sub' qui est un uuid
     *  => On cherche un user de notre côté qui a ce sub
     *  => Si trouvé => on le renvoi
     *  => Si pas trouvé alors c'est un user qui ne s'est jamais co => on créer un nouvel user en bdd et on le renvoi
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return Personne|UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $response = $this->provider->loadUserFromKeycloak($credentials);
        $email = $response->toArray()['email'];
        //$prenom = $response->toArray()['prenom'];
        //$nom = $response->toArray()['nom'];


        $personne = $this->personneRepository->findOneBy(['email' => $email]);
        /*if($personne == null) {
            $personne = new Personne();
            $personne->setPrenom($prenom);
            $personne->setNom($nom)->generateEmail();

            $this->entityManager->persist($personne);
            $this->entityManager->flush();
        }*/
        if($personne != null){
            $this->logger->info("ROLES : " . implode(";", $personne->getRoles()));
        }

        return $personne;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        // todo
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse("unauthorized");
    }

    public function supportsRememberMe()
    {
        // todo
    }
}
