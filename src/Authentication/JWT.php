<?php

declare(strict_types=1);

namespace Kea\Chacree\Authentication;

use DateTimeImmutable;
use Kea\Chacree\Exception\InvalidToken;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Eddsa;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

class JWT
{
    private Configuration $config;

    public function __construct(Key $private, Key $public)
    {
        $this->config = Configuration::forAsymmetricSigner(new Eddsa(), $private, $public);
        $this->config->setValidationConstraints(
            new SignedWith($this->config->signer(), $this->config->verificationKey()),
            new LooseValidAt(SystemClock::fromSystemTimezone())
        );
    }

    /**
     * @throws InvalidToken
     */
    public function decryptToken(string $encryptedToken): UnencryptedToken
    {
        $token = $this->config->parser()->parse($encryptedToken);

        $this->assertTokenIsValid($token);

        if (!$token instanceof UnencryptedToken) {
            throw new InvalidToken();
        }

        return $token;
    }

    /**
     * @throws InvalidToken
     */
    private function assertTokenIsValid(Token $token): void
    {
        $constraints = $this->config->validationConstraints();

        try {
            $this->config->validator()->assert($token, ...$constraints);
        } catch (RequiredConstraintsViolated) {
            throw new InvalidToken();
        }
    }

    public function createToken(string $userId, string $username): string
    {
        $now = new DateTimeImmutable();

        return $this->config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 year'))
            ->withClaim('userId', $userId)
            ->withClaim('username', $username)
            ->getToken($this->config->signer(), $this->config->signingKey())
            ->toString();
    }
}
