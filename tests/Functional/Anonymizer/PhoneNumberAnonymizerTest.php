<?php

declare(strict_types=1);

namespace DbToolsBundle\PackDeDE\Tests\Functional\Anonymizer;

use MakinaCorpus\DbToolsBundle\Anonymization\Config\AnonymizationConfig;
use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizator;
use MakinaCorpus\DbToolsBundle\Anonymization\Config\AnonymizerConfig;
use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\AnonymizerRegistry;
use MakinaCorpus\DbToolsBundle\Anonymization\Anonymizer\Options;
use MakinaCorpus\DbToolsBundle\Test\FunctionalTestCase;

class PhoneNumberAnonymizerTest extends FunctionalTestCase
{
    /** @before */
    protected function createTestData(): void
    {
        $this->createOrReplaceTable(
            'table_test',
            [
                'id' => 'integer',
                'data' => 'string',
            ],
            [
                [
                    'id' => '1',
                    'data' => '0234567834',
                ],
                [
                    'id' => '2',
                    'data' => '0334567234',
                ],
                [
                    'id' => '3',
                    'data' => '0534567234',
                ],
                [
                    'id' => '4',
                ],
            ],
        );
    }

    public function testAnonymize(): void
    {
        $config = new AnonymizationConfig();
        $config->add(new AnonymizerConfig(
            'table_test',
            'data',
            'de-de.phone',
            new Options()
        ));

        $anonymizator = new Anonymizator(
            $this->getDatabaseSession(),
            new AnonymizerRegistry(),
            $config
        );

        $this->assertSame(
            "0234567834",
            $this->getDatabaseSession()->executeQuery('select data from table_test where id = 1')->fetchOne(),
        );

        $anonymizator->anonymize();

        $datas = $this->getDatabaseSession()->executeQuery('select data from table_test order by id asc')->fetchFirstColumn();
        $this->assertNotNull($datas[0]);
        $this->assertNotSame('0234567834', $datas[0]);
        $this->assertNotNull($datas[1]);
        $this->assertNotSame('0334567234', $datas[1]);
        $this->assertNotNull($datas[2]);
        $this->assertNotSame('0534567234', $datas[2]);
        $this->assertNull($datas[3]);
        $this->assertCount(4, \array_unique($datas), 'All generated values are different.');
    }
}
