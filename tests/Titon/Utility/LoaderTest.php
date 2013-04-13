<?php
/**
 * @copyright	Copyright 2010-2013, The Titon Project
 * @license		http://opensource.org/licenses/bsd-license.php
 * @link		http://titon.io
 */

namespace Titon\Utility;

use Titon\Test\TestCase;
use Titon\Utility\Loader;

/**
 * Test class for Titon\Utility\Loader.
 */
class LoaderTest extends TestCase {

	/**
	 * Test that the class name is returned without the namespace or extension.
	 */
	public function testBaseClass() {
		$this->assertEquals('ClassName', Loader::baseClass('\test\namespace\ClassName'));
		$this->assertEquals('ClassName', Loader::baseClass('\test\namespace\ClassName.ext'));

		$this->assertEquals('ClassName', Loader::baseClass('test:namespace:ClassName', ':'));
		$this->assertEquals('ClassName', Loader::baseClass('test:namespace:ClassName.ext', ':'));

		$this->assertEquals('ClassName', Loader::baseClass('test/namespace/ClassName', '/'));
		$this->assertEquals('ClassName', Loader::baseClass('test/namespace/ClassName.ext', '/'));

		$this->assertEquals('ClassName', Loader::baseClass('test.namespace.ClassName', '.'));
		$this->assertEquals('ext', Loader::baseClass('test.namespace.ClassName.ext', '.'));
	}

	/**
	 * Test that only the namespace package is returned when a fully qualified class name is returned.
	 */
	public function testBaseNamespace() {
		$this->assertEquals('test\namespace', Loader::baseNamespace('\test\namespace\ClassName'));
		$this->assertEquals('test\namespace', Loader::baseNamespace('\test\namespace\ClassName.ext'));

		$this->assertEquals('test\namespace', Loader::baseNamespace('/test/namespace/ClassName'));
		$this->assertEquals('test\namespace', Loader::baseNamespace('/test/namespace/ClassName.ext'));
	}

	/**
	 * Test that all slashes are converted to forward slashes (works for linux and windows).
	 */
	public function testDs() {
		// linux
		$this->assertEquals('/some/fake/folder/path/fileName.php', Loader::ds('/some/fake/folder/path/fileName.php'));
		$this->assertEquals('/some/fake/folder/path/fileName.php', Loader::ds('/some\fake/folder\path/fileName.php'));

		// windows
		$this->assertEquals('C:/some/fake/folder/path/fileName.php', Loader::ds('C:\some\fake\folder\path\fileName.php'));
		$this->assertEquals('C:/some/fake/folder/path/fileName.php', Loader::ds('C:\some/fake\folder/path\fileName.php'));

		// linux
		$this->assertEquals('/some/fake/folder/path/fileName/', Loader::ds('/some/fake/folder/path/fileName', true));
		$this->assertEquals('/some/fake/folder/path/fileName/', Loader::ds('/some\fake/folder\path/fileName/', true));

		// windows
		$this->assertEquals('C:/some/fake/folder/path/fileName/', Loader::ds('C:\some\fake\folder\path\fileName/'));
		$this->assertEquals('C:/some/fake/folder/path/fileName/', Loader::ds('C:\some/fake\folder/path\fileName\\'));
	}

	/**
	 * Test that defining new include paths registers correctly.
	 */
	public function testIncludePath() {
		$baseIncludePath = get_include_path();
		$selfPath1 = '/fake/test/1';
		$selfPath2 = '/fake/test/2';
		$selfPath3 = '/fake/test/3';

		$this->assertEquals($baseIncludePath, get_include_path());

		Loader::includePath($selfPath1);
		$this->assertEquals($baseIncludePath . PATH_SEPARATOR . $selfPath1, get_include_path());

		Loader::includePath(array($selfPath2, $selfPath3));
		$this->assertEquals($baseIncludePath . PATH_SEPARATOR . $selfPath1 . PATH_SEPARATOR . $selfPath2 . PATH_SEPARATOR . $selfPath3, get_include_path());
	}

	/**
	 * Test that removing an extension from a file path works correctly.
	 */
	public function testStripExt() {
		$this->assertEquals('NoExt', Loader::stripExt('NoExt'));
		$this->assertEquals('ClassName', Loader::stripExt('ClassName.php'));
		$this->assertEquals('File_Name', Loader::stripExt('File_Name.php'));

		$this->assertEquals('\test\namespace\ClassName', Loader::stripExt('\test\namespace\ClassName.php'));
		$this->assertEquals('\test\namespace\Class_Name', Loader::stripExt('\test\namespace\Class_Name.php'));

		$this->assertEquals('/test/file/path/FileName', Loader::stripExt('/test/file/path/FileName.php'));
		$this->assertEquals('/test/file/path/File/Name', Loader::stripExt('/test/file/path/File/Name.php'));
	}

	/**
	 * Test that converting a path to a namespace package works correctly.
	 */
	public function testToNamespace() {
		$this->assertEquals('test\file\path\FileName', Loader::toNamespace('/test/file/path/FileName.php'));
		$this->assertEquals('test\file\path\File\Name', Loader::toNamespace('/test/file/path/File/Name.php'));

		$this->assertEquals('test\file\path\FileName', Loader::toNamespace('vendors/src/test/file/path/FileName.php'));
		$this->assertEquals('Titon\test\file\path\File\Name', Loader::toNamespace('vendors/src/Titon/test/file/path/File/Name.php'));
	}

	/**
	 * Test that converting a namespace to a path works correctly.
	 */
	public function testToPath() {
		$this->assertEquals('/test/namespace/ClassName.php', Loader::toPath('\test\namespace\ClassName'));
		$this->assertEquals('/test/namespace/Class/Name.php', Loader::toPath('\test\namespace\Class_Name'));

		$this->assertEquals('/Test/NameSpace/ClassName.php', Loader::toPath('\Test\NameSpace\ClassName'));
		$this->assertEquals('/Test/NameSpace/Class/Name.php', Loader::toPath('\Test\NameSpace\Class_Name'));

		$this->assertEquals('/test/namespace/ClassName.PHP', Loader::toPath('\test\namespace\ClassName', 'PHP'));
		$this->assertEquals('/test/namespace/Class/Name.PHP', Loader::toPath('\test\namespace\Class_Name', 'PHP'));

		$this->assertEquals(TEST_DIR . '/test/namespace/ClassName.php', Loader::toPath('\test\namespace\ClassName', 'php', TEST_DIR));
		$this->assertEquals(TEST_DIR . '/test/namespace/Class/Name.php', Loader::toPath('\test\namespace\Class_Name', 'php', TEST_DIR));

		$this->assertEquals(VENDOR_DIR . '/test/namespace/ClassName.php', Loader::toPath('\test\namespace\ClassName', 'php', VENDOR_DIR));
		$this->assertEquals(VENDOR_DIR . '/test/namespace/Class/Name.php', Loader::toPath('\test\namespace\Class_Name', 'php', VENDOR_DIR));
	}

}