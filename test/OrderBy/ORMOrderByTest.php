<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-doctrine-querybuilder for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-doctrine-querybuilder/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-doctrine-querybuilder/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Doctrine\QueryBuilder\OrderBy;

use DateTime;
use Db\Entity;
use Doctrine\ORM\Tools\SchemaTool;
use LaminasTest\ApiTools\Doctrine\QueryBuilder\TestCase;

use function reset;

class ORMOrderByTest extends TestCase
{
    /**
     * @return mixed
     */
    private function fetchResult(iterable $orderBy, string $entity = 'Db\Entity\Artist')
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $orderByManager = $serviceManager->get('LaminasDoctrineQueryBuilderOrderByManagerOrm');
        $objectManager  = $serviceManager->get('doctrine.entitymanager.orm_default');

        $queryBuilder = $objectManager->createQueryBuilder();
        $queryBuilder->select('row')
            ->from($entity, 'row');

        $metadata = $objectManager->getMetadataFactory()->getAllMetadata();

        $orderByManager->orderBy($queryBuilder, $metadata[0], $orderBy);

        return $queryBuilder->getQuery()->getResult();
    }

    public function setUp(): void
    {
        $this->setApplicationConfig(
            include __DIR__ . '/application.config.php'
        );
        parent::setUp();

        $serviceManager = $this->getApplication()->getServiceManager();
        $objectManager  = $serviceManager->get('doctrine.entitymanager.orm_default');

        $tool = new SchemaTool($objectManager);
        $res  = $tool->createSchema($objectManager->getMetadataFactory()->getAllMetadata());

        $artist1 = new Entity\Artist();
        $artist1->setName('ABBA');
        $artist1->setCreatedAt(new DateTime('2011-12-18 13:17:17'));
        $objectManager->persist($artist1);

        $artist2 = new Entity\Artist();
        $artist2->setName('Band, The');
        $artist2->setCreatedAt(new DateTime('2014-12-18 13:17:17'));
        $objectManager->persist($artist2);

        $artist3 = new Entity\Artist();
        $artist3->setName('CubanStack');
        $artist3->setCreatedAt(new DateTime('2012-12-18 13:17:17'));
        $objectManager->persist($artist3);

        $artist4 = new Entity\Artist();
        $artist4->setName('Drunk in July');
        $artist4->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $objectManager->persist($artist4);

        $artist5 = new Entity\Artist();
        $artist5->setName('Ekoostic Hookah');
        $objectManager->persist($artist5);

        $objectManager->flush();
    }

    public function testField()
    {
        $orderBy = [
            [
                'type'      => 'field',
                'field'     => 'name',
                'direction' => 'desc',
            ],
        ];

        $result = $this->fetchResult($orderBy);
        $artist = reset($result);

        $this->assertEquals('Ekoostic Hookah', $artist->getName());

        $orderBy = [
            [
                'type'      => 'field',
                'field'     => 'name',
                'direction' => 'asc',
            ],
        ];

        $result = $this->fetchResult($orderBy);
        $artist = reset($result);

        $this->assertEquals('ABBA', $artist->getName());
    }
}
