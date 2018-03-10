<?php

namespace Tests\AnyDataset\Store\Helpers;

use ByJG\AnyDataset\Store\Helpers\SqlBind;
use ByJG\Util\Uri;
use PHPUnit\Framework\TestCase;

class SqlBindTest extends TestCase
{
    public function getDataTest()
    {
        $paramIn = [
            'name' => 'John',
            'surname' => 'Doe',
            'age' => 43
        ];

        return [
            [
                new Uri('mysql://host'),
                'insert into value ([[name]], [[surname]], [[age]])',
                'insert into value (:name, :surname, :age)',
                $paramIn,
                $paramIn
            ],
            [
                new Uri('mysql://host'),
                'insert into value (:name, :surname, :age)',
                'insert into value (:name, :surname, :age)',
                $paramIn,
                $paramIn
            ],
            [
            new Uri('mysql://host'),
                'insert into value ([[name]], [[surname]], [[age]], [[nonexistant]])',
                'insert into value (:name, :surname, :age, null)',
                $paramIn,
                $paramIn
            ],
            [
                new Uri('mysql://host'),
                'insert into value ([[name]], [[surname]], :age)',
                'insert into value (:name, :surname, :age)',
                $paramIn,
                $paramIn
            ],
            [
                new Uri('mysql://host'),
                'insert into value (:name, [[surname]], [[age]])',
                'insert into value (:name, :surname, :age)',
                $paramIn,
                $paramIn
            ],
            [
                new Uri('mysql://host'),
                'select * from table where [[age]]-1900 > 10',
                'select * from table where :age-1900 > 10',
                $paramIn,
                [
                    'age' => 43
                ]
            ],
            [
                new Uri('mysql://host'),
                'select * from table where :age-1900 > 10',
                'select * from table where :age-1900 > 10',
                $paramIn,
                [
                    'age' => 43
                ]            ],
            [
                new Uri('mysql://host'),
                'select * from table where age = [[aaa]] and date = [[bbb]]',
                'select * from table where age = null and date = null',
                $paramIn,
                []
            ],
            [
                new Uri('mysql://host'),
                "insert into value (':name', 'a:surname', '[[age]]')",
                "insert into value (':name', 'a:surname', '[[age]]')",
                null,
                []
            ],
            [
                new Uri('mysql://host'),
                "insert into value (':name', 'a:surname', '[[age]]')",
                "insert into value (':name', 'a:surname', '[[age]]')",
                $paramIn,
                []
            ],
            [
                new Uri('mysql://host'),
                "insert into value (':na''me', 43, '[[ag''e]]')",
                "insert into value (':na''me', 43, '[[ag''e]]')",
                null,
                []
            ],
        ];
    }

    /**
     * @dataProvider getDataTest()
     */
    public function testSqlBind($uri, $subject, $expected, $paramsIn, $paramsExpected)
    {
        $this->assertEquals(
            [
                $expected,
                $paramsExpected
            ],
            SqlBind::parseSQL(
                $uri,
                $subject,
                $paramsIn
            )
        );
    }
}
