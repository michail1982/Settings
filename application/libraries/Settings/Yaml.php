<?php

/**
 * Драйвер YAML библиотеки Settings
 * @author Michail1982
 */

class Settings_Yaml implements iSettings
{
	/**
	 * Имя файла настроек
	 * @var string
	 */
	private $_dsn;

	/**
	 * Загруженные настройки
	 * @var array
	 */
	private $_config = array();

	/**
	 * Флаг изменения настроек
	 * @var boolean
	 */
	private $_changed = FALSE;

	/**
	 * Конструктор
	 * @param string $dsn
	 */
	public function __construct($dsn)
	{
		$this->_dsn = $dsn;

		if( ! class_exists('sfYaml'))
		{
			// Подключение Symfony YAML
			require_once APPPATH . 'libraries/Yaml/sfYaml.php';
		}
	}

	/* (non-PHPdoc)
	 * @see iSettings::load()
	 */
	public function load($module = NULL, $save = FALSE)
	{
		$dsn_path = $this->_get_dsn_path($module);

		$config = sfYaml::load($dsn_path);
		$config = ($dsn_path == $config) ? array() : $config;
		if ($save)
		{
			$this->_config = $config;
		}
		return $config;
	}

	/* (non-PHPdoc)
	 * @see iSettings::add_item()
	 */
	public function add_item($item, $value=NULL , $module = NULL)
	{
		$this->_changed = TRUE;
		$this->_config[$item] = $value;
	}

	/* (non-PHPdoc)
	 * @see iSettings::update_item()
	 */
	public function update_item($item, $value=NULL , $module = NULL)
	{
		$this->_changed = TRUE;
		$this->_config[$item] = $value;
	}
	/* (non-PHPdoc)
	 * @see iSettings::delete_item()
	*/

	public function delete_item($item, $module = NULL)
	{
		if (array_key_exists($item, $this->_config))
		{
			$this->_changed = TRUE;
			unset($this->_config[$item]);
		}
		return TRUE;
	}

	/**
	 * Вычисление пути к файлу настроек
	 * @param string $module
	 * @return string
	 */
	private function _get_dsn_path($module = NULL)
	{
		return APPPATH . (is_null($module) ? '' : ('modules/' . $module . '/') ) . 'config/' . $this->_dsn . '.yml';
	}

	/**
	 * Деструктор
	 */
	public function __destruct()
	{
		if($this->_changed)
		{
			$CI = & get_instance();

			$module = ( method_exists($CI->router, 'fetch_module') && $CI->router->fetch_module() != '' ) ? $CI->router->fetch_module() : NULL;

			$dsn_path = $this->_get_dsn_path($module);

			file_put_contents($dsn_path, sfYaml::dump($this->_config));
		}
	}
}