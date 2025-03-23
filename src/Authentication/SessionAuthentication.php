<?php

namespace JDS\Authentication;

use Firebase\JWT\JWT;
use JDS\Session\Session;
use JDS\Session\SessionInterface;

class SessionAuthentication extends AbstractSession implements SessionAuthInterface
{
    private AuthUserInterface $user;

    private string $accessToken;

    private string $refreshToken;


    public function __construct(
        private AuthRepositoryInterface $authRepository,
        private SessionInterface        $session,
        private readonly string $jwtSecretKey,
        private readonly string $jwtRefreshSecretKey
    )
    {
    }

    public function authenticate(string $email, string $password): bool
    {
        // query db for user using email
        $user = $this->authRepository->findByEmail($email);

        if (!$user) {
            return false;
        }

        // does the hashed user pw match the hash of the attempted password
        if (!password_verify($password, $user->getPassword())) {
            // return false
            return false;
        }
        // if yes, log the user in
        $this->login($user);

        // return true
        return true;
    }

    public function login(AuthUserInterface $user): void
    {

        // start a session
        $this->session->start();
        $issuedAt = time();
        $commonPayload = [
            'iss' => $this->session->get('SERVER_NAME'),
            'aud' => $this->session->get('HTTP_HOST'),
        ];

        $accessPayload = [
            'iat' => $issuedAt,
            'exp' => $issuedAt + (15 + 60),
            'token_type' => 'access'
        ];

        $this->accessToken = JWT::encode($accessPayload, $this->jwtSecretKey, 'HS256');

        $refreshPayload = array_merge($commonPayload, [
            'iat' => $issuedAt,
            'exp' => $issuedAt + (60 * 60 * 24 * 14),
            'token_type' => 'refresh',
            'user_id' => $user->getAuthId(),
            'email' => $user->getEmail(),
            'role_id' => $user->getRoleId(),
            'permission_id' => $user->getPermissionId(),
            'bitwise' => $user->getBitwise()
        ]);
        $this->refreshToken = JWT::encode($refreshPayload, $this->jwtRefreshSecretKey, 'HS256');

        // log the user in
        $this->session->set($this->session::AUTH_KEY, $user->getAuthId());
        $this->session->set($this->session::ACCESS_TOKEN, $this->accessToken);
        $this->session->set($this->session::REFRESH_TOKEN, $this->refreshToken);
        $this->session->set($this->session::AUTH_BITWISE, $user->getBitwise());
        $this->session->set($this->session::AUTH_PERMISSION, $user->getPermissionId());
        $this->session->set($this->session::AUTH_ROLE_WEIGHT, $user->getRoleWeight());
        $this->session->set($this->session::AUTH_ROLE, $user->getRoleId());
        $this->session->set($this->session::AUTH_ADMIN, $user->isAdmin());
        $this->setAdmin(); // determine if this is an admin (true) or not (false)

        // set the user
        $this->user = $user;
        session_regenerate_id(true);
    }

    private function setAdmin(): void
    {
        ($this->session->has($this->session::AUTH_ROLE) && $this->session->has($this->session::AUTH_PERMISSION) && $this->session->has($this->session::AUTH_ROLE_WEIGHT) && $this->session->has($this->session::AUTH_BITWISE)) ? $this->session->setAdmin() : $this->session->set($this->session::AUTH_ADMIN, false);
        ;
    }

    public function logout(): void
    {
        // Remove all session keys related to the user (auth and tokens)
        $this->session->remove($this->session::AUTH_KEY);
        $this->session->remove($this->session::ACCESS_TOKEN);
        $this->session->remove($this->session::REFRESH_TOKEN);
        $this->session->remove($this->session::AUTH_BITWISE);
        $this->session->remove($this->session::AUTH_PERMISSION);
        $this->session->remove($this->session::AUTH_ROLE_WEIGHT);
        $this->session->remove($this->session::AUTH_ROLE);
        $this->session->remove($this->session::AUTH_ADMIN);


        // Optionally clear the entire session
        $this->session->clear();
        $this->resetCookie();
        // Destroy the session
        $this->session->destroy();
        session_regenerate_id(true);
    }

    public function getUser(): AuthUserInterface
    {
        return $this->user;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}

