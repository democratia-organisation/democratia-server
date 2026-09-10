<?php

namespace Koyok\democratia\middleware;

use Exception;
use Jose\Bundle\JoseFramework\DependencyInjection\Source\KeyManagement\JWKSetSource\JWKSet;
use Jose\Component\Checker\{AlgorithmChecker, AudienceChecker, ClaimCheckerManager, ExpirationTimeChecker, HeaderCheckerManager, InvalidClaimException, IssuerChecker};
use Jose\Component\Core\{AlgorithmManager, JWK};
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\{ES256, None};
use Jose\Component\Signature\{JWS, JWSBuilder, JWSTokenSupport, JWSVerifier};
use Jose\Component\Signature\Serializer\CompactSerializer;
use Koyok\democratia\domain\Extension\ClockImplementation;
use Koyok\democratia\lib\CodeDeRetourApi;

final class JwtChecker
{
    private array $payload;

    private JWK|JWKSet $privateKey;

    private AlgorithmManager $algorithmManager;

    private JWS $jws;

    private ClockImplementation $clock;

    private static int $REFRESH_TIME = 3600;

    private static int $KEY_TIME = 3600 * 24 * 7;

    public array $arrayChecker;

    private CompactSerializer $jwtSerializer;

    public function __construct(private string $uri, private string $client)
    {
        $this->algorithmManager = new AlgorithmManager([new ES256]);
        $this->clock = new ClockImplementation;
        $this->jwtSerializer = new CompactSerializer;
        $this->arrayChecker = [
            new ExpirationTimeChecker(clock: $this->clock),
            new IssuerChecker([$this->uri]),
            new AudienceChecker($this->client),
        ];
        $jwsBuilder = new JWSBuilder(new AlgorithmManager([new None]));
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
            $this->privateKey = JWKFactory::createECKey('P-256', ['alg' => 'ES256', 'use' => 'sig', 'kid' => 'key-2026-v2']);
            file_put_contents($keyFile, json_encode($this->privateKey->jsonSerialize()));
        }
    }

    public function GenerateKey(string $email): array
    {

        $now = $this->clock->now()->getTimestamp();
        $jwsBuilder = new JWSBuilder($this->algorithmManager);
        $payloadAcces = json_encode([
            'iss' => $this->uri,
            'aud' => $this->client,
            'sub' => $email,
            'iat' => $now,
            'exp' => $now + JwtChecker::$KEY_TIME,
            'kid' => 'key-2026-v2',
        ]);
        $payloadRefresh = json_encode([
            'iss' => $this->uri,
            'aud' => $this->client,
            'sub' => $email,
            'iat' => $now,
            'exp' => $now + JwtChecker::$REFRESH_TIME,
            'kid' => 'key-2026-v2',
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

        return ['data' => ['API_KEY' => $tokenAccess, 'REFRESH' => $tokenRefresh], 'code' => CodeDeRetourApi::OK->value];
    }

    /**
     * Fonction qui vérifie si toute la clé est valide
     *
     * @throws InvalidClaimException|Exception Si l'erreur concerne sub ou exp, une erreur métier est jetté
     */
    public function CheckJWT(array $header): void
    {
        $this->SetJWS($header);
        $claimChecker = new ClaimCheckerManager($this->arrayChecker);
        $jwsVerifier = new JWSVerifier($this->algorithmManager);
        $headerCheckerManager = new HeaderCheckerManager([new AlgorithmChecker(['ES256'])], [new JWSTokenSupport]);
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
