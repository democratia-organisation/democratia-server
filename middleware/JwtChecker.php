<?php

namespace Koyok\democratia\middleware;

use Exception;
use Jose\Bundle\JoseFramework\DependencyInjection\Source\KeyManagement\JWKSetSource\JWKSet;
use Jose\Component\Checker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature;
use Jose\Component\Signature\JWS;
use Koyok\democratia\domain\Extension;
use Koyok\democratia\domain\utils;

final class JwtChecker
{
    private string $uri;

    private string $client;

    private array $payload;

    private JWK|JWKSet $privateKey;

    private AlgorithmManager $algorithmManager;

    private JWS $jws;

    public array $arrayChecker;

    public function __construct(string $uri, string $client, array $header)
    {
        $this->$uri = $uri;
        $this->$client = $client;
        $this->algorithmManager = new AlgorithmManager([new Signature\Algorithm\ES256]);
        $jwtSerializer = new Signature\Serializer\CompactSerializer;
        $token = str_replace('Bearer ', '', $header['Authorization'] ?? '');
        $this->jws = $jwtSerializer->unserialize($token);
        $this->payload = json_decode($jws->getPayload(), true);

        $keyFile = dirname(__DIR__, 2).'/server/src/data/config/private.key';
        if (file_exists($keyFile)) {
            $this->privateKey = JWKFactory::createFromValues(json_decode(file_get_contents($keyFile), true));
        } else {
            $this->$privateKey = JWKFactory::createECKey('P-256', ['alg' => 'ES256', 'use' => 'sig']);
            file_put_contents($keyFile, json_encode($privateKey->jsonSerialize()));
        }
    }

    public function GenerateKey(string $email): void
    {
        $jwsBuilder = new Signature\JWSBuilder($this->algorithmManager);
        $payloadAcces = json_encode([
            'iss' => $this->uri,
            'aud' => $this->client,
            'sub' => $email,
            'iat' => time(),
            'exp' => time() + 3600,
        ]);
        $payloadRefresh = json_encode([
            'iss' => $this->uri,
            'aud' => $this->client,
            'sub' => $email,
            'iat' => time(),
            'exp' => time() + 3600 * 24 * 7,
        ]);
        $jws = $jwsBuilder
            ->create()
            ->withPayload($payloadAcces)
            ->addSignature($this->privateKey, ['alg' => 'ES256'])
            ->build();
        $jwsRefresh = $jwsBuilder
            ->create()
            ->withPayload($payloadRefresh)
            ->addSignature($this->privateKey, ['alg' => 'ES256'])
            ->build();
        $jwtSerializer = new Signature\Serializer\CompactSerializer;
        $tokenAccess = $jwtSerializer->serialize($jws);
        $tokenRefresh = $jwtSerializer->serialize($jwsRefresh);
        http_response_code(utils\CodeDeRetourApi::OK->value);
        echo json_encode(['data' => ['API_KEY' => $tokenAccess, 'REFRESH' => $tokenRefresh]]);
    }

    public function CheckJWT(): void
    {
        $clock = new Extension\ClockImplementation;
        $arrayChecker = [
            new Checker\ExpirationTimeChecker(clock: $clock),
            new Checker\IssuerChecker([$this->uri]),
            new Checker\AudienceChecker($this->client),
        ];
        $claimChecker = new Checker\ClaimCheckerManager($arrayChecker);
        $jwsVerifier = new Signature\JWSVerifier($this->algorithmManager);
        $headerCheckerManager = new Checker\HeaderCheckerManager([new Checker\AlgorithmChecker(['ES256'])], [new Signature\JWSTokenSupport]);

        try {
            if (! $jwsVerifier->verifyWithKey($this->jws, $this->privateKey, 0)) {
                throw new Exception;
            }
            $claimChecker->check($this->payload);
            $headerCheckerManager->check($this->jws, 0);
        } catch (Checker\InvalidClaimException $th) {
            if ($th->getClaim() == 'exp') {
                throw new Exception('Token expiré', utils\CodeDeRetourApi::Unauthorized->value);
            }
            if ($th->getClaim() == 'sub') {
                throw new Exception('Utilisateur incorérent', utils\CodeDeRetourApi::Unauthorized->value);
                // TODO : lors d'une future phase de développement, renvoyé unauthorized qu'une fois qu'une validation par mail sera faite
                // TODO : générer une empreinte d'appareil unique et si une nouvelle est détecté alors prévenir par mail
            }
            throw new Exception('Token invalide', utils\CodeDeRetourApi::Malicious->value);
        }

    }

    public function GetPayload(): array
    {
        return $this->payload;
    }
}
