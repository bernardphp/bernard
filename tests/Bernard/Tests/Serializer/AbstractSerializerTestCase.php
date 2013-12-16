<?php

namespace Bernard\Tests\Serializer;

abstract class AbstractSerializerTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = $this->getSerializer();
    }

    public function getSerializeTests()
    {
        return array_map(array($this, 'wrapInArray'), $this->getFiles('php'));
    }

    public function getDeserializeTests()
    {
        return array_map(array($this, 'wrapInArray'), $this->getFiles('json'));

    }

    /**
     * @dataProvider getDeserializeTests
     */
    public function testDeserializeIntegration($file)
    {
        $this->assertFileExists($file);
        $this->assertFileExists($expected = $file . '.expect');

        // Some editors uses EOL as marking the end of file. (vim)
        $envelope = $this->serializer->deserialize(file_get_contents($file));

        $this->assertEquals(print_r($envelope, true), file_get_contents($expected));
    }

    /**
     * @dataProvider getSerializeTests
     */
    public function testSerializeIntegration($file)
    {
        $this->assertFileExists($file);
        $this->assertFileExists($expected = $file . '.expect');

        // Some editors uses EOL as marking the end of file. (vim)
        $expected = rtrim(file_get_contents($expected), "\n\r");

        $this->assertEquals($this->serializer->serialize(require $file), $expected);
    }

    protected function getFiles($extension)
    {
        $directory = new \RecursiveDirectoryIterator($this->getFixturesDir(), \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);

        return array_filter(iterator_to_array($iterator), function ($file) use ($extension) {
            if ($file->isDir()) {
                return false;

            }

            return $file->getExtension() == $extension;
        });
    }

    public function wrapInArray($file)
    {
        return array($file->getPathName());
    }

    abstract protected function getFixturesDir();

    abstract protected function getSerializer();
}
