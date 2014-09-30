<?php

namespace Matheo\UserBundle\Connect;

use Symfony\Component\Security\Core\User\UserInterface;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseProvider;


class UserProvider extends BaseProvider implements AccountConnectorInterface, OAuthAwareUserProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Uid';
        $setter_token = $setter.'Token';
        $setter_data = $setter.'Data';

        $username = $response->getUsername();
        $user = $this->userManager->findUserBy([$this->getProperty($response) => $username]);

        // when the user is new to the system or is registered with another account different to previous registration account (eg, first with facebook then with google)
        if (null === $user || null === $username) {
            // find by email in internal accounts
            $user = $this->userManager->findUserBy(['emailCanonical' => $response->getEmail()]);

            if (null === $user ) { // a completely new account

                if (null == $username) { // Kernel Panic! somehow we have no data to create automatically an account
                    throw new AccountNotLinkedException(sprintf("Usuario no encontrado."));
                }

                // create new user here
                $user = $this->userManager->createUser();
                $user->$setter_id($username); // the social id
                $user->$setter_token($response->getAccessToken());
                $user->$setter_data($response->getResponse());

                $user->setUsername($response->getEmail()); // use the email as username
                $user->setEmail($response->getEmail());
                $user->setPlainPassword($response->getAccessToken().''.time());
                $user->setEnabled(true);
                $this->userManager->updateUser($user);

                // this needs to be done, 'cause the FOSUserBundle has a bug that does not persist the realName but after a second time reloading the entity
                // it's only a one time during registration proccess, so no big deal
                $user->setNombre($response->getRealName());
                $user->setAvatar($response->getProfilePicture());
                $this->userManager->updateUser($user);

                return $user;
            }

            // existing user with the same email address, let's link the accounts
            $user->$setter_id($username);
            $updateUserData = true;
            // the same process as if we've found the same account from the same provider
        }

        if (null === $user->getNombre()) {
            $user->setNombre($response->getRealName());
            $updateUserData = true;
        }

        if (null === $user->getAvatar() || $user->getAvatar() != $response->getProfilePicture()) {
            $user->setAvatar($response->getProfilePicture());
            $updateUserData = true;
        }

        if (isset($updateUserData)) {
            $this->userManager->updateUser($user);
        }

        // update access token and data
        $user->$setter_token($response->getAccessToken());
        $user->$setter_data($response->getResponse());

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Uid';
        $setter_token = $setter.'Token';
        $setter_data = $setter.'Data';

        //if (!method_exists($user, $setter)) {
        //    throw new \RuntimeException(sprintf("Class '%s' should have a method '%s'.", get_class($user), $setter));
        //}

        $username = $response->getUsername();

        if (null !== $previousUser = $this->userManager->findUserBy([$property => $username])) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $user->$setter_data($response->getResponse());

        if (null === $user->getNombre()) {
            $user->setNombre($response->getRealName());
        }

        if (null === $user->getAvatar() || $user->getAvatar() != $response->getProfilePicture()) {
            $user->setAvatar($response->getProfilePicture());
        }

        $this->userManager->updateUser($user);
    }
}
