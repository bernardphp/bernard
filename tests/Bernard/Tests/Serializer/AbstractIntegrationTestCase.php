<?php

namespace Bernard\Tests\Serializer;

abstract class AbstractIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = $this->getSerializer();
    }

    public function getSerializeTests()
    {
        return $this->findFiles($this->getFixturesDir() . '/serialize');
    }

    public function getDeserializeTests()
    {
        return $this->findFiles($this->getFixturesDir() . '/deserialize');

    }

    /**
     * @dataProvider getDeserializeTests
     */
    public function testDeserializeIntegration($file)
    {
        $this->assertFileExists($file);

        list($json, $expected) = $this->extractExpected($file);

        $envelope = $this->serializer->deserialize($json);
        $pretty = trim(print_r($envelope, true));

        $this->assertEquals($pretty, $expected);
    }

    /**
     * @dataProvider getSerializeTests
     */
    public function testSerializeIntegration($file)
    {
        $this->assertFileExists($file);

        list($test, $expected) = $this->extractExpected($file);

        $envelope = eval($test);

        $this->assertEquals($this->serializer->serialize($envelope), $expected);
    }

    abstract protected function getFixturesDir();

    abstract protected function getSerializer();

    protected function extractExpected($file, $divider = '--EXPECTED--')
    {
        $contents = file_get_contents($file, null, null);
        $position = strrpos($contents, $divider);

        return array(
            trim(substr($contents, 0, $position)),
            trim(substr($contents, $position + strlen($divider))),
        );
    }

    protected function findFiles($directory)
    {
        $directory = new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME);
        $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::CHILD_FIRST);

        $files = array_filter(iterator_to_array($iterator), 'is_file');

        return array_map(function ($file) { return array($file); }, $files);
    }
}
