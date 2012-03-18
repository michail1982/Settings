<?php
/**
 * Драйвер баз данных библиотеки Settings
 * @author Michail1982
 */
class Settings_Database implements iSettings
{
	/**
	 * Имя таблицы настроек
	 * @var string
	 */
	private $_dsn;

	private $_item_keys = array();
	/**
	 * Конструктор
	 * @param string $dsn
	 */
	public function __construct($dsn)
	{
		$this->_dsn = $dsn;
	}

	/* (non-PHPdoc)
	 * @see iSettings::load()
	 */
	public function load($module = NULL, $save = FALSE)
	{
		$config  = get_instance()->db->get_where(
				$this->_dsn,
				array
				(
					'module' => $module
				)
		)->result_array();
		$items = array();
		foreach ($config as $item)
		{
			$items[$item['item']] = unserialize($item['value']);
		}
		if($save)
		{
			$this->_item_keys = array_keys($items);
		}
		return $items;
	}

	/* (non-PHPdoc)
	 * @see iSettings::add_item()
	 */
	public function add_item($item, $value=NULL , $module = NULL)
	{
		if (in_array($item, $this->_item_keys))
		{
			return $this->update_item($item, $value, $module);
		}

		return get_instance()->db->insert(
			$this->_dsn,
			array
			(
				'module' => $module,
				'item' => $item,
				'value' => serialize($value)
			)
		);
	}

	/* (non-PHPdoc)
	 * @see iSettings::update_item()
	 */
	public function update_item($item, $value=NULL , $module = NULL)
	{
		if ( ! in_array($item, $this->_item_keys))
		{
			return $this->add_item($item, $value, $module);
		}

		return get_instance()->db->update(
			$this->_dsn,
			array
			(
				'value' => serialize($value)
			),
			array
			(
				'module' => $module,
				'item' => $item
			)
		);
	}

	/* (non-PHPdoc)
	 * @see iSettings::delete_item()
	 */
	public function delete_item($item, $module = NULL)
	{
		if (in_array($item, $this->_item_keys))
		{
			return get_instance()->db->delete(
				$this->_dsn,
				array
				(
					'module' => $module,
					'item' => $item
				)
			);
		}
		return TRUE;
	}
}