<?php

use Anon\Filters\IdentifierTranslator;

class IdentifierTranslatorTest extends PHPUnit_Framework_TestCase
{
    protected $filter;

    public function setUp()
    {
        parent::setUp();

        // Mock route object
        $this->route = $this->getMockBuilder(stdClass::class)
            ->setMethods(['parameters', 'setParameter', 'forgetParameter'])
            ->getMock();
        // Mock request object
        $this->request = $this->getMockBuilder(stdClass::class)
            ->getMock();
        // Mock Laravel App
        $this->app = $this->getMockBuilder(stdClass::class)
            ->setMethods(['make'])
            ->getMock();
        // Mock Cached Identifier Repository
        $this->repo = $this->getMock('Anon\Support\Contracts\CachedIdentifierInterface');

        // Instantiate Filter
        $this->translator = new IdentifierTranslator($this->app);
    }

    /**
     * Test that a parameter with the Identifier suffix
     * results in the proper translation calls to
     * its corresponding Repository.
     */
    public function test_translateIdentifier_identifierSuffix()
    {
        $this->route
            ->method('parameters')
            ->willReturn([
                'testIdentifier' => '123abc'
        ]);

        $this->app
            ->method('make')
            ->with('TestRepositoryInterface')
            ->willReturn($this->repo);

        $this->repo
            ->method('getIdFromIdentifier')
            ->with('123abc')
            ->willReturn(123);

        $this->route
            ->expects($this->once())
            ->method('setParameter')
            ->with('testIdentifier', 123);

        $this->translator->filter($this->route, $this->request);
    }

    /**
     * Test that a parameter with the Idf suffix
     * results in the proper translation calls to
     * its corresponding Repository.
     */
    public function test_translateIdentifier_idfSuffix()
    {
        $this->route
            ->method('parameters')
            ->willReturn([
                'testIdf' => '123abc'
        ]);

        $this->app
            ->method('make')
            ->with('TestRepositoryInterface')
            ->willReturn($this->repo);

        $this->repo
            ->method('getIdFromIdentifier')
            ->with('123abc')
            ->willReturn(123);

        $this->route
            ->expects($this->once())
            ->method('setParameter')
            ->with('testIdf', 123);

        $this->translator->filter($this->route, $this->request);
    }

    /**
     * Test that a parameter for which a corresponding
     * Repository cannot be found causes an exception to be thrown.
     *
     * @expectedException Anon\Filters\IdentifierTranslatorException
     */
    public function test_translateIdentifier_missingRepo()
    {
        $this->route
            ->method('parameters')
            ->willReturn([
                'testIdentifier' => '123abc'
        ]);

        $this->app
            ->method('make')
            ->with('TestRepositoryInterface')
            ->will($this->throwException(new Exception));

        $this->repo
            ->expects($this->never())
            ->method('getIdFromIdentifier');

        $this->route
            ->expects($this->never())
            ->method('setParameter');

        $this->translator->filter($this->route, $this->request);
    }

    /**
     * Test that a parameter for which the corresponding
     * Repository does not implement the CachedIdentifierInterface
     * causes an exception to be thrown.
     *
     * @expectedException Anon\Filters\IdentifierTranslatorException
     */
    public function test_translateIdentifier_missingInterface()
    {
        $this->route
            ->method('parameters')
            ->willReturn([
                'testIdf' => '123abc'
        ]);

        $this->app
            ->method('make')
            ->with('TestRepositoryInterface')
            ->willReturn($this->getMock(stdClass::class));

        $this->repo
            ->expects($this->never())
            ->method('getIdFromIdentifier');

        $this->route
            ->expects($this->never())
            ->method('setParameter');

        $this->translator->filter($this->route, $this->request);
    }

    /**
     * Test that a parameter with a non-matching suffix
     * causes the filter to skip it.
     */
    public function test_translateIdentifier_ignoredParameter_wrongSuffix()
    {
        $this->route
            ->method('parameters')
            ->willReturn([
                'testId' => '123abc'
        ]);

        $this->app
            ->expects($this->never())
            ->method('make');

        $this->repo
            ->expects($this->never())
            ->method('getIdFromIdentifier');

        $this->route
            ->expects($this->never())
            ->method('setParameter');

        $this->translator->filter($this->route, $this->request);
    }

    /**
     * Test that a parameter prefixed with an underscore
     * is simply stripped of the underscore, and not translated.
     */
    public function test_translateIdentifier_ignoredParameter_underscore()
    {
        $this->route
            ->method('parameters')
            ->willReturn([
                '_testIdentifier' => '123abc'
        ]);

        $this->route
            ->expects($this->once())
            ->method('forgetParameter')
            ->with('_testIdentifier');

        $this->route
            ->expects($this->once())
            ->method('setParameter')
            ->with('testIdentifier', '123abc');

        $this->app
            ->expects($this->never())
            ->method('make');

        $this->repo
            ->expects($this->never())
            ->method('getIdFromIdentifier');

        $this->translator->filter($this->route, $this->request);
    }
}

