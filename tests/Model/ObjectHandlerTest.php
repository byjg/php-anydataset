<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\Util\XmlUtil;
use stdClass;
use Tests\Sample\ModelGetter;
use Tests\Sample\ModelList;
use Tests\Sample\ModelList2;
use Tests\Sample\ModelList3;
use Tests\Sample\ModelPublic;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-04-29 at 18:59:37.
 */
class ObjectHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ObjectHandler
     */
    protected $object;
    protected $document;
    protected $root;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->document = XmlUtil::CreateXmlDocumentFromStr("<root/>");
        $this->root = $this->document->documentElement;
        //$this->object = new ObjectHandler;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_ObjectGetter_1elem()
    {
        $model = new ModelGetter(10, 'Joao');

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<ModelGetter>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</ModelGetter>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_ObjectGetter_2elem()
    {
        $model = array(
            new ModelGetter(10, 'Joao'),
            new ModelGetter(20, 'JG')
        );

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<ModelGetter>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</ModelGetter>'
                . '<ModelGetter>'
                . '<Id>20</Id>'
                . '<Name>JG</Name>'
                . '</ModelGetter>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_ObjectPublic_1elem()
    {
        $model = new ModelPublic(10, 'Joao');

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Tests_Sample_ModelPublic>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</Tests_Sample_ModelPublic>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_ObjectPublic_2elem()
    {
        $model = array(
            new ModelPublic(10, 'Joao'),
            new ModelPublic(20, 'JG')
        );

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Tests_Sample_ModelPublic>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</Tests_Sample_ModelPublic>'
                . '<Tests_Sample_ModelPublic>'
                . '<Id>20</Id>'
                . '<Name>JG</Name>'
                . '</Tests_Sample_ModelPublic>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_StdClass_1()
    {
        $model = new stdClass();
        $model->Id = 10;
        $model->Name = 'Joao';

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_StdClass_Model()
    {
        $model = new stdClass();
        $model->Id = 10;
        $model->Name = 'Joao';
        $model->Object = new ModelGetter(20, 'JG');

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '<Object>'
                . '<ModelGetter>'
                . '<Id>20</Id>'
                . '<Name>JG</Name>'
                . '</ModelGetter>'
                . '</Object>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_1()
    {
        $model = [
            'Id' => 10,
            'Name' => 'Joao'
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_2()
    {
        $model = [
            'Id' => 10,
            'Name' => 'Joao',
            'Data' =>
            [
                'Code' => '2',
                'Sector' => '3'
            ]
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '<Data>'
                . '<Code>2</Code>'
                . '<Sector>3</Sector>'
                . '</Data>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_StdClass_Array()
    {
        $model = new stdClass();
        $model->Obj = [
            'Id' => 10,
            'Name' => 'Joao'
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Obj>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</Obj>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_Scalar()
    {
        $model = new stdClass();
        $model->Obj = [
            10,
            'Joao'
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Obj>10</Obj>'
                . '<Obj>Joao</Obj>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_Mixed()
    {
        $model = new stdClass();
        $model->Obj = [
            10,
            'Joao',
            new ModelGetter(20, 'JG')
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Obj>10</Obj>'
                . '<Obj>Joao</Obj>'
                . '<Obj><ModelGetter><Id>20</Id><Name>JG</Name></ModelGetter></Obj>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_Array()
    {
        $model = new stdClass();
        $model->Obj = [
            'Item1' =>
            [
                10,
                'Joao'
            ],
            'Item2' =>
            [
                20,
                'JG'
            ]
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Obj>'
                . '<Item1>10</Item1><Item1>Joao</Item1>'
                . '<Item2>20</Item2><Item2>JG</Item2>'
                . '</Obj>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_Array_2()
    {
        $model = new stdClass();
        $model->Obj = [
            [
                10,
                'Joao'
            ]
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Obj>'
                . '<scalar>10</scalar>'
                . '<scalar>Joao</scalar>'
                . '</Obj>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_Array_3()
    {
        $model = [
            [
                'Id' => 10,
                'Name' => 'Joao'
            ],
            [
                'Id' => 11,
                'Name' => 'Gilberto'
            ],
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');

        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<__object>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</__object>'
                . '<__object>'
                . '<Id>11</Id>'
                . '<Name>Gilberto</Name>'
                . '</__object>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Array_Array_5()
    {
        $model = new stdClass;

        $model->Title = 'testing';
        $model->List = [
            [
                'Id' => 10,
                'Name' => 'Joao'
            ],
            [
                'Id' => 11,
                'Name' => 'Gilberto'
            ],
        ];
        $model->Group = "test";

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');

        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<Title>testing</Title>'
                . '<List>'
                . '<Id>10</Id>'
                . '<Name>Joao</Name>'
                . '</List>'
                . '<List>'
                . '<Id>11</Id>'
                . '<Name>Gilberto</Name>'
                . '</List>'
                . '<Group>test</Group>'
                . '</root>'), $this->document
        );

         $this->assertEquals('{"Title":"testing","List":[{"Id":"10","Name":"Joao"},{"Id":"11","Name":"Gilberto"}],"Group":"test"}', $this->object->xml2json($this->document));
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Collection_DontCreateNode()
    {
        $modellist = new ModelList();
        $modellist->addItem(new ModelGetter(10, 'Joao'));
        $modellist->addItem(new ModelGetter(20, 'JG'));

        $this->object = new ObjectHandler($this->root, $modellist, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<ModelList>'
                . '<ModelGetter><Id>10</Id><Name>Joao</Name></ModelGetter>'
                . '<ModelGetter><Id>20</Id><Name>JG</Name></ModelGetter>'
                . '</ModelList>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Collection_CreateNode()
    {
        $modellist = new ModelList2();
        $modellist->addItem(new ModelGetter(10, 'Joao'));
        $modellist->addItem(new ModelGetter(20, 'JG'));

        $this->object = new ObjectHandler($this->root, $modellist, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<ModelList>'
                . '<collection>'
                . '<ModelGetter><Id>10</Id><Name>Joao</Name></ModelGetter>'
                . '<ModelGetter><Id>20</Id><Name>JG</Name></ModelGetter>'
                . '</collection>'
                . '</ModelList>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_Collection_SkipParentAndRenameChild()
    {
        $modellist = new ModelList3();
        $modellist->addItem(new ModelGetter(10, 'Joao'));
        $modellist->addItem(new ModelGetter(20, 'JG'));

        $this->object = new ObjectHandler($this->root, $modellist, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<ModelList>'
                . '<List><Id>10</Id><Name>Joao</Name></List>'
                . '<List><Id>20</Id><Name>JG</Name></List>'
                . '</ModelList>'
                . '</root>'), $this->document
        );
    }

    /**
     * @covers ByJG\AnyDataset\Model\ObjectHandler::CreateObjectFromModel
     * @todo   Implement testCreateObjectFromModel().
     */
    public function testCreateObjectFromModel_OnlyScalarAtFirstLevel()
    {
        $model = [
            10,
            'Joao'
        ];

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<scalar>10</scalar>'
                . '<scalar>Joao</scalar>'
                . '</root>'), $this->document
        );
    }

    public function testEmptyValues()
    {
        $model = new stdClass();
        $model->varFalse = false;
        $model->varTrue = true;
        $model->varZero = 0;
        $model->varZeroStr = '0';
        $model->varNull = null;        // Sould not created
        $model->varEmptyString = '';   // Sould not created

        $this->object = new ObjectHandler($this->root, $model, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<varFalse>false</varFalse>'
                . '<varTrue>true</varTrue>'
                . '<varZero>0</varZero>'
                . '<varZeroStr>0</varZeroStr>'
                . '</root>'), $this->document
        );
    }

    public function testIterator()
    {
        $model = new AnyDataset();
        $model->AddField("id", 10);
        $model->AddField("name", 'Testing');

        $model->appendRow();
        $model->AddField("id", 20);
        $model->AddField("name", 'Other');

        $iterator = $model->getIterator();

        $this->object = new ObjectHandler($this->root, $iterator, 'xmlnuke');
        $result = $this->object->createObjectFromModel();

        $this->assertEquals(
            XmlUtil::CreateXmlDocumentFromStr(
                '<root>'
                . '<row>'
                . '<field name="id">10</field>'
                . '<field name="name">Testing</field>'
                . '</row>'
                . '<row>'
                . '<field name="id">20</field>'
                . '<field name="name">Other</field>'
                . '</row>'
                . '</root>'), $this->document
        );
    }
}
