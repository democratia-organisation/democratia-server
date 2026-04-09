<?php

namespace Koyok\democratia\middleware;

use Exception;
use Jose\Bundle\JoseFramework\DependencyInjection\Source\KeyManagement\JWKSetSource\JWKSet;
use Jose\Component\Checker;
use Jose\Component\Checker\InvalidClaimException;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature;
use Jose\Component\Signature\JWS;
use Koyok\democratia\domain\Extension;
use Koyok\democratia\domain\utils\CodeDeRetourApi;

final class JwtChecker
{
    private string $uri;

    private string $client;

    private array $payload;

    private JWK|JWKSet $privateKey;

    private AlgorithmManager $algorithmManager;

    private JWS $jws;

    private Extension\ClockImplementation $clock;

    private static int $REFRESH_TIME = 3600;

    private static int $KEY_TIME = 3600 * 24 * 7;

    public array $arrayChecker;

    private Signature\Serializer\CompactSerializer $jwtSerializer;

    public function __construct(string $uri, string $client)
    {
        $this->uri = $uri;
        $this->client = $client;
        $this->clock = new Extension\ClockImplementation;
        $this->algorithmManager = new AlgorithmManager([new Signature\Algorithm\ES256]);
        $this->arrayChecker = [
            new Checker\ExpirationTimeChecker(clock: $this->clock),
            new Checker\IssuerChecker([$this->uri]),
            new Checker\AudienceChecker($this->client),
        ];
        $this->jwtSerializer = new Signature\Serializer\CompactSerializer;
        $algorithmManager = new AlgorithmManager([
            new Signature\Algorithm\None,
        ]);

        $jwsBuilder = new Signature\JWSBuilder($algorithmManager);
        $jwk = new JWK([
            'kty' => 'none',
        ]);

        $payload = json_encode([
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => 'Mon Application',
        ]);

        $this->jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($jwk, ['alg' => 'none'])
            ->build();
        $keyFile = dirname(__DIR__, 1).'/src/data/config/private.key';
        if (file_exists($keyFile)) {
            $this->privateKey = JWKFactory::createFromValues(json_decode(file_get_contents($keyFile), true));
        } else {
            $this->privateKey = JWKFactory::createECKey('P-256', ['alg' => 'ES256', 'use' => 'sig']);
            file_put_contents($keyFile, json_encode($this->privateKey->jsonSerialize()));
        }
    }

    public function GenerateKey(string $email): array
    {

        $now = $this->clock->now()->getTimestamp();
        $jwsBuilder = new Signature\JWSBuilder($this->algorithmManager);
        $payloadAcces = json_encode([
            'iss' => $this->uri,
            'aud' => $this->client,
            'sub' => $email,
            'iat' => $now,
            'exp' => $now + $this->KEY_TIME,
        ]);
        $payloadRefresh = json_encode([
            'iss' => $this->uri,
            'aud' => $this->client,
            'sub' => $email,
            'iat' => $now,
            'exp' => $now + $this->REFRESH_TIME,
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
        $tokenAccess = $this->jwtSerializer->serialize($jws);
        $tokenRefresh = $this->jwtSerializer->serialize($jwsRefresh);
        http_response_code(CodeDeRetourApi::OK->value);

        return ['data' => ['API_KEY' => $tokenAccess, 'REFRESH' => $tokenRefresh]];
    }

    /**
     * Fonction qui vérifie si toute la clé est valide
     *
     * @throws InvalidClaimException|Exception Si l'erreur concerne sub ou exp, une erreur métier est jetté
     */
    public function CheckJWT(array $header): void
    {
        $this->SetJWS($header);
        $claimChecker = new Checker\ClaimCheckerManager($this->arrayChecker);
        $jwsVerifier = new Signature\JWSVerifier($this->algorithmManager);
        $headerCheckerManager = new Checker\HeaderCheckerManager([new Checker\AlgorithmChecker(['ES256'])], [new Signature\JWSTokenSupport]);
        $this->payload = json_decode($this->jws->getPayload(), true);
        try {
            if (! $jwsVerifier->verifyWithKey($this->jws, $this->privateKey, 0)) {
                throw new Exception("La clé n'est pas la bonne", CodeDeRetourApi::Malicious->value);
            }
            $claimVerifier = $claimChecker->check($this->payload);
            if (\count($claimVerifier) != \count($this->arrayChecker)) {
                throw new Exception("Toutes les conditions n'ont pas été vérifié", CodeDeRetourApi::InternalServerError->value);
            }
            $headerCheckerManager->check($this->jws, 0);
        } catch (InvalidClaimException $th) {
            if ($th->getClaim() == 'exp') {
                throw new Exception('Token expiré', CodeDeRetourApi::Unauthorized->value);
            }
            if ($th->getClaim() == 'sub') {
                throw new Exception('Utilisateur incorérent', CodeDeRetourApi::Unauthorized->value);
                // TODO : lors d'une future phase de développement, renvoyé unauthorized qu'une fois qu'une validation par mail sera faite
                // TODO : générer une empreinte d'appareil unique et si une nouvelle est détecté alors prévenir par mail
            }
            throw $th;
        }

    }

    private function SetJWS(array $header): void
    {
        $token = str_replace('Bearer ', '', $header['Authorization']);
        $this->jws = $this->jwtSerializer->unserialize($token);
    }

    public function GetPayload(): array
    {
        $this->payload = json_decode($this->jws->getPayload(), true);

        return $this->payload;
    }
}
