<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router\Tests\Targets;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Uri;
use Spiral\Router\Route;
use Spiral\Router\Targets\Action;
use Spiral\Router\Tests\Fixtures\TestController;
use Zend\Diactoros\ServerRequest;

class ActionTargetTest extends TestCase
{
    public function testDefaultAction()
    {
        $route = new Route("/home", new Action(TestController::class, "test"));
        $this->assertSame(['action' => 'test'], $route->getDefaults());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\ConstrainException
     */
    public function testConstrainedAction()
    {
        $route = new Route("/home", new Action(TestController::class, ["test", "other"]));
        $route->match(new ServerRequest());
    }

    public function testMatch()
    {
        $route = new Route(
            "/test[/<action>]",
            new Action(TestController::class, ["test", "other"])
        );

        $route = $route->withDefaults(['action' => 'test']);

        $this->assertNull($route->match(new ServerRequest()));
        $this->assertNull($route->match(new ServerRequest([], [], new Uri('/test/something'))));
        $this->assertNull($route->match(new ServerRequest([], [], new Uri('/test/tester'))));

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test')))
        );

        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/test')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/test/')))
        );
        $this->assertSame(['action' => 'test'], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/other')))
        );

        $this->assertSame(['action' => 'other'], $match->getMatches());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\InvalidArgumentException
     */
    public function testActionException()
    {
        new Action(TestController::class, $this);
    }
}