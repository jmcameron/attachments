<?php
require_once 'PHPUnit/Framework.php';
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

jimport('joomla.log.log');

jimport('joomla.plugin.plugin');
jimport('joomla.plugin.helper');
jimport('joomla.event.dispatcher');
jimport('joomla.filter.filterinput');
jimport('joomla.environment.request');
jimport('joomla.application.component.helper');

require_once JPATH_BASE.'/administrator/components/com_attachments/permissions.php';


class CategoryEditTest extends JoomlaDatabaseTestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();
		parent::setUpBeforeClass();
	}


	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return xml dataset
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS . '/joomla_db.xml');
	}


    /**
     * @dataProvider provider
     */
	public function testCategoryEdit($user_id,$username,$cat_id,$may_edit)
	{
		$result = AttachmentsPermissions::userMayEditCategory((int)$cat_id, (int)$user_id);
		$errmsg = "----> Failed test for $username edit category $cat_id, expected $may_edit, got $result";

		$this->assertEquals($result, (bool)$may_edit, $errmsg);
	}
	

	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testCategoryEditData.csv');
    }


}

?>
