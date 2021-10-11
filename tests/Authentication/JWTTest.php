<?php

namespace Kea\Chacree\Authentication;

use Kea\Chacree\Exception\InvalidToken;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Framework\TestCase;

/**
 * @covers Kea\Chacree\Authentication\JWT
 */
class JWTTest extends TestCase
{
    public function testCreateAndDecryptToken()
    {
        $jwt = $this->buildJWT();

        $token = $jwt->createToken('123456789', 'K3a');

        $unencyptedToken = $jwt->decryptToken($token);
        $this->assertInstanceOf(UnencryptedToken::class, $unencyptedToken);
        $this->assertSame('123456789', $unencyptedToken->claims()->get('userId'));
        $this->assertSame('K3a', $unencyptedToken->claims()->get('username'));
    }

    /**
     * @covers \Kea\Chacree\Exception\InvalidToken;
     */
    public function testDecryptInvalidToken()
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6IkszYSIsInVzZXJJZCI6Ik15RmFudGFzdGljSUQiLCJmaWF0IjoxNTE2MjM5MDIyfQ.e7wQsHE2GVcnrwEkFX1llhFUAVOOn16oRqjJ7QFVHp4';
        $jwt = $this->buildJWT();

        $this->expectException(InvalidToken::class);
        $jwt->decryptToken($token);
    }

    protected function buildJWT(): JWT
    {
        $keyPair = \sodium_crypto_sign_keypair();
        $jwt = new JWT(
            InMemory::plainText(\sodium_crypto_sign_secretkey($keyPair)),
            InMemory::plainText(\sodium_crypto_sign_publickey($keyPair))
        );

        return $jwt;
    }
}
