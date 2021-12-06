<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\GithubClient;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class GithubController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/github", name="connect_github_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('github')
            ->redirect([

            ], ['redirect_uri' => $this->generateUrl('connect_github_check', [], UrlGenerator::ABSOLUTE_URL)]);
    }

    /**
     * @Route("/connect/github/check", name="connect_github_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        /** @var GithubClient $client */
        $client = $clientRegistry->getClient('github');

        try {
            /** @var GithubResourceOwner $user */
            $user = $client->fetchUser();

            var_dump($user); die;
        } catch (IdentityProviderException $e) {
            var_dump($e->getMessage()); die;
        }
    }
}
