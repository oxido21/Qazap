<?php

/**
 * @version     1.0.0
 * @package     com_qazap
 * @copyright   Copyright (C) 2013 VirtuePlanet Services LLP. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      VirtuePlanet Services LLP <info@virtueplanet.com> - http://www.virtueplanet.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * vendorfield Table class
 */
class QazapTablevendorfield extends JTable 
{
	/**
	* Constructor
	*
	* @param JDatabase A database connector object
	*/
	public function __construct(&$db) 
	{
		parent::__construct('#__qazap_vendorfields', 'id', $db);
	}

	/**
	* Overloaded bind function to pre-process the params.
	*
	* @param	array		Named array
	* @return	null|string	null is operation was satisfactory, otherwise returns an error
	* @see		JTable:bind
	* @since	1.5
	*/
	public function bind($array, $ignore = '') 
	{
		$input = JFactory::getApplication()->input;
		$task = $input->getString('task', '');

		if(($task == 'save' || $task == 'apply') && (!JFactory::getUser()->authorise('core.edit.state','com_qazap') && $array['state'] == 1))
		{
			$array['state'] = 0;
		}

		if (isset($array['params']) && is_array($array['params'])) 
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata'])) 
		{
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if(!JFactory::getUser()->authorise('core.admin', 'com_qazap.vendorfield.'.$array['id']))
		{
			$actions = JFactory::getACL()->getActions('com_qazap','vendorfield');
			$default_actions = JFactory::getACL()->getAssetRules('com_qazap.vendorfield.'.$array['id'])->getData();
			$array_jaccess = array();
			foreach($actions as $action)
			{
				$array_jaccess[$action->name] = $default_actions[$action->name];
			}
			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}
		//Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules'])) 
		{
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	* This function convert an array of JAccessRule objects into an rules array.
	* @param type $jaccessrules an arrao of JAccessRule objects.
	*/
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();
		foreach($jaccessrules as $action => $jaccess)
		{
			$actions = array();
			foreach($jaccess->getData() as $group => $allow)
			{
				$actions[$group] = ((bool)$allow);
			}
			$rules[$action] = $actions;
		}
		return $rules;
	}

	/**
	* Overloaded check function
	*/
	public function check() 
	{
		//If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0) 
		{
			$this->ordering = self::getNextOrder();
		}
		return parent::check();
	}

	/**
	* Method to set the publishing state for a row or list of rows in the database
	* table.  The method respects checked out rows by other users and will attempt
	* to checkin rows that it can after adjustments are made.
	*
	* @param    mixed    An optional array of primary key values to update.  If not
	*                    set the instance property value is used.
	* @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
	* @param    integer The user id of the user performing the operation.
	* @return    boolean    True on success.
	* @since    1.0.4
	*/
	public function publish($pks = null, $state = 1, $userId = 0) 
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks)) 
		{
			if ($this->$k) 
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else 
			{
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) 
		{
			$checkin = '';
		} 
		else 
		{
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
		'UPDATE `' . $this->_tbl . '`' .
		' SET `state` = ' . (int) $state .
		' WHERE (' . $where . ')' .
		$checkin
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) 
		{
		$this->setError($this->_db->getErrorMsg());
		return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows())) 
		{
			// Checkin each row.
			foreach ($pks as $pk) 
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) 
		{
			$this->state = $state;
		}

		$this->setError('');
		return true;
	}

	/**
	* Define a namespaced asset name for inclusion in the #__assets table
	* @return string The asset name 
	*
	* @see JTable::_getAssetName 
	*/
	protected function _getAssetName() 
	{
		$k = $this->_tbl_key;
		return 'com_qazap.vendorfield.' . (int) $this->$k;
	}

	/**
	* 
	* Check Whether a state or country field exist or not 
	* 
	*/
	protected function checkType($fieldtype)
	{
		if($fieldtype == "country" || $fieldtype == "state")
		{
			$db = JFactory::getDBO();
			$sql = $db->getQuery(true)
			->select(count('id'))
			->from('`#__qazap_vendorfields`')
			->where('field_type = '.$db->quote($fieldtype));
			$db->setQuery($sql);
			$result = $db->loadResult();
			$fieldName = explode(':',$fieldtype);
			if($result >0)
			{
				$this->setError(ucfirst($fieldName[1]).' field already exists');
				return false;
			}	
		}	
		return true;
	}
	
	protected function checkTitle($fieldtype,$fieldname)
	{
		$db = JFactory::getDBO();
		$sql = $db->getQuery(true)
					->select($fieldname)
					->from('#__qazap_vendor');
		try 
		{
			$db->setQuery($sql);
			$result = $db->loadResult();
		} 
		catch (Exception $e) 
		{
			$query = $db->getQuery(true)
						->select('COUNT(id)')
						->from('#__qazap_vendorfields')
						->where('field_name = '.$db->quote($fieldname));
			$db->setQuery($query);
			$count = $db->loadResult();
			if($count)
			{
				$this->setError(JText::_('COM_QAZAP_FIELD_ALREADY_EXIST'));
				return false;
			}

			return true;
		}
		$this->setError(JText::_('COM_QAZAP_FIELD_ALREADY_EXIST'));
		return false;
	}
	/**
	* Method to store a node in the database table.
	*
	* @param   boolean  $updateNulls  True to update null values as well.
	*
	* @return  boolean  True on success.
	*
	* @link    http://docs.joomla.org/JTableNested/store
	* @since   11.1
	*/
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		if ($this->id)
		{
			// Existing item
			$this->modified_time		= $date->toSql();
			$this->modified_by			= $user->get('id');
		}
		else
		{
			// New contact. A contact created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created_time)
			{
				$this->created_time = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}	
		}		

		return parent::store($updateNulls);
	}
}
