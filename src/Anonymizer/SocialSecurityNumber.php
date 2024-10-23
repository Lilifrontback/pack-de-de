<?php
declare(strict_types=1);

namespace DbToolsBundle\PackDeDE\Anonymizer;

use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\AbstractAnonymizer;
use MakinaCorpus\DbToolsBundle\Attribute\AsAnonymizer;
use MakinaCorpus\QueryBuilder\Query\Update;

/**
 * Anonymize german social security numbers(Sozialversicherungsnummer).
 */
#[AsAnonymizer(
    name: 'secu',
    pack: 'de-de',
    description: <<<TXT
    Anonymize with a random fictional german social sécurity numbers. The random letter is the same for all social security numbers in the base for the moment.
    TXT
)]
class SocialSecurityNumber extends AbstractAnonymizer
{
    private $alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    protected function getSample(): array
    {
        $randomIndex = mt_rand(0, count($this->alphabet) - 1);
        return [$this->alphabet[$randomIndex]];
    }

    public function anonymize(Update $update): void
    {
        $expr = $update->expression();
        $randomLetter = $this->getSample()[0];

        $update->set(
            $this->columnName,
            $this->getSetIfNotNullExpression(
                $expr->concat(
                    $expr->lpad($this->getRandomIntExpression(99), 2, '0'),
                    '13',
                    $expr->lpad($this->getRandomIntExpression(999999999), 4 , '0'),
                    $expr->lpad($randomLetter, 1),
                    $expr->lpad($this->getRandomIntExpression(999), 3, '0'),
                )
            )
        );
    }
}
