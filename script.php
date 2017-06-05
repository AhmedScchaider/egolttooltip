<?php
/**
 * @package   	Egolt Tooltip
 * @link 		http://www.egolt.com
 * @copyright 	Copyright (C) Egolt - www.egolt.com
 * @author    	Soheil Novinfard
 * @license    	GNU/GPL 2
 *
 * Name:		Egolt Tooltip
 * License:		GNU/GPL 2
 * Product:		http://www.egolt.com/products/egolttooltip
 */

// Check Joomla! Library and direct access
defined('_JEXEC') or die('Direct access denied!');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
 
class pkg_EgoltTooltipInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	// function install($parent) 
	// {
		// $parent is the class calling this method
		// $parent->getParent()->setRedirectURL('index.php?option=com_egolttooltip&task=about');
	// }
	
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		$option = 'pkg_egolttooltip';
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where($query->qn('type') . ' = ' . $db->quote('package'));
		$query->where($query->qn('element') . ' = ' . $db->quote($option));
		$db->setQuery($query);
		$row = $db->loadObject();
		// $info	= json_decode($res);
		
		$id = $row->extension_id;
		
		$manifestFile = JPATH_MANIFESTS . '/packages/' . $row->element . '.xml';
		
		if(version_compare(JVERSION, '3.0', 'ge'))
			$manifest = new JInstallerManifestPackage($manifestFile);
		else
			$manifest = new JPackageManifest($manifestFile);
			
		$extension_root = JPATH_MANIFESTS . '/packages/' . $manifest->packagename;

		// Because packages may not have their own folders we cannot use the standard method of finding an installation manifest
		if (!file_exists($manifestFile))
		{
			// TODO: Fail?
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MISSINGMANIFEST'));
			return false;

		}

		$xml = JFactory::getXML($manifestFile);

		// If we cannot load the XML file return false
		if (!$xml)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_LOAD_MANIFEST'));
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 * @todo: Remove backwards compatibility in a future version
		 * Should be 'extension', but for backward compatibility we will accept 'install'.
		 */
		if ($xml->getName() != 'install' && $xml->getName() != 'extension')
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_INVALID_MANIFEST'));
			return false;
		}

		$error = false;
		foreach ($manifest->filelist as $extension)
		{
			// die(var_dump($extension));
			$tmpInstaller = new JInstaller;
			$id = $this->getExtID($extension->type, $extension->id, $extension->client, $extension->group);
			$client = JApplicationHelper::getClientInfo($extension->client, true);
			if ($id)
			{
				if($extension->type == 'library')
					continue;
				// if(($extension->type == 'plugin') and ($extension->id == 'egotrigger'))
					// continue;
				if(($extension->type == 'plugin') and ($extension->id == 'egoltupdate'))
					continue;
				if (!$tmpInstaller->uninstall($extension->type, $id, $client->id))
				{
					$error = true;
					JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_NOT_PROPER', basename($extension->filename)));
				}
			}
			else
			{
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_UNKNOWN_EXTENSION'));
			}
		}

		// Remove any language files
		jimport('joomla.base.adapterinstance');
		JInstaller::removeFiles($xml->languages);

		// clean up manifest file after we're done if there were no errors
		if (!$error)
		{
			JFile::delete($manifestFile);
			$folder = $extension_root;
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
			$exttable = JTable::getInstance('extension');
			$exttable->load($row->extension_id);
			$exttable->delete();
		}
		else
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MANIFEST_NOT_REMOVED'));
		}
		
		
		$app = JFactory::getApplication();
		$app->redirect(JRoute::_('index.php?option=com_installer'), 'Egolt Tooltip Uninstalled Successfully!');

	}
	
	public function getExtID($type, $id, $client, $group) 
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id');
		$query->from('#__extensions');
		$query->where('type = ' . $db->Quote($type));
		$query->where('element = ' . $db->Quote($id));

		switch ($type)
		{
			case 'plugin':
				// Plugins have a folder but not a client
				$query->where('folder = ' . $db->Quote($group));
				break;

			case 'library':
			case 'package':
			case 'component':
				// Components, packages and libraries don't have a folder or client.
				// Included for completeness.
				break;

			case 'language':
			case 'module':
			case 'template':
				// Languages, modules and templates have a client but not a folder
				$client = JApplicationHelper::getClientInfo($client, true);
				$query->where('client_id = ' . (int) $client->id);
				break;
		}

		$db->setQuery($query);
		$result = $db->loadResult();

		// Note: For templates, libraries and packages their unique name is their key.
		// This means they come out the same way they came in.
		return $result;
		
	}
	
	/**
	 * Joomla! pre-flight event
	 * 
	 * @param string $type Installation type (install, update, discover_install)
	 * @param JInstaller $parent Parent object
	 */
	// public function preflight($type, $parent)
	// {
		// Create Cache Folder
		// $f = JPATH_ROOT.'/cache/egolttooltip';
		// if(!JFolder::exists($f))
		// {
			// JFolder::create($f);
		// }
		
	// }
	
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
		if ( ($type == 'install') or ($type == 'update') ) {
			
			// Enable Egolt Update
			$plugin = 'egoltupdate';
			$folder = 'system';
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->update($db->qn('#__extensions'))
						->set($db->qn('enabled').' = '.$db->q('1'))
						->where($db->qn('element').' = '.$db->q($plugin))
						->where($db->qn('folder').' = '.$db->q($folder));
			$db->setQuery($query);
			$db->execute();
			
			$egpublic = "##EGEGPUB##";
			$egprivate = "##EGEGPRI##";
			$content = "<?php\r\ndefined('_JEXEC') or die('Direct access denied!');\r\n\$public='".$egpublic."';\r\n\$private='".$egprivate."';";
			JFile::write(JPATH_ROOT.'/plugins/system/egoltupdate/lic.php' ,$content);
			
		}
	}
}
