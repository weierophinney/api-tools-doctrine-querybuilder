<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-doctrine-querybuilder for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-doctrine-querybuilder/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-doctrine-querybuilder/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Doctrine\QueryBuilder\Filter;

use DateTime;
use Db\Entity;
use Db\Entity\Album;
use Db\Entity\Artist;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Tools\SchemaTool;
use LaminasTest\ApiTools\Doctrine\QueryBuilder\TestCase;

use function count;

class ORMFilterTest extends TestCase
{
    /** @var ObjectManager */
    protected $objectManager;

    public function setUp(): void
    {
        $this->setApplicationConfig(
            include __DIR__ . '/application.config.php'
        );

        parent::setUp();

        $serviceManager      = $this->getApplication()->getServiceManager();
        $this->objectManager = $serviceManager->get('doctrine.entitymanager.orm_default');
        $objectManager       = $this->objectManager;

        $tool = new SchemaTool($objectManager);

        $tool->createSchema($objectManager->getMetadataFactory()->getAllMetadata());

        $artist1 = new Entity\Artist();
        $artist1->setName('ArtistOne');
        $artist1->setCreatedAt(new DateTime('2011-12-18 13:17:17'));
        $objectManager->persist($artist1);

        $artist2 = new Entity\Artist();
        $artist2->setName('ArtistTwo');
        $artist2->setCreatedAt(new DateTime('2014-12-18 13:17:17'));
        $objectManager->persist($artist2);

        $artist3 = new Entity\Artist();
        $artist3->setName('ArtistThree');
        $artist3->setCreatedAt(new DateTime('2012-12-18 13:17:17'));
        $objectManager->persist($artist3);

        $artist4 = new Entity\Artist();
        $artist4->setName('ArtistFour');
        $artist4->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $objectManager->persist($artist4);

        $artist5 = new Entity\Artist();
        $artist5->setName('ArtistFive');
        $objectManager->persist($artist5);

        $album1 = new Entity\Album();
        $album1->setName('AlbumOne');
        $album1->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $album1->setArtist($artist1);
        $objectManager->persist($album1);

        $album2 = new Entity\Album();
        $album2->setName('AlbumTwo');
        $album2->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $album2->setArtist($artist1);
        $objectManager->persist($album2);

        $album3 = new Entity\Album();
        $album3->setName('AlbumThree');
        $album3->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $album3->setArtist($artist1);
        $objectManager->persist($album3);

        $album4 = new Entity\Album();
        $album4->setName('AlbumFour');
        $album4->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $album4->setArtist($artist2);
        $objectManager->persist($album4);

        $album5 = new Entity\Album();
        $album5->setName('AlbumFive');
        $album5->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $album5->setArtist($artist2);
        $objectManager->persist($album5);

        $album6 = new Entity\Album();
        $album6->setName('AlbumSix');
        $album6->setCreatedAt(new DateTime('2013-12-18 13:17:17'));
        $objectManager->persist($album6);

        $objectManager->flush();
    }

    public function testOrX()
    {
        $filters = [
            [
                'type'       => 'orx',
                'conditions' => [
                    [
                        'field' => 'name',
                        'type'  => 'eq',
                        'value' => 'ArtistOne',
                    ],
                    [
                        'field' => 'name',
                        'type'  => 'eq',
                        'value' => 'ArtistTwo',
                    ],
                ],
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));

        $filters = [
            [
                'type'       => 'orx',
                'conditions' => [
                    [
                        'field' => 'name',
                        'type'  => 'eq',
                        'value' => 'ArtistOne',
                    ],
                    [
                        'field' => 'name',
                        'type'  => 'eq',
                        'value' => 'ArtistTwo',
                    ],
                ],
            ],
            [
                'type'  => 'eq',
                'field' => 'createdAt',
                'value' => '2014-12-18 13:17:17',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));
    }

    public function testAndX()
    {
        $filters = [
            [
                'type'       => 'andx',
                'conditions' => [
                    [
                        'field' => 'name',
                        'type'  => 'eq',
                        'value' => 'ArtistOne',
                    ],
                    [
                        'field' => 'name',
                        'type'  => 'eq',
                        'value' => 'ArtistTwo',
                    ],
                ],
            ],
        ];

        self::assertEquals(0, $this->countResult($filters));

        $filters = [
            [
                'type'       => 'andx',
                'conditions' => [
                    [
                        'field' => 'createdAt',
                        'type'  => 'eq',
                        'value' => '2014-12-18 13:17:17',
                    ],
                    [
                        'field' => 'name',
                        'type'  => 'eq',
                        'value' => 'ArtistTwo',
                    ],
                ],
            ],
            [
                'where' => 'or',
                'type'  => 'eq',
                'field' => 'name',
                'value' => 'ArtistOne',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));
    }

    public function testEquals()
    {
        $filters = [
            [
                'field' => 'name',
                'type'  => 'eq',
                'value' => 'ArtistOne',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'eq',
                'value'  => '2014-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'eq',
                'value'  => '2014-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'eq',
                'value'  => '2012-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));
    }

    public function testNotEquals()
    {
        $filters = [
            [
                'field' => 'name',
                'type'  => 'neq',
                'value' => 'ArtistOne',
            ],
        ];

        self::assertEquals(4, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'neq',
                'value'  => '2014-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'neq',
                'value'  => '2014-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'neq',
                'value'  => '2012-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));
    }

    public function testLessThan()
    {
        $filters = [
            [
                'field'  => 'createdAt',
                'type'   => 'lt',
                'value'  => '2014-01-01',
                'format' => 'Y-m-d',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'and',
                'type'  => 'lt',
                'value' => '2013-12-18 13:17:17',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'or',
                'type'  => 'lt',
                'value' => '2013-12-18 13:17:17',
            ],
            [
                'field' => 'name',
                'where' => 'or',
                'type'  => 'eq',
                'value' => 'ArtistTwo',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));
    }

    public function testLessThanOrEquals()
    {
        $filters = [
            [
                'field'  => 'createdAt',
                'type'   => 'lte',
                'value'  => '2011-12-20',
                'format' => 'Y-m-d',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'type'  => 'lte',
                'value' => '2011-12-18 13:17:16',
            ],
        ];

        self::assertEquals(0, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'and',
                'type'  => 'lte',
                'value' => '2013-12-18 13:17:17',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'or',
                'type'  => 'lte',
                'value' => '2013-12-18 13:17:17',
            ],
            [
                'field' => 'name',
                'where' => 'or',
                'type'  => 'eq',
                'value' => 'ArtistTwo',
            ],
        ];

        self::assertEquals(4, $this->countResult($filters));
    }

    public function testGreaterThan()
    {
        $filters = [
            [
                'field'  => 'createdAt',
                'type'   => 'gt',
                'value'  => '2014-01-01',
                'format' => 'Y-m-d',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'gt',
                'value'  => '2013-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'gt',
                'value'  => '2013-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'gt',
                'value'  => '2012-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));
    }

    public function testGreaterThanOrEquals()
    {
        $filters = [
            [
                'field' => 'createdAt',
                'type'  => 'gte',
                'value' => '2014-12-18 13:17:17',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'type'  => 'gte',
                'value' => '2014-12-18 13:17:18',
            ],
        ];

        self::assertEquals(0, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'gte',
                'value'  => '2013-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'gte',
                'value'  => '2013-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'gte',
                'value'  => '2012-12-18 13:17:17',
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));
    }

    public function testIsNull()
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $objectManager  = $serviceManager->get('doctrine.entitymanager.orm_default');

        $filters = [
            [
                'field' => 'createdAt',
                'type'  => 'isnull',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'and',
                'type'  => 'isnull',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'or',
                'type'  => 'isnull',
            ],
            [
                'field' => 'name',
                'where' => 'or',
                'type'  => 'eq',
                'value' => 'ArtistOne',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));
    }

    public function testIsNotNull()
    {
        $filters = [
            [
                'field' => 'createdAt',
                'type'  => 'isnotnull',
            ],
        ];

        self::assertEquals(4, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'and',
                'type'  => 'isnotnull',
            ],
        ];

        self::assertEquals(4, $this->countResult($filters));

        $filters = [
            [
                'field' => 'createdAt',
                'where' => 'or',
                'type'  => 'isnotnull',
            ],
            [
                'field' => 'name',
                'where' => 'or',
                'type'  => 'eq',
                'value' => 'ArtistFive',
            ],
        ];

        self::assertEquals(5, $this->countResult($filters));
    }

    public function testIn()
    {
        $filters = [
            [
                'field'  => 'name',
                'type'   => 'in',
                'values' => [
                    'ArtistOne',
                    'ArtistTwo',
                ],
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'in',
                'values' => ['2011-12-18 13:17:17'],
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'in',
                'values' => ['2011-12-18 13:17:17'],
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));
    }

    public function testNotIn()
    {
        $filters = [
            [
                'field'  => 'name',
                'type'   => 'notin',
                'values' => ['ArtistOne', 'ArtistTwo'],
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'notin',
                'values' => [
                    '2011-12-18 13:17:17',
                    'format' => 'Y-m-d H:i:s',
                ],
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'notin',
                'values' => [
                    '2011-12-18 13:17:17',
                ],
                'format' => 'Y-m-d H:i:s',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters));
    }

    public function testBetween()
    {
        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'and',
                'type'   => 'between',
                'from'   => '2012-12-15',
                'to'     => '2013-01-01',
                'format' => 'Y-m-d',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'where'  => 'or',
                'type'   => 'between',
                'from'   => '2010-12-15',
                'to'     => '2013-01-01',
                'format' => 'Y-m-d',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));

        $filters = [
            [
                'field'  => 'createdAt',
                'type'   => 'between',
                'from'   => '2010-12-15',
                'to'     => '2013-01-01',
                'format' => 'Y-m-d',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters));
    }

    public function testLike(): void
    {
        $filters = [
            [
                'field' => 'name',
                'type'  => 'like',
                'value' => 'Artist%',
            ],
        ];

        self::assertEquals(5, $this->countResult($filters));

        $filters = [
            [
                'field' => 'name',
                'type'  => 'like',
                'value' => '%Two',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'field' => 'name',
                'where' => 'and',
                'type'  => 'like',
                'value' => '%Art%',
            ],
        ];

        self::assertEquals(5, $this->countResult($filters));

        $filters = [
            [
                'field' => 'name',
                'where' => 'or',
                'type'  => 'like',
                'value' => 'ArtistT%',
            ],
            [
                'field' => 'name',
                'where' => 'or',
                'type'  => 'like',
                'value' => 'ArtistF%',
            ],
        ];

        self::assertEquals(4, $this->countResult($filters));
    }

    public function testNotLike(): void
    {
        $filters = [
            [
                'field' => 'name',
                'type'  => 'notlike',
                'value' => '%Two',
            ],
        ];

        self::assertEquals(4, $this->countResult($filters));

        $filters = [
            [
                'field' => 'name',
                'where' => 'and',
                'type'  => 'notlike',
                'value' => '%Art%',
            ],
        ];

        self::assertEquals(0, $this->countResult($filters));

        $filters = [
            [
                'field' => 'name',
                'where' => 'and',
                'type'  => 'notlike',
                'value' => 'ArtistT%',
            ],
            [
                'field' => 'name',
                'where' => 'and',
                'type'  => 'notlike',
                'value' => 'ArtistF%',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters));
    }

    public function testIsMemberOf()
    {
        $albumOneId = $this->objectManager
            ->getRepository('Db\Entity\Album')
            ->findOneBy(['name' => 'AlbumOne'])
            ->getId();
        $albumSixId = $this->objectManager
            ->getRepository('Db\Entity\Album')
            ->findOneBy(['name' => 'AlbumSix'])
            ->getId();

        $filters = [
            [
                'type'  => 'ismemberof',
                'where' => 'and',
                'field' => 'album',
                'value' => $albumOneId,
            ],
        ];
        self::assertEquals(1, $this->countResult($filters));

        $filters = [
            [
                'type'  => 'ismemberof',
                'where' => 'and',
                'field' => 'album',
                'value' => $albumSixId,
            ],
        ];
        self::assertEquals(0, $this->countResult($filters));
    }

    public function testInnerJoin()
    {
        $filters = [
            [
                'type'  => 'innerjoin',
                'alias' => 'a',
                'field' => 'artist',
            ],
            [
                'alias' => 'a',
                'field' => 'name',
                'type'  => 'eq',
                'value' => 'ArtistOne',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters, Album::class));

        $filters = [
            [
                'type'        => 'innerjoin',
                'parentAlias' => 'row',
                'alias'       => 'a',
                'field'       => 'artist',
            ],
            [
                'alias' => 'a',
                'field' => 'name',
                'type'  => 'eq',
                'value' => 'ArtistTwo',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters, Album::class));

        $filters = [
            [
                'type'          => 'innerjoin',
                'parentAlias'   => 'row',
                'alias'         => 'a',
                'field'         => 'artist',
                'conditionType' => 'WITH',
                'condition'     => "a.name = 'ArtistTwo'",
            ],
        ];

        self::assertEquals(2, $this->countResult($filters, Album::class));
    }

    public function testLeftJoin()
    {
        $filters = [
            [
                'type'  => 'leftjoin',
                'alias' => 'a',
                'field' => 'artist',
            ],
            [
                'alias' => 'a',
                'field' => 'name',
                'type'  => 'eq',
                'value' => 'ArtistOne',
            ],
        ];

        self::assertEquals(3, $this->countResult($filters, Album::class));

        $filters = [
            [
                'type'        => 'leftjoin',
                'parentAlias' => 'row',
                'alias'       => 'a',
                'field'       => 'artist',
            ],
            [
                'alias' => 'a',
                'field' => 'name',
                'type'  => 'eq',
                'value' => 'ArtistTwo',
            ],
        ];

        self::assertEquals(2, $this->countResult($filters, Album::class));

        /**
         * ArtistThree has no shows
         */
        $filters = [
            [
                'type'        => 'leftjoin',
                'parentAlias' => 'row',
                'alias'       => 'a',
                'field'       => 'artist',
            ],
            [
                'type'  => 'isnull',
                'field' => 'id',
                'alias' => 'a',
            ],
        ];

        self::assertEquals(1, $this->countResult($filters, Album::class));
    }

    private function countResult(array $filters, string $entity = Artist::class): int
    {
        $serviceManager = $this->getApplication()->getServiceManager();
        $filterManager  = $serviceManager->get('LaminasDoctrineQueryBuilderFilterManagerOrm');
        $objectManager  = $this->objectManager;

        $queryBuilder = $objectManager->createQueryBuilder();
        $queryBuilder->select('row')
                     ->from($entity, 'row');

        $metadata = $objectManager->getMetadataFactory()->getAllMetadata();

        $filterManager->filter($queryBuilder, $metadata[0], $filters);

        $result = $queryBuilder->getQuery()->getResult();

        return count($result);
    }
}
