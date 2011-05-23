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


class ActionsTest extends JoomlaDatabaseTestCase
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
	 * #U,uname,A,M,C,D,ES,E,EO
     */
	public function testActions($user_id,$username,$admin,$manage,$create,$delete,$edit_state,$edit,$edit_own)
	{
		$canDo = AttachmentsPermissions::getActions((int)$user_id);

		$this->assertEquals($canDo->get('core.admin'), (bool)$admin,
							"----> Failed test for $username core.admin for com_attachments, " .
							" expected $admin, got ".$canDo->get('core.admin'));
	}
	

	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testActionsData.csv');
    }


}

?>
