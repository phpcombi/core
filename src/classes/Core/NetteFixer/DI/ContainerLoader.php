<?php

namespace Combi\Core\NetteFixer\DI;

use Combi\{
    Helper as helper,
    Abort as abort,
    Core as core
};

use Nette\{DI, SmartObject};

class ContainerLoader
{
	use SmartObject;

	/**
	 * @var bool
	 */
	protected $auto_rebuild = false;

	/**
	 * @var string
	 */
	protected $tmp_dir;

	/**
	 * @var string|null
	 */
	protected $updated_meta = null;

	public function __construct(string $tmp_dir, bool $auto_rebuild = false)
	{
		$this->tmp_dir 		= $tmp_dir;
		$this->auto_rebuild = $auto_rebuild;
	}

	/**
 	 * callable define: function (Nette\DI\Compiler $compiler): string|null
	 *
	 * @param  callable $generator
	 * @param  string $pid
	 * @return string
	 */
	public function load(callable $generator, string $pid)
	{
		$class = $this->getClassName($pid);
		if (!class_exists($class, false)) {
			$this->loadFile($class, $generator);
		}
		return $class;
	}

	/**
	 * @param string $pid
	 * @return string
	 */
	public function getClassName(string $pid)
	{
		return "Container_{$pid}_".substr(md5($pid), 5, 5);
	}

	/**
	 * @param string $class
	 * @param callable $generator
	 * @return void
	 */
	protected function loadFile(string $class, callable $generator): void
	{
		$php_file 	= "$this->tmp_dir/$class.php";
		$meta_file	= "$php_file.meta";

		// 检查是否需要重建
		if (!$this->checkNeedRebuild($meta_file) && (include $php_file) !== false) {
			return;
		}

		// 目录构建
		if (!is_dir($this->tmp_dir)) {
			@mkdir($this->tmp_dir, 0755, true);
		}

		// 锁
		$handle = fopen("$php_file.lock", 'c+');
		if (!$handle || !flock($handle, LOCK_EX)) {
			throw new \RuntimeException("Unable to acquire exclusive lock on '$php_file.lock'.");
		}

		// 生成meta内容
		if ($this->updated_meta) {
			$to_write[$meta_file] = $updatedMeta;
		} else {
			[$to_write[$php_file], $to_write[$meta_file]] = $this->generate($class, $generator);
		}

		// 写文件
		foreach ($to_write as $filename => $content) {
			$this->writeFile($filename, $content);
		}

		// 再次载入
		if ((include $php_file) === false) { // @ - error escalated to exception
			throw new \RuntimeException("Unable to include '$php_file'.");
		}
		flock($handle, LOCK_UN);
	}

	protected function writeFile(string $filename, string $content) {
		if (file_put_contents("$filename.tmp", $content) !== strlen($content)
			|| !rename("$filename.tmp", $filename)) {

			@unlink("$filename.tmp"); // @ - file may not exist
			throw new \RuntimeException("Unable to create file '$filename'.");
		} elseif (function_exists('opcache_invalidate')) {
			@opcache_invalidate($filename, true); // @ can be restricted
		}
	}

	protected function checkNeedRebuild(string $meta_file): bool
	{
		if ($this->auto_rebuild) {
			if (!is_file($meta_file)) {
				return true;
			}

			$meta = core\Utils\Pack::decode('msgpack', file_get_contents($meta_file));
			$orig = $meta[2];
			return empty($meta[0])
				|| DI\DependencyChecker::isExpired(...$meta)
				|| ($orig !== $meta[2] && $this->updated_meta = core\Utils\Pack::encode('msgpack', $meta));
		}
		return false;
	}


	/**
	 * @return array of (code, file[])
	 */
	protected function generate(string $class, callable $generator): array
	{
		$compiler = new DI\Compiler;
		$compiler->setClassName($class);
		$code = $generator(...[&$compiler]) ?: $compiler->compile();
		return [
			"<?php\n$code",
			core\Utils\Pack::encode('msgpack', $compiler->exportDependencies())
		];
	}

}
