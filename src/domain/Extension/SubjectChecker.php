<?php

namespace Koyok\democratia\domain\Extension;

use Jose\Component\Checker\ClaimChecker;
use Jose\Component\Checker\InvalidClaimException;

final class SubjectChecker implements ClaimChecker
{
    private string $initialMail;

    /**
     * {@inheritdoc}
     */
    public function checkClaim(mixed $value): void
    {
        if (! is_string($value)) {
            throw new InvalidClaimException('Le sub est absent', 'sub', $value);
        } else {
            // TODO : regarder si le claim sub a un auteur valide dans la base de donnée
            if ($this->initialMail != $value) {
                throw new InvalidClaimException("Le demandeur n'est pas celui à l'origine de la requete", 'sub', $value);
            }

        }

    }

    public function __construct(string $initialMail)
    {
        $this->initialMail = $initialMail;
    }

    /**
     * {@inheritdoc}
     */
    public function supportedClaim(): string
    {
        return 'sub';
    }
}
