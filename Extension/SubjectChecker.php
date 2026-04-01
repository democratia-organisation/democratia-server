<?php

namespace Koyok\democratia\Extension;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\InvalidClaimException;

final class SubjectChecker implements ClaimChecker
{
    /**
     * {@inheritdoc}
     */
    public function checkClaim(mixed $value): void
    {
        if (! is_string($value)) {
            throw new InvalidClaimException('Le sub est absent', 'sub', $value);
        } else {
            // TODO : regarder si le claim sub a un auteur valide dans la base de donnée
            // TODO : vérifier si le clamier correspond à celui qui a fait la requêtre de login lors de la vérification de la clé
        }

    }

    /**
     * {@inheritdoc}
     */
    public function supportedClaim(): string
    {
        return 'sub';
    }
}
