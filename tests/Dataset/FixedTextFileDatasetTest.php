<?php

namespace Tests\AnyDataset\Dataset;

use ByJG\AnyDataset\Dataset\FixedTextFileDataset;
use ByJG\AnyDataset\Enum\FixedTextDefinition;
use PHPUnit\Framework\TestCase;

class FixedTextFileDatasetTest extends TestCase
{

    /**
     * @var FixedTextFileDataset
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testGetIterator()
    {
        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('enable', 10, 1, null, 'S|N'),
            new FixedTextDefinition('code', 11, 4),
        ];

        $repository = new FixedTextFileDataset(__DIR__ . '/sample-fixed.txt', $fieldDefinition);

        $this->assertEquals([
            0 => [
                'id' => '001',
                'name' => 'JOAO   ',
                'enable' => 'S',
                'code' => '1520'
            ],
            1 => [
                'id' => '002',
                'name' => 'GILBERT',
                'enable' => 'S',
                'code' => '1621'
            ]
            ], $repository->getIterator()->toArray());
    }

    public function testGetIterator_SubTypes()
    {
        $fieldDefinition = [
            new FixedTextDefinition('id', 0, 3),
            new FixedTextDefinition('name', 3, 7),
            new FixedTextDefinition('enable', 10, 1, null, 'S|N'),
            new FixedTextDefinition(
                'code',
                11,
                4,
                null,
                [
                    new FixedTextDefinition('first', 0, 1),
                    new FixedTextDefinition('second', 1, 3),
                ]
            ),
        ];

        $repository = new FixedTextFileDataset(__DIR__ . '/sample-fixed.txt', $fieldDefinition);

        $this->assertEquals([
            0 => [
                'id' => '001',
                'name' => 'JOAO   ',
                'enable' => 'S',
                'code' => [
                    'first' => '1',
                    'second' => '520'
                ]
            ],
            1 => [
                'id' => '002',
                'name' => 'GILBERT',
                'enable' => 'S',
                'code' => [
                    'first' => '1',
                    'second' => '621'
                ]
            ]
            ], $repository->getIterator()->toArray());
    }
}
