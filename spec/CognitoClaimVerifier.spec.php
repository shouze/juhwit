<?php

use TeamGantt\Juhwit\Models\Token;
use TeamGantt\Juhwit\Exceptions\InvalidClaimsException;
use TeamGantt\Juhwit\CognitoClaimVerifier;

describe('CognitoClaimVerifier', function () {
    beforeEach(function () {
        $this->clientId = 'client';
        $this->poolId = 'pool';
        $this->region = 'us-east-2';
        $this->claims = [
            'aud' => $this->clientId,
            'iss' => 'https://cognito-idp.us-east-2.amazonaws.com/pool',
            'token_use' => 'id',
            'email' => 'brian@internet.com',
            'custom:user_id' => 123
        ];
        $this->verifier = new CognitoClaimVerifier($this->clientId, $this->poolId, $this->region);
    });

    describe('->verify()', function () {
        it('should return the given token if claims are correct', function () {
            $token = new Token($this->claims);
            
            $verified = $this->verifier->verify($token);

            expect($verified)->toBeAnInstanceOf(Token::class);
        });

        it('should throw an exception if the aud claim does not match the client id', function () {
            $this->claims['aud'] = 'ham';
            $token = new Token($this->claims);

            $sut = function () use ($token) {
                $this->verifier->verify($token);
            };

            expect($sut)->toThrow(new InvalidClaimsException('Invalid aud claim'));
        });

        it('should throw an exception if the iss claim does not match the pool id', function () {
            $verifier = new CognitoClaimVerifier($this->clientId, 'ham', $this->region);
            $token = new Token($this->claims);

            $sut = function () use ($verifier, $token) {
                $verifier->verify($token);
            };

            expect($sut)->toThrow(new InvalidClaimsException('Invalid iss claim'));
        });

        it('should throw an exception if the iss claim does not match the region', function () {
            $verifier = new CognitoClaimVerifier($this->clientId, $this->poolId, 'ham');
            $token = new Token($this->claims);

            $sut = function () use ($verifier, $token) {
                $verifier->verify($token);
            };

            expect($sut)->toThrow(new InvalidClaimsException('Invalid iss claim'));
        });

        it('should throw an exception if the token_use claim is not "id"', function () {
            $this->claims['token_use'] = 'ham';
            $token = new Token($this->claims);

            $sut = function () use ($token) {
                $this->verifier->verify($token);
            };

            expect($sut)->toThrow(new InvalidClaimsException('Invalid token_use claim'));
        });
    });
});