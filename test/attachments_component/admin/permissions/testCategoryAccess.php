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

require_once JPATH_BASE.'/plugins/attachments/attachments_plugin_framework/attachments_plugin_framework.php';

require_once JPATH_BASE.'/administrator/components/com_attachments/permissions.php';


class CategoryAccessTest extends JoomlaDatabaseTestCase
{
	protected $object;

	/**
	 * Receives the callback from JError and logs the required error information for the test.
	 *
	 * @param	JException	The JException object from JError
	 *
	 * @return	bool	To not continue with JError processing
	 */
	static function errorCallback( $error )
	{
		CategoryAccessTest::$actualError['code'] = $error->get('code');
		CategoryAccessTest::$actualError['msg'] = $error->get('message');
		CategoryAccessTest::$actualError['info'] = $error->get('info');
		return false;
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		include_once JPATH_BASE . '/libraries/joomla/database/table.php';
		include_once JPATH_BASE . '/libraries/joomla/user/user.php';

		parent::setUp();
		parent::setUpBeforeClass();

		$this->saveFactoryState();
		$this->saveErrorHandlers();
		$this->setErrorCallback('CategoryAccessTest');
		CategoryAccessTest::$actualError = array();
	}


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->setErrorhandlers($this->savedErrorState);
		$this->restoreFactoryState();
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


	public function testCategoryAccess()
	{

		

	/* ???
		require_once JPATH_BASE.'/plugins/attachments/attachments_for_content/attachments_for_content.php';
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		$parent = $apm->getAttachmentsPlugin('com_content');
	*/
		$this->markTestIncomplete(
		'This test has not been implemented yet.'
		);
	}
	

	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testCategoryAccessData.csv');
    }


}

?>
