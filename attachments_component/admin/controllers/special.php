<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

/**
 * The controller for special requests
 * (adapted from administrator/components/com_config/controllers/component.php)
 *
 * @package Attachments
 */
class AttachmentsControllerSpecial extends JController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		$default['default_task'] = 'noop';
		parent::__construct( $default );
	}

	/** A noop function so this controller does not have a usable default */
	function noop()
	{
		echo "<h1>" . JText::_('ERROR_NO_SPECIAL_FUNCTION_SPECIFIED') . "</h1>";
		exit();
	}

	/**
	 * This function allows editing the Attachments parameters
	 * with a form that has a regular submit button (not Javascript).
	 *
	 * This function is for automated testing of the Attachments
	 * extension and as exactly the same functionality as the regular
	 * Attachments parameter editor in the component manager, except
	 * that it is not a pop-up window.
	 *
	 * Due to the implementation of the component parameter editor for
	 * pop up frames, this save form pops back into edit mode after saving.
	 */
	function editParams()
	{
		$model = $this->getModel('Special');
		if ( !$model ) {
			$errmsg = JText::_('ERROR_UNABLE_TO_FIND_SPECIAL_MODEL') . ' (ERR 34)';
			JError::raiseError( 500, $errmsg);
			}

		$table =& JTable::getInstance('component');
		if (!$table->loadByOption( 'com_attachments' )) {
			$errmsg = JText::_('ERROR_UNABLE_TO_LOAD_ATTACHMENTS_COMPONENT') . ' (ERR 35)';
			JError::raiseError( 500, $errmsg);
			}

		require_once(JPATH_COMPONENT.DS.'views'.DS.'special'.DS.'view.php');
		$view = new AttachmentsViewSpecial( );
		$view->assignRef('component', $table);
		$view->setModel( $model, true );
		$view->display();
	}

	/** Show the current SEF mode */
	function showSEF()
	{
		$app = JFactory::getApplication();
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo "<html><head><title>SEF Status</title></head><body>";
		echo "SEF: " . $app->getCfg('sef') . "<br />";
		echo "</body></html>";
		exit();
	}

	/** Show a list of all attachment IDs */
	function listAttachmentIDs()
	{
		$db =& JFactory::getDBO();
		$query = "SELECT attach.id,parent_id,parent_type,art.sectionid,art.catid FROM #__attachments as attach ";
		$query .= "LEFT JOIN #__content as art ON attach.parent_id = art.id ORDER BY art.id";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo "\n<html><head><title>Attachment IDs</title></head><body>\n";
		echo "Attachment IDS:";
		foreach ($rows as $row) {
			echo " " . $row->id . "/" . $row->parent_id . "/" . $row->parent_type . "/" . $row->sectionid . "/" . $row->catid;
			}
		echo "\n</body></html>";
		exit();
	}


	// Define some functions for URLs to invoke the udpate functions
	//	 (We could move these to an update controller...)


	/**
	 * Add icon filenames for attachments missing an icon
	 * (See AttachmentsUpdate::add_icon_filenames() in update.php for details )
	 */
	function add_icon_filenames()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');
		$msg = AttachmentsUpdate::add_icon_filenames();
		$this->setRedirect('index.php?option=' . $this->option, $msg);
	}


	/**
	 * Update any null dates in any attachments
	 * (See AttachmentsUpdate::update_null_dates() in update.php for details )
	 */
	function update_null_dates()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$numUpdated = AttachmentsUpdate::update_null_dates();
		$msg = JText::sprintf( 'UPDATED_N_ATTACHMENTS', $numUpdated );
		$this->setRedirect('index.php?option=' . $this->option, $msg);
	}


	/**
	 * Update the attachments table
	 * (See AttachmentsUpdate::update_attachments_table() in update.php for details )
	 */
	function update_attachments_table()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo "\n<html><head><title>Updating Attachments Tables</title></head><body>\n";
		echo "<h2>Updating Attachments Tables</h2>\n";

		AttachmentsUpdate::update_attachments_table();

		$uri = JFactory::getURI();
		$return_url = $uri->base(true);
		echo "<br />&nbsp;<br /><a href=\"$return_url\">Return to Admin page</a>\n";
		echo "</body>\n</html>";

		exit();
	}


	/**
	 * Disalbe SQL uninstall of existing attachments (when Attachments is uninstalled)
	 * (See AttachmentsUpdate::disable_sql_uninstall() in update.php for details )
	 */
	function disable_sql_uninstall()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::disable_sql_uninstall();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo "<script>window.parent.SqueezeBox.close();
				  window.parent.location.reload();</script>";
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Regenerate system filenames
	 * (See AttachmentsUpdate::regenerate_system_filenames() in update.php for details )
	 */
	function regenerate_system_filenames()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::regenerate_system_filenames();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
			echo "<script>window.parent.SqueezeBox.close();
				  window.parent.location.reload();</script>";
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}

	/**
	 * Update system filenames to attachments-2.0 format
	 * (See AttachmentsUpdate::update_system_filenames() in update.php for details )
	 */
	function update_system_filenames()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::update_system_filenames();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
			echo "<script>window.parent.SqueezeBox.close();
				  window.parent.location.reload();</script>";
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Remove spaces from system filenames for all attachments
	 * (See AttachmentsUpdate::remove_spaces_from_system_filenames() in update.php for details )
	 */
	function remove_spaces_from_system_filenames()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::remove_spaces_from_system_filenames();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
			echo "<script>window.parent.SqueezeBox.close();
				  window.parent.location.reload();</script>";
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Update file sizes for all attachments
	 * (See AttachmentsUpdate::update_file_sizes() in update.php for details )
	 */
	function update_file_sizes()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::update_file_sizes();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
			echo "<script>window.parent.SqueezeBox.close();
				  window.parent.location.reload();</script>";
			}
		else {
		$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Check all files in any attachments
	 * (See AttachmentsUpdate::check_files() in update.php for details )
	 */
	function check_files()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::check_files_existance();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
			echo "<script>window.parent.SqueezeBox.close();
				  window.parent.location.reload();</script>";
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}

	/**
	 * Validate all URLS in any attachments
	 * (See AttachmentsUpdate::validate_urls() in update.php for details )
	 */
	function validate_urls()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::validate_urls();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
			echo "<script>window.parent.SqueezeBox.close();
				  window.parent.location.reload();</script>";
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}

	/**
	 * Test a URL
	 */
	function testURL($url, $relative=false)
	{
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

		$r =& AttachmentsHelper::parse_url($url, $relative);
		if ( $r->error ) {
			echo "BAD URL($url) ERR({$r->error_code}) <br />";
		echo " ---> {$r->error_msg} <br />&nbsp;<br />";
			}
		else {
			echo "OK  URL($url) PROT({$r->protocol}) DOM({$r->domain}) PORT({$r->port}) PATH({$r->path}) PARM({$r->params}) URL({$r->url}) <br />&nbsp;<br />";
			}
	}


	/**
	 * Test function
	 */
	function test()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$fnames = Array(
			'C:\htdocs\attachments\001_file.jpg',
			'D:\www\htdocs\test\attachments\file.jpg',
			'D:\www\htdocs\test\attachments\article\1\file.jpg',
			'/var/www/htdocs/test/attachments/001_file.jpg',
			'/var/www/htdocs/attachments/file.jpg',
			'/var/www/htdocs/joomla/attachments/article/1/file.jpg'
			);

		foreach ($fnames as $file) {
			$finfo = AttachmentsUpdate::checkFilename($file);
			echo "--- Checking $file <br/>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Type: ";
			echo $finfo->winfile ? "Windows (" : "Linux (";
			echo $finfo->oldstyle ? "oldstyle) <br/>" : "newstyle) <br/>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; basename: " . $finfo->basename . " (extension: '" . $finfo->extension . "')<br/>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Rel file: " . $finfo->relfile . "<br/>";
			if ( $finfo->oldstyle AND $finfo->prefix ) {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Prefixed file, non-prefix filename: " . $finfo->basename_no_prefix . "<br/>";
				}
			else {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Nonprefixed file<br/>";
				}
			echo "<br/>";
			}

		/*
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();

		$entity_info =& $apm->getInstalledEntityInfo();

		foreach ($entity_info as $einfo) {
			echo 'E: ' . $einfo['id'] . ", " . $einfo['id_canonical'] . ", " .
				$einfo['name'] . ", " . $einfo['name_plural'] . "<br />";
			}

		AttachmentsControllerSpecial::testURL('http://google.com');
		AttachmentsControllerSpecial::testURL('http://jmcameron.net');
		AttachmentsControllerSpecial::testURL('http://jmcameron.net/rtm/robots.txt');
		AttachmentsControllerSpecial::testURL('https://jmcameron.net/rtm/robots.txt');
		AttachmentsControllerSpecial::testURL('http://localhost/test');
		AttachmentsControllerSpecial::testURL('http://localhost/test/');
		AttachmentsControllerSpecial::testURL('http://localhost/test/test.php');
		AttachmentsControllerSpecial::testURL('http://localhost/test/test.php#myanchor');
		AttachmentsControllerSpecial::testURL('http://localhost/test/test.txt');
		AttachmentsControllerSpecial::testURL('http://localhost/test/test.html?id=2');
		AttachmentsControllerSpecial::testURL('http://localhost/test/bad.txt');
		AttachmentsControllerSpecial::testURL('http://localhost/test/skype-mute.png');
		AttachmentsControllerSpecial::testURL('http://localhost/test/CMN_2007_Meeting_Summary.pdf');
		AttachmentsControllerSpecial::testURL('http://localhost/test/joomla-multi-install-2009-03-21.tar.gz');
		AttachmentsControllerSpecial::testURL('http://localhost/test/joe');

		AttachmentsControllerSpecial::testURL('ftp://jmcameron.net');
		AttachmentsControllerSpecial::testURL('git://jmcameron.net');
		AttachmentsControllerSpecial::testURL('svn://jmcameron.net');

		AttachmentsControllerSpecial::testURL('jmcameron.net');
		AttachmentsControllerSpecial::testURL('jmcameron.net/hello');
		AttachmentsControllerSpecial::testURL('jmcameron.net/rtm/robots.txt');

		AttachmentsControllerSpecial::testURL('index.php', true);

		AttachmentsControllerSpecial::testURL('/hello', true);
		*/

		exit();
	}

}

?>
