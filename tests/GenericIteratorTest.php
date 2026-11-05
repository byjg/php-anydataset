<?php

namespace Tests;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use Generator;
use LogicException;
use Override;
use PHPUnit\Framework\TestCase;

class GenericIteratorTest extends TestCase
{
    /**
     * The rows read so far and whether the source was released, shared with the static generator
     */
    private static array $source;

    #[Override]
    protected function setUp(): void
    {
        self::$source = ['read' => 0, 'released' => false];
    }

    private static function iterator(): GenericIterator
    {
        return new class extends GenericIterator {
            #[Override]
            protected function rows(): Generator
            {
                return self::read();
            }

            private static function read(): Generator
            {
                try {
                    for ($i = 1; $i <= 3; $i++) {
                        GenericIteratorTest::readRow();
                        yield new Row(['id' => $i]);
                    }
                } finally {
                    GenericIteratorTest::release();
                }
            }
        };
    }

    public static function readRow(): void
    {
        self::$source['read']++;
    }

    public static function release(): void
    {
        self::$source['released'] = true;
    }

    public function testReadsAllTheRows(): void
    {
        $this->assertEquals([['id' => 1], ['id' => 2], ['id' => 3]], self::iterator()->toArray());
        $this->assertTrue(self::$source['released']);
    }

    public function testReleasesTheSourceWhenAPartlyReadIteratorIsDestroyed(): void
    {
        $iterator = self::iterator();
        $this->assertEquals(['id' => 1], $iterator->first());
        $this->assertFalse(self::$source['released']);

        unset($iterator);

        // Released at once, not when the garbage collector runs
        $this->assertTrue(self::$source['released']);
        $this->assertEquals(1, self::$source['read']);
    }

    public function testMovesForwardOnly(): void
    {
        $iterator = self::iterator();
        $iterator->next();

        $this->assertEquals([['id' => 2], ['id' => 3]], $iterator->toArray());
        $this->assertSame([], $iterator->toArray());
    }

    public function testForeachContinuesFromTheCurrentRow(): void
    {
        // valid()/current() read the first row without moving: a foreach starts at it
        $iterator = self::iterator();
        $this->assertEquals(['id' => 1], $iterator->current()->toArray());
        $ids = [];
        foreach ($iterator as $row) {
            $ids[] = $row->get('id');
        }
        $this->assertSame([1, 2, 3], $ids);

        // A break keeps the row where it stopped: the next foreach starts at it
        $iterator = self::iterator();
        $ids = [];
        foreach ($iterator as $row) {
            $ids[] = $row->get('id');
            if ($row->get('id') === 2) {
                break;
            }
        }
        foreach ($iterator as $row) {
            $ids[] = $row->get('id');
        }
        $this->assertSame([1, 2, 2, 3], $ids);

        // After the last row, a foreach has no rows
        $this->assertFalse($iterator->valid());
        $this->assertSame([], $iterator->toArray());
    }

    public function testForeachKeys(): void
    {
        $this->assertSame([0, 1, 2], array_keys(iterator_to_array(self::iterator())));
    }

    public function testRowsMustNotYieldItself(): void
    {
        $iterator = new class extends GenericIterator {
            #[Override]
            protected function rows(): Generator
            {
                yield new Row(['id' => 1]);
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('must return the generator of a static function');
        $iterator->valid();
    }
}
