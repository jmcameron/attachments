<?php
/**
 * Attachments component attachments view
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

?>
<tr>
<td colspan="<?php echo $this->num_columns-3; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
<td colspan="3"><div id="componentVersion"><a target="_blank" title="<?php echo JText::_('ATTACH_ATTACHMENTS_PROJECT_URL_DESCRIPTION'); ?>" href="<?php echo $this->project_url ?>"><?php echo JText::sprintf('ATTACH_ATTACHMENTS_VERSION_S', $this->version); ?></a></div></td>
</tr>
