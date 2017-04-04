<?php

namespace ByJG\AnyDataset\Dataset;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class FixedTextFileDatasetTest extends \PHPUnit\Framework\TestCase
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
            new \ByJG\AnyDataset\Enum\FixedTextDefinition('id', 0, 3),
            new \ByJG\AnyDataset\Enum\FixedTextDefinition('name', 3, 7),
            new \ByJG\AnyDataset\Enum\FixedTextDefinition('enable', 10, 1, null, 'S|N'),
            new \ByJG\AnyDataset\Enum\FixedTextDefinition('code', 11, 4),
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
            new \ByJG\AnyDataset\Enum\FixedTextDefinition('id', 0, 3),
            new \ByJG\AnyDataset\Enum\FixedTextDefinition('name', 3, 7),
            new \ByJG\AnyDataset\Enum\FixedTextDefinition('enable', 10, 1, null, 'S|N'),
            new \ByJG\AnyDataset\Enum\FixedTextDefinition(
                'code',
                11,
                4,
                null,
                [
                    new \ByJG\AnyDataset\Enum\FixedTextDefinition('first', 0, 1),
                    new \ByJG\AnyDataset\Enum\FixedTextDefinition('second', 1, 3),
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
