<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\View\System;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Updater\Updater;

/**
 * HTML View class for the Cpanel component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	private static $notEmpty = false;
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_CPANEL_TITLE_SYSTEM_PANEL'), 'cog help_header');
		ToolbarHelper::help('screen.cpanel');

		$app         = Factory::getApplication();
		$user        = Factory::getUser();
		$links       = [];
		$headerIcons = [];

		// Build the array of links

		// System
		if ($user->authorise('core.admin'))
		{
			$links['COM_CPANEL_SYSTEM_SETUP'] = [
				// System configuration
				'com_config' => static::arrayBuilder(
					'MOD_MENU_CONFIGURATION',
					'',
					'index.php?option=com_config',
					'cog'
				),
			];

			$headerIcons['COM_CPANEL_SYSTEM_SETUP'] = 'cog';

			static::$notEmpty = true;
		}

		// Maintain
		if ($user->authorise('core.manage', 'com_cache'))
		{
			$links['MOD_MENU_MAINTAIN'] = [
				'com_cache' => static::arrayBuilder(
					'MOD_MENU_CLEAR_CACHE',
					'',
					'index.php?option=com_cache',
					'trash'
				),
				'com_cache_purge' => static::arrayBuilder(
					'MOD_MENU_PURGE_EXPIRED_CACHE',
					'',
					'index.php?option=com_cache&view=purge',
					'trash'
				),
			];

			$headerIcons['MOD_MENU_MAINTAIN'] = 'refresh';

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_checkin'))
		{
			$checkinModel = $app->bootComponent('com_checkin')->getMVCFactory()->createModel('Checkin', 'Administrator', ['ignore_request' => true]);
			$checkins     = count($checkinModel->getItems());

			$new = [
				'com_checkin' => static::arrayBuilder(
					'MOD_MENU_GLOBAL_CHECKIN',
					$checkins,
					'index.php?option=com_checkin',
					'refresh'
				),
			];

			if (!empty($links['MOD_MENU_MAINTAIN']))
			{
				$links['MOD_MENU_MAINTAIN'] = array_merge($links['MOD_MENU_MAINTAIN'], $new);
			}
			else
			{
				$links['MOD_MENU_MAINTAIN'] = $new;

				$headerIcons['MOD_MENU_MAINTAIN'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		// Information
		if ($user->authorise('core.manage', 'com_installer'))
		{
			$warningsModel   = $app->bootComponent('com_installer')->getMVCFactory()->createModel('Warnings', 'Administrator', ['ignore_request' => true]);
			$warningMessages = count($warningsModel->getItems());

			$links['MOD_MENU_INFORMATION'] = [
				'com_installer_warnings' => static::arrayBuilder(
					'MOD_MENU_INFORMATION_WARNINGS',
					$warningMessages,
					'index.php?option=com_installer&view=warnings',
					'refresh'
				),
			];

			$headerIcons['MOD_MENU_INFORMATION'] = 'refresh';

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_postinstall'))
		{
			$messagesModel = $app->bootComponent('com_postinstall')->getMVCFactory()->createModel('Messages', 'Administrator', ['ignore_request' => true]);
			$messages      = count($messagesModel->getItems());

			$new = [
				'com_postinstall' => static::arrayBuilder(
					'MOD_MENU_INFORMATION_POST_INSTALL_MESSAGES',
					$messages,
					'index.php?option=com_postinstall',
					'info'
				),
			];

			if (!empty($links['MOD_MENU_INFORMATION']))
			{
				$links['MOD_MENU_INFORMATION'] = array_merge($links['MOD_MENU_INFORMATION'], $new);
			}
			else
			{
				$links['MOD_MENU_INFORMATION'] = $new;

				$headerIcons['MOD_MENU_INFORMATION'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		if ($user->authorise('core.admin'))
		{
			$new = [
				'com_admin_sysinfo' => static::arrayBuilder(
					'MOD_MENU_SYSTEM_INFORMATION_SYSINFO',
					'',
					'index.php?option=com_admin&view=sysinfo',
					'info'
				),
			];

			if (!empty($links['MOD_MENU_INFORMATION']))
			{
				$links['MOD_MENU_INFORMATION'] = array_merge($links['MOD_MENU_INFORMATION'], $new);
			}
			else
			{
				$links['MOD_MENU_INFORMATION'] = $new;

				$headerIcons['MOD_MENU_INFORMATION'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			$databaseModel  = $app->bootComponent('com_installer')->getMVCFactory()->createModel('Database', 'Administrator', ['ignore_request' => true]);
			$changeSet      = $databaseModel->getItems();
			$changeSetCount = 0;

			foreach ($changeSet as $item)
			{
				$changeSetCount += $item['errorsCount'];
			}

			$new = [
				'com_installer_database' => static::arrayBuilder(
					'MOD_MENU_SYSTEM_INFORMATION_DATABASE',
					$changeSetCount,
					'index.php?option=com_installer&view=database',
					'refresh'
				),
			];

			if (!empty($links['MOD_MENU_INFORMATION']))
			{
				$links['MOD_MENU_INFORMATION'] = array_merge($links['MOD_MENU_INFORMATION'], $new);
			}
			else
			{
				$links['MOD_MENU_INFORMATION'] = $new;

				$headerIcons['MOD_MENU_INFORMATION'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		// Install
		if ($user->authorise('core.manage', 'com_installer'))
		{
			$discoverModel = $app->bootComponent('com_installer')->getMVCFactory()->createModel('Discover', 'Administrator', ['ignore_request' => true]);
			$discoverModel->discover();
			$discoveredExtensions = count($discoverModel->getItems());

			// Install
			$links['MOD_MENU_INSTALL'] = [
				'com_installer_install' => static::arrayBuilder(
					'MOD_MENU_INSTALL_EXTENSIONS',
					'',
					'index.php?option=com_installer&view=install',
					'cog'
				),
				'com_installer_discover' => static::arrayBuilder(
					'MOD_MENU_INSTALL_DISCOVER',
					$discoveredExtensions,
					'index.php?option=com_installer&view=discover',
					'cog'
				),
				'com_languages_install' => static::arrayBuilder(
					'MOD_MENU_INSTALL_LANGUAGES',
					'',
					'index.php?option=com_installer&view=languages',
					'cog'
				),
			];

			$headerIcons['MOD_MENU_INSTALL'] = 'download';

			static::$notEmpty = true;
		}

		// Manage
		if ($user->authorise('core.manage', 'com_installer'))
		{
			$links['MOD_MENU_MANAGE'] = [
				'com_installer_manage' => static::arrayBuilder(
					'MOD_MENU_MANAGE_EXTENSIONS',
					'',
					'index.php?option=com_installer&view=manage',
					'cog'
				),
			];

			$headerIcons['MOD_MENU_MANAGE'] = 'refresh';

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_languages'))
		{
			$new = [
				'com_languages_installed' => static::arrayBuilder(
					'MOD_MENU_MANAGE_LANGUAGES',
					'',
					'index.php?option=com_languages&view=installed',
					'cog'
				),
				'com_languages_content' => static::arrayBuilder(
					'MOD_MENU_MANAGE_LANGUAGES_CONTENT',
					'',
					'index.php?option=com_languages&view=languages',
					'cog'
				),
				'com_languages_overrides' => static::arrayBuilder(
					'MOD_MENU_MANAGE_LANGUAGES_OVERRIDES',
					'',
					'index.php?option=com_languages&view=overrides',
					'cog'
				),
			];

			if (!empty($links['MOD_MENU_MANAGE']))
			{
				$links['MOD_MENU_MANAGE'] = array_merge($links['MOD_MENU_MANAGE'], $new);
			}
			else
			{
				$links['MOD_MENU_MANAGE'] = $new;

				$headerIcons['MOD_MENU_MANAGE'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_csp'))
		{
			$new = [
				'com_csp_main' => static::arrayBuilder(
					'MOD_MENU_MANAGE_CSP',
					'',
					'index.php?option=com_csp',
					'cog'
				),
			];

			if (!empty($links['MOD_MENU_MANAGE']))
			{
				$links['MOD_MENU_MANAGE'] = array_merge($links['MOD_MENU_MANAGE'], $new);
			}
			else
			{
				$links['MOD_MENU_MANAGE'] = $new;

				$headerIcons['MOD_MENU_MANAGE'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_plugins'))
		{
			$new = [
				'com_plugins' => static::arrayBuilder(
					'MOD_MENU_MANAGE_PLUGINS',
					'',
					'index.php?option=com_plugins',
					'cog'
				),
			];

			if (!empty($links['MOD_MENU_MANAGE']))
			{
				$links['MOD_MENU_MANAGE'] = array_merge($links['MOD_MENU_MANAGE'], $new);
			}
			else
			{
				$links['MOD_MENU_MANAGE'] = $new;

				$headerIcons['MOD_MENU_MANAGE'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_redirect'))
		{
			$new = [
				'com_redirect' => static::arrayBuilder(
					'MOD_MENU_MANAGE_REDIRECTS',
					'',
					'index.php?option=com_redirect',
					'cog'
				),
			];

			if (!empty($links['MOD_MENU_MANAGE']))
			{
				$links['MOD_MENU_MANAGE'] = array_merge($links['MOD_MENU_MANAGE'], $new);
			}
			else
			{
				$links['MOD_MENU_MANAGE'] = $new;

				$headerIcons['MOD_MENU_MANAGE'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_modules'))
		{
			$new = [
				'com_modules' => static::arrayBuilder(
					'MOD_MENU_EXTENSIONS_MODULE_MANAGER_SITE',
					'',
					'index.php?option=com_modules&view=modules&client_id=0',
					'cog'
				),
				'com_modules_edit' => static::arrayBuilder(
					'MOD_MENU_EXTENSIONS_MODULE_MANAGER_ADMINISTRATOR',
					'',
					'index.php?option=com_modules&view=modules&client_id=1',
					'cog'
				),
			];

			if (!empty($links['MOD_MENU_MANAGE']))
			{
				$links['MOD_MENU_MANAGE'] = array_merge($links['MOD_MENU_MANAGE'], $new);
			}
			else
			{
				$links['MOD_MENU_MANAGE'] = $new;

				$headerIcons['MOD_MENU_MANAGE'] = 'refresh';
			}

			static::$notEmpty = true;
		}

		// Update
		if ($user->authorise('core.manage', 'com_joomlaupdate'))
		{
			$joomlaUpdateModel = $app->bootComponent('com_joomlaupdate')->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
			$joomlaUpdateModel->refreshUpdates(true);
			$joomlaUpdate      = $joomlaUpdateModel->getUpdateInformation();
			$hasUpdate         = $joomlaUpdate['hasUpdate'] ? $joomlaUpdate['latest'] : '';

			$links['MOD_MENU_UPDATE'] = [
				'com_joomlaupdate' => static::arrayBuilder(
					'MOD_MENU_UPDATE_JOOMLA',
					$hasUpdate,
					'index.php?option=com_joomlaupdate',
					'upload'
				),
			];

			$headerIcons['MOD_MENU_UPDATE'] = 'upload';

			static::$notEmpty = true;
		}

		if ($user->authorise('core.manage', 'com_installer'))
		{
			Updater::getInstance()->findUpdates();

			$updateModel     = $app->bootComponent('com_installer')->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);
			$extensionsCount = count($updateModel->getItems());

			$new = [
				'com_installer_extensions_update' => static::arrayBuilder(
					'MOD_MENU_UPDATE_EXTENSIONS',
					$extensionsCount,
					'index.php?option=com_installer&view=update',
					'upload'
				),
				'com_installer_update_sites' => static::arrayBuilder(
					'MOD_MENU_UPDATE_SOURCES',
					'',
					'index.php?option=com_installer&view=updatesites',
					'edit'
				),
			];

			if (!empty($links['MOD_MENU_UPDATE']))
			{
				$links['MOD_MENU_UPDATE'] = array_merge($links['MOD_MENU_UPDATE'], $new);
			}
			else
			{
				$links['MOD_MENU_UPDATE'] = $new;

				$headerIcons['MOD_MENU_UPDATE'] = 'upload';
			}

			static::$notEmpty = true;
		}

		// Templates
		if ($user->authorise('core.manage', 'com_templates'))
		{
			// Site
			$links['MOD_MENU_TEMPLATES'] = [
				'com_templates' => static::arrayBuilder(
					'MOD_MENU_TEMPLATE_SITE_TEMPLATES',
					'',
					'index.php?option=com_templates&view=templates&client_id=0',
					'edit'
				),
				'com_templates_site_styles' => static::arrayBuilder(
					'MOD_MENU_TEMPLATE_SITE_STYLES',
					'',
					'index.php?option=com_templates&view=styles&client_id=0',
					'image'
				),
				// Admin
				'com_templates_edit' => static::arrayBuilder(
					'MOD_MENU_TEMPLATE_ADMIN_TEMPLATES',
					'',
					'index.php?option=com_templates&view=templates&client_id=1',
					'edit'
				),
				'com_templates_admin_styles' => static::arrayBuilder(
					'MOD_MENU_TEMPLATE_ADMIN_STYLES',
					'',
					'index.php?option=com_templates&view=styles&client_id=1',
					'image'
				),
			];

			$headerIcons['MOD_MENU_TEMPLATES'] = 'image';

			static::$notEmpty = true;
		}

		// Access
		if ($user->authorise('core.manage', 'com_users'))
		{
			// Site
			$links['MOD_MENU_ACCESS'] = [
				'com_users_groups' => static::arrayBuilder(
					'MOD_MENU_ACCESS_GROUPS',
					'',
					'index.php?option=com_users&view=groups',
					'image'
				),
				'com_users_levels' => static::arrayBuilder(
					'MOD_MENU_ACCESS_LEVELS',
					'',
					'index.php?option=com_users&view=levels',
					'image'
				),
			];

			$headerIcons['MOD_MENU_ACCESS'] = 'lock';

			static::$notEmpty = true;
		}

		// Global Configuration - Permissions and Filters
		if ($user->authorise('core.admin'))
		{
			$new = [
				'com_config_permissions' => static::arrayBuilder(
					'MOD_MENU_ACCESS_SETTINGS',
					'',
					'index.php?option=com_config#page-permissions',
					'refresh'
				),
				'com_config_filters' => static::arrayBuilder(
					'MOD_MENU_ACCESS_TEXT_FILTERS',
					'',
					'index.php?option=com_config#page-filters',
					'refresh'
				),
			];

			if (!empty($links['MOD_MENU_ACCESS']))
			{
				$links['MOD_MENU_ACCESS'] = array_merge($links['MOD_MENU_ACCESS'], $new);
			}
			else
			{
				$links['MOD_MENU_ACCESS'] = $new;

				$headerIcons['MOD_MENU_ACCESS'] = 'lock';
			}

			static::$notEmpty = true;
		}

		if (static::$notEmpty)
		{
			$this->links = $links;
			$this->headerIcons = $headerIcons;
		}
		else
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		Factory::getLanguage()->load(
			'mod_menu',
			JPATH_ADMINISTRATOR,
			Factory::getLanguage()->getTag(),
			true
		);

		return parent::display($tpl);
	}

	/**
	 * Helper function to build an array for each link
	 *
	 * @param   string  $name   the name of the link
	 * @param   string  $badge  the information badge
	 * @param   string  $link   the url of the link
	 * @param   string  $icon   the name of the icon
	 *
	 * @return array
	 *
	 * @since 4.0.0
	 */
	private static function arrayBuilder($name, $badge, $link, $icon): array
	{
		return [
			'link'    => $link,
			'title'   => $name,
			'badge'   => $badge,
			'label'   => $name . '_LBL',
			'desc'    => $name . '_DESC',
			'icon'    => $icon
		];
	}
}