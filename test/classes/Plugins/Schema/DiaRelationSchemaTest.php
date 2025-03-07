<?php

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Plugins\Schema;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Plugins\Schema\Dia\DiaRelationSchema;
use PhpMyAdmin\Relation;
use PhpMyAdmin\Tests\AbstractTestCase;
use PhpMyAdmin\Version;

/**
 * @requires extension xmlwriter
 */
class DiaRelationSchemaTest extends AbstractTestCase
{
    /** @var DiaRelationSchema */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        parent::setUp();
        $_REQUEST['page_number'] = 33;
        $_REQUEST['dia_show_color'] = true;
        $_REQUEST['dia_show_keys'] = true;
        $_REQUEST['dia_orientation'] = 'orientation';
        $_REQUEST['dia_paper'] = 'paper';
        $_REQUEST['t_h'] = ['information_schema.files' => 1];
        $_REQUEST['t_x'] = ['information_schema.files' => 0];
        $_REQUEST['t_y'] = ['information_schema.files' => 0];
        $_POST['t_db'] = ['information_schema'];
        $_POST['t_tbl'] = ['files'];

        $GLOBALS['server'] = 1;
        $GLOBALS['db'] = 'information_schema';
        $GLOBALS['cfg']['Server']['table_coords'] = 'table_name';

        //_SESSION
        $_SESSION['relation'][$GLOBALS['server']] = [
            'version' => Version::VERSION,
            'table_coords' => 'table_name',
            'displaywork' => 'displaywork',
            'db' => 'information_schema',
            'table_info' => 'table_info',
            'relwork' => 'relwork',
            'relation' => 'relation',
        ];
        $relation = new Relation($GLOBALS['dbi']);
        $relation->getRelationsParam();

        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dbi->expects($this->any())
            ->method('numRows')
            ->will($this->returnValue(1));

        $dbi->expects($this->any())
            ->method('query')
            ->will($this->returnValue('executed_1'));

        $dbi->expects($this->any())
            ->method('tryQuery')
            ->will($this->returnValue('executed_1'));

        //table name in information_schema_relations
        $fetchArrayReturn = ['table_name' => 'CHARACTER_SETS'];

        //table name in information_schema_relations
        $fetchArrayReturn2 = ['table_name' => 'COLLATIONS'];

        $dbi->expects($this->at(2))
            ->method('fetchAssoc')
            ->will($this->returnValue($fetchArrayReturn));
        $dbi->expects($this->at(3))
            ->method('fetchAssoc')
            ->will($this->returnValue($fetchArrayReturn2));
        $dbi->expects($this->at(4))
            ->method('fetchAssoc')
            ->will($this->returnValue(null));

        $getIndexesResult = [
            [
                'Table' => 'pma_tbl',
                'Field' => 'field1',
                'Key' => 'PRIMARY',
                'Key_name' => 'Key_name',
                'Column_name' => 'Column_name',
            ],
        ];
        $dbi->expects($this->any())->method('getTableIndexes')
            ->will($this->returnValue($getIndexesResult));

        $fetchValue = "CREATE TABLE `pma_bookmark` (
             `id` int(11) NOT NULL AUTO_INCREMENT,
              `dbase` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
              `user` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
              `label` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
              `query` text COLLATE utf8_bin NOT NULL,
              PRIMARY KEY (`id`)
             ) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 "
            . "COLLATE=utf8_bin COMMENT='Bookmarks'";

        $dbi->expects($this->any())
            ->method('fetchValue')
            ->will($this->returnValue($fetchValue));

        $GLOBALS['dbi'] = $dbi;

        $this->object = new DiaRelationSchema('information_schema');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->object);
    }

    /**
     * Test for construct, the Property is set correctly
     *
     * @group medium
     */
    public function testSetProperty(): void
    {
        $this->assertEquals(
            33,
            $this->object->getPageNumber()
        );
        $this->assertTrue(
            $this->object->isShowColor()
        );
        $this->assertTrue(
            $this->object->isShowKeys()
        );
        $this->assertEquals(
            'L',
            $this->object->getOrientation()
        );
        $this->assertEquals(
            'paper',
            $this->object->getPaper()
        );
    }
}
