<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\ClassLoader;

use Symfony\Component\ClassLoader\ClassMapGenerator;

class ClassMapGeneratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getTestCreateMapTests
     */
    public function testCreateMap($directory, $expected)
    {
        $this->assertEqualsNormalized($expected, ClassMapGenerator::createMap($directory));
    }

    public function getTestCreateMapTests()
    {
        return array(
            array(__DIR__.'/Fixtures/Namespaced', array(
                'Namespaced\\Bar' => realpath(__DIR__).'/Fixtures/Namespaced/Bar.php',
                'Namespaced\\Foo' => realpath(__DIR__).'/Fixtures/Namespaced/Foo.php',
                'Namespaced\\Baz' => realpath(__DIR__).'/Fixtures/Namespaced/Baz.php',
                )
            ),
            array(__DIR__.'/Fixtures/beta/NamespaceCollision', array(
                'NamespaceCollision\\A\\B\\Bar' => realpath(__DIR__).'/Fixtures/beta/NamespaceCollision/A/B/Bar.php',
                'NamespaceCollision\\A\\B\\Foo' => realpath(__DIR__).'/Fixtures/beta/NamespaceCollision/A/B/Foo.php',
            )),
            array(__DIR__.'/Fixtures/Pearlike', array(
                'Pearlike_Foo' => realpath(__DIR__).'/Fixtures/Pearlike/Foo.php',
                'Pearlike_Bar' => realpath(__DIR__).'/Fixtures/Pearlike/Bar.php',
                'Pearlike_Baz' => realpath(__DIR__).'/Fixtures/Pearlike/Baz.php',
            )),
            array(__DIR__.'/Fixtures/classmap', array(
                'Foo\\Bar\\A'             => realpath(__DIR__).'/Fixtures/classmap/sameNsMultipleClasses.php',
                'Foo\\Bar\\B'             => realpath(__DIR__).'/Fixtures/classmap/sameNsMultipleClasses.php',
                'Alpha\\A'                => realpath(__DIR__).'/Fixtures/classmap/multipleNs.php',
                'Alpha\\B'                => realpath(__DIR__).'/Fixtures/classmap/multipleNs.php',
                'Beta\\A'                 => realpath(__DIR__).'/Fixtures/classmap/multipleNs.php',
                'Beta\\B'                 => realpath(__DIR__).'/Fixtures/classmap/multipleNs.php',
                'ClassMap\\SomeInterface' => realpath(__DIR__).'/Fixtures/classmap/SomeInterface.php',
                'ClassMap\\SomeParent'    => realpath(__DIR__).'/Fixtures/classmap/SomeParent.php',
                'ClassMap\\SomeClass'     => realpath(__DIR__).'/Fixtures/classmap/SomeClass.php',
            )),
        );
    }

    public function testCreateMapFinderSupport()
    {
        if (!class_exists('Symfony\\Component\\Finder\\Finder')) {
            $this->markTestSkipped('Finder component is not available');
        }

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()->in(__DIR__ . '/Fixtures/beta/NamespaceCollision');

        $this->assertEqualsNormalized(array(
            'NamespaceCollision\\A\\B\\Bar' => realpath(__DIR__).'/Fixtures/beta/NamespaceCollision/A/B/Bar.php',
            'NamespaceCollision\\A\\B\\Foo' => realpath(__DIR__).'/Fixtures/beta/NamespaceCollision/A/B/Foo.php',
        ), ClassMapGenerator::createMap($finder));
    }

    protected function assertEqualsNormalized($expected, $actual, $message = null)
    {
        foreach ($expected as $ns => $path) {
            $expected[$ns] = strtr($path, '\\', '/');
        }
        foreach ($actual as $ns => $path) {
            $actual[$ns] = strtr($path, '\\', '/');
        }
        $this->assertEquals($expected, $actual, $message);
    }
}
