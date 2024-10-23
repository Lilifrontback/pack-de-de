<?php

declare(strict_types=1);

namespace DbToolsBundle\PackDeDE\Anonymizer;

use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\AbstractAnonymizer;
use MakinaCorpus\DbToolsBundle\Attribute\AsAnonymizer;
use MakinaCorpus\QueryBuilder\Query\Update;

/**
 * Anonymize german telephone numbers.
 *
 * This will create phone number with reserved prefixes for fiction and tests:
 *   - 018 XX XX XX XX (mobile)
 *   - 0 137 XXX XXX (landline)
 *
 * Under the hood, it will simple send basic strings such as: "018123456789" with
 * trailing 0's randomly replaced with something else. Formating may be
 * implemented later.
 *
 * Options are:
 *   - "mode": can be "mobile" or "landline"
 */
#[AsAnonymizer(
    name: 'phone',
    pack: 'de-de',
    description: <<<TXT
    Anonymize with a random fictional german phone number.
    You can choose if you want a "landline" or a "mobile" phone number with option 'mode'
    TXT
)]
class PhoneNumberAnonymizer extends AbstractAnonymizer
{
    /**
     * {@inheritdoc}
     */
    public function anonymize(Update $update): void
    {
        $expr = $update->expression();

        $update->set(
            $this->columnName,
            $this->getSetIfNotNullExpression(
                $expr->concat(
                    match ($this->options->get('mode', 'mobile')) {
                        // Mobile numbers start either with 015, 016, 017
                        'mobile' => '018',
                        // 0190/0139: These prefixes were used in the past for special services like information lines or premium-rate numbers, but they have not been assigned since 2003
                        // 0137/0138: These prefixes have never been assigned and are not used.
                        'landline' => '0137',
                        default => throw new \InvalidArgumentException('"mode" option can be "mobile", "landline"'),
                    },
                    $expr->lpad($this->getRandomIntExpression(9999), 6, '0')
                ),
            )
        );
    }
}
