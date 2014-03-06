<?php

define('DS', DIRECTORY_SEPARATOR);

namespace PPF;

/**
 * PHP Project Framework
 *
 * @author Leon van der Veen <vdvleon@gmail.com>
 * @package ppf
 */
class PPF
{
	/**
	 * Instance.
	 *
	 * @var \PPF\PPF
	 */
	static protected $instance_ = null;

	/**
	 * Modules.
	 *
	 * @var array
	 */
	protected $modules_ = [];

	/**
	 * Namespace mode.
	 *
	 * @var bool
	 */
	protected $namespaces_ = true;

	/**
	 * Constructor.
	 *
	 * @param array $settings
	 */
	protected function __construct(array $settings)
	{
		foreach ($settings as $name => $arguments)
		{
			$arguments = (array) $arguments;
			$method = $name;

			if ( ! is_callable([$this, $method]) && count($arguments) == 1 && is_array($arguments[0]))
			{
				$method = self::camelize('set ' . $name);
			}
			if ( ! is_callable([$this, $method]))
			{
				$method = self::camelize('add ' . $name);
			}
			if ( ! is_callable([$this, $method]))
			{
				throw new \Exception('Wrong setting \'' . $method . '\'');
			}

			call_user_func_array([$this, $method], $arguments);
		}
	}

	/**
	 * Camelize.
	 *
	 * @param string $str
	 * @return string
	 */
	public static function camelize($str)
	{
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
		return substr(str_replace(' ', '', $str), 1);
	}

	/**
	 * Initialize a PHP Project using PPF.
	 *
	 * @param array $settings
	 */
	public static function init(array $settings = null)
	{
		return self::$instance_ = new self($settings);
	}

	/**
	 * Instance.
	 *
	 * @return \PPF\PFF
	 */
	public static function instance()
	{
		return self::$instance_;
	}

	/**
	 * Call static.
	 *
	 * @param string $name Method name.
	 * @param array $arguments Method arguments.
	 * @return mixed
	 */
	public static function __callStatic($name, array $arguments)
	{
		return call_user_func_array([self::instance(), $name], $arguments);
	}

	/**
	 * Modules.
	 *
	 * @param array|string $module
	 * @param bool $overwrite Overwrite modules instead of adding.
	 * @return array|\PPF\PPF List of modules in get mode, this pointer otherwise.
	 */
	public function modules($module = null, $overwrite = false)
	{
		// Getter?
		if ($module === null)
		{
			return $this->modules_;
		}

		// Overwrite?
		if ($overwrite)
		{
			$this->modules_ = [];
		}

		$modules = (array) $module;
		foreach ($modules as $module)
		{
			// Add module
			$module = (string) $module;
			if (strpos($module, DS) === false)
			{
				$module = rtrim($_SERVER['DOCUMENT_ROOT'], DS) . DS . $module;
			}
			$module = rtrim(realpath($module), DS) . DS;
			$this->modules_[] = $module;

			// Load module, if needed
			if (file_exists($module . 'init.php'))
			{
				require_once($module . 'init.php');
			}
		}

		return $this;
	}

	/**
	 * Enable autoloader.
	 *
	 * @param bool $enable
	 * @return \PPF\PPF This.
	 */
	public function enableAutoloader($enable = true)
	{
		if ($enable)
		{
			spl_autoload_register([$this, 'autoload']);
		}
		else
		{
			spl_autoload_unregister([$this, 'autoload']);
		}
		return $this;
	}

	/**
	 * Namespace support.
	 *
	 * @param bool $enable
	 * @return \PPF\PPF This.
	 */
	public function namespaces($enable = true)
	{
		$this->namespaces_ = (bool) $enable;
	}

	/**
	 * Find path.
	 *
	 * @param string $file
	 * @param string $directory
	 * @param string $extension
	 * @param bool $all
	 * @return string|null|array
	 */
	public function findPath($file, $directory = null, $extension = null, $all = false)
	{
		$file = trim($file, DS);
		if ($directory !== null)
		{
			$file = trim($directory, DS) . DS . $file;
		}
		if ( ! empty($extension))
		{
			$file .= '.' . $extension;
		}

		$result = [];
		foreach ($this->modules_ as $dir)
		{
			$path = $dir . $file;

			if (file_exists($path))
			{
				if ($all)
				{
					$result[] = $path;
				}
				else
				{
					return $path;
				}
			}
		}

		if ($all)
		{
			return $result;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Autoload.
	 *
	 * @param string $class
	 */
	public function autoload($class)
	{
		$separator = $this->namespaces_ ? '\\' : '_';

		$path = $this->findPath(
			trim(str_replace(DS . DS, DS, str_replace($separator, DS, $class)), DS),
			'classes',
			'php'
		);

		if ($path !== null)
		{
			require_once($path);
		}
	}
};
