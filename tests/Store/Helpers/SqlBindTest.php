<?php
/**
 * User: jg
 * Date: 16/02/17
 * Time: 11:22
 */

namespace Store;

use ByJG\AnyDataset\Store\Helpers\SqlBind;
use ByJG\Util\Uri;

// backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');
}

class SqlBindTest extends \PHPUnit\Framework\TestCase
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
                new Uri('sqlrelay://host'),
                'insert into value ([[name]], [[surname]], [[age]])',
                'insert into value (?, ?, ?)',
                $paramIn,
                $paramIn
            ],
            [
                new Uri('sqlrelay://host'),
                'insert into value (:name, :surname, :age)',
                'insert into value (?, ?, ?)',
                $paramIn,
                $paramIn
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
