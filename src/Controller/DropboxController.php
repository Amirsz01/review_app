<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\Provider\DropboxClient;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\FacebookUser;
use Stevenmaguire\OAuth2\Client\Provider\Dropbox;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;

class DropboxController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/dropbox", name="connect_dropbox_start")
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('dropbox')
            ->redirect([

            ], ['redirect_uri' => $this->generateUrl('connect_dropbox_check', [], UrlGenerator::ABSOLUTE_URL)]);
    }

    /**
     * @Route("/connect/dropbox/check", name="connect_dropbox_check")
     */
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        /** @var DropboxClient $client */
        $client = $clientRegistry->getClient('dropbox');

        try {
            /** @var Dropbox $user */
            $user = $client->fetchUser();

            var_dump($user); die;
        } catch (IdentityProviderException $e) {
            var_dump($e->getMessage()); die;
        }
    }
}
