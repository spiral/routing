<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Router;

use PHPUnit\Framework\TestCase;
use Spiral\Http\Uri;
use Spiral\Router\Fixtures\TestController;
use Spiral\Router\Targets\Group;
use Zend\Diactoros\ServerRequest;

class GroupTargetTest extends TestCase
{
    public function testDefaultAction()
    {
        $route = new Route("/<controller>/<action>", new Group(['test' => TestController::class]));
        $this->assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\ConstrainException
     */
    public function testConstrainedController()
    {
        $route = new Route("/<action>", new Group(['test' => TestController::class]));
        $route->match(new ServerRequest());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\ConstrainException
     */
    public function testConstrainedAction()
    {
        $route = new Route("/<controller>", new Group(['test' => TestController::class]));
        $route->match(new ServerRequest());
    }

    public function testActionSelector()
    {
        $route = new Route(
            "/<controller>[/<action>]",
            new Group(['test' => TestController::class])
        );

        $route = $route->withDefaults(['controller' => 'test']);

        $this->assertNull($route->match(new ServerRequest()));

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test')))
        );

        $this->assertSame(['controller' => 'test', 'action' => null], $match->getMatches());

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/test/action/')))
        );

        $this->assertSame(['controller' => 'test', 'action' => 'action'], $match->getMatches());

        $this->assertNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/other/action/')))
        );

        $this->assertNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/other')))
        );
    }
}