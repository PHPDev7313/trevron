<?php

namespace JDS\Authentication;

use Firebase\JWT\JWT;
use JDS\Contracts\Authentication\AuthRepositoryInterface;
use JDS\Contracts\Authentication\AuthUserInterface;
use JDS\Contracts\Authentication\SessionAuthInterface;
use JDS\Contracts\Authentication\UserIdentityProviderInterface;
use JDS\Contracts\Session\SessionInterface;

class SessionAuthentication extends AbstractSession implements SessionAuthInterface
{
    private AuthUserInterface $user;

    private string $accessToken;

    private string $refreshToken;


    public function __construct(
        private readonly AuthRepositoryInterface        $authRepository,
        private readonly SessionInterface               $session,
        private readonly UserIdentityProviderInterface  $identityProvider,
        private readonly string                         $jwtSecretKey,
        private readonly string                         $jwtRefreshSecretKey
    ) {}

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

        //
        // Build authorization identiy
        //
        $identity = $this->identityProvider->buildIdentity($user);

        //
        // store identity in session
        //
        $this->session->set('auth_identity', $identity);

        //
        // Generate JWT token
        //
        $issueAt = time();

        $companyPayload = [
            'iss' => $_SERVER['SERVER_NAME'] ?? 'localhost',
            'aud' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'sup' => $identity->userId,
        ];

        $accessPayload = array_merge($companyPayload, [
            'iat' => $issueAt,
            'exp' => $issueAt + (15 * 60),
            'token_type' => 'access',
        ]);

        $this->accessToken = JWT::encode($accessPayload, $this->jwtSecretKey, 'HS256');

        $refreshPayload = array_merge($companyPayload, [
            'iat' => $issueAt,
            'exp' => $issueAt + (60 * 60 * 24 * 14), // 14 days
            'token_type' => 'refresh',
            'email' => $identity->email,
            'company_id' => $identity->companyId,
            'role_ids' => $identity->roleIds,
            'permission_ids' => $identity->permissionIds,
            'access_token' => $this->accessToken,
            'admin' => $identity->isAdmin,
        ]);

        $this->refreshToken = JWT::encode($refreshPayload, $this->jwtRefreshSecretKey, 'HS256');

        //
        // Store the tokens
        //

        $this->session->set($this->session::ACCESS_TOKEN, $this->accessToken);
        $this->session->set($this->session::REFRESH_TOKEN, $this->refreshToken);

        //
        // Keep user for this request
        ///
        $this->user = $user;

        session_regenerate_id(true);
    }

    public function logout(): void
    {
        // Remove all session keys related to the user (auth and tokens)
        $this->session->remove('auth_identity');
        $this->session->remove($this->session::ACCESS_TOKEN);
        $this->session->remove($this->session::REFRESH_TOKEN);

        //
        // Clear the entire session
        //
        $this->session->clear();

        //
        // Reset the cookie
        //
        $this->resetCookie();

        //
        // Destroy the session
        //
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

