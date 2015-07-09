<?php


namespace Ajde;

use Ajde\Object\Singleton;
use Ajde\Model;
use Ajde\Event;
use Ajde\Core\Route;
use SettingModel;
use NodeModel;
use Config;
use ProductModel;



class Cms extends Singleton
{
    private $_homepageSet = false;

    /**
     * @var NodeModel|boolean
     */
    private $_detectedNode = false;

    public static function getInstance()
    {
        static $instance;
        return $instance === null ? $instance = new self : $instance;
    }

    protected function __construct()
    {
        Model::registerAll();

        // Load applications bootstrap file
        require_once APP_DIR . 'Bootstrap.php';
    }

    public function __bootstrap()
    {
        Event::register('Ajde_Core_Route', 'onAfterLangSet', array($this, 'setHomepage'));
        Event::register('Ajde_Core_Route', 'onAfterRouteSet', array($this, 'detectNodeSlug'));
        Event::register('Ajde_Core_Route', 'onAfterRouteSet', array($this, 'detectShopSlug'));
        return true;
    }

    public function setHomepage(Route $route)
    {
        if ($this->_homepageSet) return;
        $this->_homepageSet = true;

        $homepageNodeId = (int) SettingModel::byName('homepage');

        if ($homepageNodeId) {
            $node = NodeModel::fromPk($homepageNodeId);
            if ($node) {
                Config::getInstance()->homepageRoute = $node->getUrl();
            }
        }
    }

    public function detectNodeSlug(Route $route)
    {
        $slug = $route->getRoute();

        $slug = trim($slug, '/');
        $lastSlash = strrpos($slug, '/');
        if ($lastSlash !== false) {
            $slug = substr($slug, $lastSlash + 1);
        }

        $node = NodeModel::fromSlug($slug);
        if ($node) {
            $this->_detectedNode = $node;
            $route->setRoute($slug);
            $routes = Config::get('routes');
            array_unshift($routes, array('%^(' . preg_quote($slug) . ')$%' => array('slug')));
            Config::getInstance()->routes = $routes;
        }
    }

    public function detectShopSlug(Route $route)
    {
        $slug = $route->getRoute();

        $slug = trim($slug, '/');
        $lastSlash = strrpos($slug, '/');
        if ($lastSlash !== false) {
            $lastSlugPart = substr($slug, $lastSlash + 1);

            $product = ProductModel::fromSlug($lastSlugPart);
            if ($product) {
                $route->setRoute($slug);
                $routes = Config::get('routes');
                array_unshift($routes, array('%^(shop)/(' . preg_quote($lastSlugPart) . ')$%' => array('module', 'slug')));
                Config::getInstance()->routes = $routes;
            }
        }
    }

    /**
     * @return NodeModel|boolean
     */
    public function getRoutedNode()
    {
        return $this->_detectedNode;
    }
}