<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects



/**
 * A class for attachments permissions functions
 *
 * @package Attachments
 */
class AttachmentsPermissions
{
    /**
     * Get the actions
     *
     * @return an array of which actions are permitted for this user
     */
    public static function getActions($user_id = null)
    {
        if ($user_id) {
            $user = Factory::getContainer()
                ->get(UserFactoryInterface::class)
                ->loadUserById($user_id);
        } else {
            $user = Factory::getApplication()->getIdentity();
        }

        $result = new Registry();

        $assetName = 'com_attachments';

        $actions = array( 'core.admin',
                          'core.manage',
                          'core.create',
                          'core.delete',
                          'core.edit',
                          'core.edit.state',
                          'core.edit.own',
                          'attachments.edit.state.own',
                          'attachments.delete.own',
                          'attachments.edit.state.ownparent',
                          'attachments.delete.ownparent'
                         );

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }


    /**
     * Determine if a user can edit a specified category
     *
     * Partially based on allowEdit() in com_categories/controllers/category.php
     *
     * @param  integer $category_id the ID for the category to be tested
     * @param  integer $id  The id of the user to load (defaults to null)
     */
    public static function userMayEditCategory($category_id, $user_id = null)
    {
        if ($user_id) {
            $user = Factory::getContainer()
                ->get(UserFactoryInterface::class)
                ->loadUserById($user_id);
        } else {
            $user = Factory::getApplication()->getIdentity();
        }

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_content')) {
            return true;
        }

        // Check specific edit permission.
        if ($user->authorise('core.edit', 'com_content.category.' . $category_id)) {
            return true;
        }

        // No general permissions, see if 'edit own' is permitted for this category
        if (
            $user->authorise('core.edit.own', 'com_content') ||
             $user->authorise('core.edit.own', 'com_content.category.' . $category_id)
        ) {
            // Yes user can 'edit.own', Find out if the user created the category
            /** @var \Joomla\Database\DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('id')->from('#__categories');
            $query->where('id = ' . (int)$category_id . ' AND created_user_id = ' . (int)$user->id);
            $db->setQuery($query, 0, 1);
            try {
                $results = $db->loadObject();
            } catch (\Exception $e) {
                $errmsg = Text::_('ATTACH_ERROR_CHECKING_CATEGORY_OWNERSHIP') . ' (ERR 106)';
                throw new \Exception($errmsg, 500);
            }

            if (!empty($results)) {
                // The user did actually create the category
                return true;
            }
        }

        return false;
    }



    /**
     * Determine if a user can edit a specified article
     *
     * Partially based on allowEdit() in com_content/controllers/article.php
     *
     * @param  integer $article_id the ID for the article to be tested
     * @param  integer $id  The id of the user to load (defaults to null)
     */
    public static function userMayEditArticle($article_id, $user_id = null)
    {
        if ($user_id) {
            $user = Factory::getContainer()
                ->get(UserFactoryInterface::class)
                ->loadUserById($user_id);
        } else {
            $user = Factory::getApplication()->getIdentity();
        }

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_content')) {
            return true;
        }

        // Check specific edit permission.
        if ($user->authorise('core.edit', 'com_content.article.' . $article_id)) {
            return true;
        }

        // Check for article being created.
        // NOTE: we must presume that the article is being created by this user!
        if (((int)$article_id == 0) && $user->authorise('core.edit.own', 'com_content')) {
            return true;
        }

        // No general permissions, see if 'edit own' is permitted for this article
        if (
            $user->authorise('core.edit.own', 'com_content') ||
             $user->authorise('core.edit.own', 'com_content.article.' . $article_id)
        ) {
            // Yes user can 'edit.own', Find out if the user created the article
            /** @var \Joomla\Database\DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->select('id')->from('#__content');
            $query->where('id = ' . (int)$article_id . ' AND created_by = ' . (int)$user->id);
            $db->setQuery($query, 0, 1);
            try {
                $results = $db->loadObject();
            } catch (\Exception $e) {
                $errmsg = Text::_('ATTACH_ERROR_CHECKING_ARTICLE_OWNERSHIP') . ' (ERR 107)';
                throw new \Exception($errmsg, 500);
            }

            if (!empty($results)) {
                // The user did actually create the article
                return true;
            }
        }

        return false;
    }
}
