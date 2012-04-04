<?php

/**
 * Библиотека настроек
 * @author Michail1982
 */
Class Settings
{

	/**
	 * Объект драйвера настроек
	 * @var object
	 */
	private $_driver;

	/**
	 * Название модуля
	 * @var string
	 */
	private $_module = NULL;

	/**
	 * Загруженные настройки
	 * @var array
	 */
	private $_config = array();

	/**
	 * Конструктор
	 * @param Array $params
	 */
	function __construct($params = array())
	{
		$CI = & get_instance();

		$module = ( method_exists($CI->router, 'fetch_module') && $CI->router->fetch_module() != '' ) ? $CI->router->fetch_module() : NULL;

		$driver = isset($params['driver']) ? ucfirst($params['driver']) : 'Database';

		$dsn = isset($params['dsn']) ? $params['dsn'] : 'settings';

		// Загрузка драйвера
		$CI->load->file(dirname(__FILE__) .  DIRECTORY_SEPARATOR . 'Settings' .  DIRECTORY_SEPARATOR . $driver . EXT);

		$driver_class = 'Settings_' . $driver;

		$this->_driver = new $driver_class($dsn);

		if ( ! is_null($module))
		{
			//Загрузка настроек приложения
			$this->driver->load();
		}

		//Загрузка настроек модуля или приложения
		$config = $this->_driver->load($module, TRUE);

		// Добавление настроек в Config
		foreach ($config as $item => $value)
		{
			$CI->config->set_item($item, $value);
		}

		$this->_module = $module;

		$this->_config = $config;
	}

	/**
	 * Добавление ключа в настройки
	 * @param string $item
	 * @param any $value
	 * @return boolean
	 */
	public function add_item($item, $value = NULL)
	{
		get_instance()->config->set_item($item, $value);
		return $this->_driver->add_item($item, $value, $this->_module);
	}

	/**
	 * Удаление ключа из настроек
	 * @param string $item
	 */
	public function delete_item($item)
	{
		get_instance()->config->set_item($item, FALSE);
		return $this->_driver->delete_item($item, $this->_module);
	}

	/**
	 * Деструктор
	 */
	public function __destruct()
	{
		$CI = & get_instance();
		// Поиск изменённіх ключей
		if (sizeof($diff = array_keys(array_diff_assoc($this->_config, $CI->config->config))))
		{
			foreach ($diff as $key)
			{
				// Обновление настроек
				$this->_driver->update_item($key,$CI->config->item($key), $this->_module);
			}
		}
	}
}

/**
 * Интерфейс драйвера конфигурации
 * @author Michail1982
 *
 */
Interface iSettings
{
	/**
	 * Загрузка конфигурации
	 * @param string $module
	 * @param boolean $save
	 * @return array
	 */
	public function load($module = NULL, $save = FALSE);

	/**
	 * Добавление ключа в настройки
	 * @param string $item
	 * @param ambigous $value
	 * @param string $module
	 * @return boolean
	 */
	public function add_item($item, $value = NULL, $module = NULL);

	/**
	 * Обновление ключа настроек
	 * @param string $item
	 * @param ambigous $value
	 * @param string $module
	 * @return boolean
	 */
	public function update_item($item, $value = NULL, $module = NULL);

	/**
	 * Удаление ключа настроек
	 * @param string $item
	 * @param string $module
	 * @return boolean
	 */
	public function delete_item($item, $module = NULL);

}
