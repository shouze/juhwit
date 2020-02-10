<?php

namespace TeamGantt\Juhwit;

use TeamGantt\Juhwit\Contracts\ClaimVerifierInterface;
use TeamGantt\Juhwit\Models\Token;
use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;

class CognitoClaimVerifier implements ClaimVerifierInterface
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $poolId;

    /**
     * @var string
     */
    protected $region;

    /**
     * CognitoClaimVerifier constructor.
     *
     * @param string $clientId
     * @param string $poolId
     * @param string $region
     */
    public function __construct(string $clientId, string $poolId, string $region)
    {
        $this->clientId = $clientId;
        $this->poolId = $poolId;
        $this->region = $region;
    }

    /**
     * {@inheritdoc}
     *
     * @param Token $token
     *
     * @throws InvalidClaimsException
     *
     * @return Token
     */
    public function verify(Token $token): Token
    {
        $aud = $token->getClaim('aud');
        $iss = $token->getClaim('iss');
        $tokenUse = $token->getClaim('token_use');

        if ($aud !== $this->clientId) {
            throw new InvalidClaimsException('Invalid aud claim');
        }

        $poolId = $this->poolId;
        $region = $this->region;

        if ($iss !== "https://cognito-idp.$region.amazonaws.com/$poolId") {
            throw new InvalidClaimsException('Invalid iss claim');
        }

        if ($tokenUse !== 'id') {
            throw new InvalidClaimsException('Invalid token_use claim');
        }

        return $token;
    }
}