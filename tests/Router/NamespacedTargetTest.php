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
use Spiral\Router\Targets\Namespaced;
use Zend\Diactoros\ServerRequest;

class NamespacedTargetTest extends TestCase
{
    public function testDefaultAction()
    {
        $route = new Route("/<controller>/<action>", new Namespaced('Spiral\Router\Fixtures'));
        $this->assertSame(['controller' => null, 'action' => null], $route->getDefaults());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\ConstrainException
     */
    public function testConstrainedController()
    {
        $route = new Route("/<action>", new Namespaced('Spiral\Router\Fixtures'));
        $route->match(new ServerRequest());
    }

    /**
     * @expectedException \Spiral\Router\Exceptions\ConstrainException
     */
    public function testConstrainedAction()
    {
        $route = new Route("/<controller>", new Namespaced('Spiral\Router\Fixtures'));
        $route->match(new ServerRequest());
    }

    public function testMatch()
    {
        $route = new Route(
            "/<controller>[/<action>]",
            new Namespaced('Spiral\Router\Fixtures')
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

        $this->assertNotNull(
            $match = $route->match(new ServerRequest([], [], new Uri('/other/action/')))
        );

        $this->assertSame(['controller' => 'other', 'action' => 'action'], $match->getMatches());
    }
}