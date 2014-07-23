<?php

/**
 * ITEA Office copyright message placeholder
 *
 * @category    Calendar
 * @package     View
 * @subpackage  Helper
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2014 ITEA Office (http://itea3.org)
 */
namespace Calendar\View\Helper;

use BjyAuthorize\Controller\Plugin\IsAllowed;
use BjyAuthorize\Service\Authorize;
use Calendar\Entity\EntityAbstract;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\ServerUrl;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;

/**
 * Class LinkAbstract
 * @package Calendar\View\Helper
 */
abstract class LinkAbstract extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * @var HelperPluginManager
     */
    protected $serviceLocator;
    /**
     * @var RouteMatch
     */
    protected $routeMatch = null;
    /**
     * @var string Text to be placed as title or as part of the linkContent
     */
    protected $text;
    /**
     * @var string
     */
    protected $router;
    /**
     * @var string
     */
    protected $action;
    /**
     * @var string
     */
    protected $show;
    /**
     * @var string
     */
    protected $alternativeShow;
    /**
     * @var array List of parameters needed to construct the URL from the router
     */
    protected $routerParams = [];
    /**
     * @var array content of the link (will be imploded during creation of the link)
     */
    protected $linkContent = [];
    /**
     * @var array Classes to be given to the link
     */
    protected $classes = [];
    /**
     * @var array
     */
    protected $showOptions = [];

    /**
     * This function produces the link in the end
     *
     * @return string
     */
    public function createLink()
    {
        /**
         * @var $url Url
         */
        $url = $this->serviceLocator->get('url');
        /**
         * @var $serverUrl ServerUrl
         */
        $serverUrl = $this->serviceLocator->get('serverUrl');
        $this->linkContent = [];
        $this->classes = [];
        $this->parseAction();
        $this->parseShow();
        if ('social' === $this->getShow()) {
            return $serverUrl->__invoke() . $url($this->router, $this->routerParams);
        }
        $uri = '<a href="%s" title="%s" class="%s">%s</a>';

        return sprintf(
            $uri,
            $serverUrl() . $url($this->router, $this->routerParams),
            $this->text,
            implode(' ', $this->classes),
            implode('', $this->linkContent)
        );
    }

    /**
     *
     */
    public function parseAction()
    {
        $this->action = null;
    }

    /**
     * @throws \Exception
     */
    public function parseShow()
    {
        switch ($this->getShow()) {
            case 'icon':
            case 'button':
                switch ($this->getAction()) {
                    case 'new-community':
                        $this->addLinkContent('<i class="fa fa-plus"></i>');
                        break;
                    case 'overview':
                    case 'contact':
                        $this->addLinkContent('<i class="fa fa-list-ul"></i>');
                        break;
                    case 'edit':
                    case 'edit-community':
                        $this->addLinkContent('<i class="fa fa-pencil-square-o"></i>');
                        break;
                    case 'download':
                    case 'download-community':
                    case 'download-binder':
                        $this->addLinkContent('<i class="fa fa-download"></i>');
                        break;
                    default:
                        $this->addLinkContent('<i class="fa fa-file-o"></i>');
                        break;
                }

                if ($this->getShow() === 'button') {
                    $this->addLinkContent(' ' . $this->getText());
                    $this->addClasses("btn btn-primary");
                }
                break;
            case 'text':
                $this->addLinkContent($this->getText());
                break;
            case 'paginator':
                if (is_null($this->getAlternativeShow())) {
                    throw new \InvalidArgumentException(
                        sprintf("this->alternativeShow cannot be null for a paginator link")
                    );
                }
                $this->addLinkContent($this->getAlternativeShow());
                break;
            case 'social':
                /**
                 * Social is treated in the createLink function, no content needs to be created
                 */

                return null;
            default:
                if (!array_key_exists($this->getShow(), $this->showOptions)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            "The option \"%s\" should be available in the showOptions array, only \"%s\" are available",
                            $this->getShow(),
                            implode(', ', array_keys($this->showOptions))
                        )
                    );
                }
                $this->addLinkContent($this->showOptions[$this->getShow()]);
                break;
        }
    }

    /**
     * @return string
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @param string $show
     */
    public function setShow($show)
    {
        $this->show = $show;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @param $linkContent
     *
     * @return $this
     */
    public function addLinkContent($linkContent)
    {
        if (!is_array($linkContent)) {
            $linkContent = [$linkContent];
        }
        foreach ($linkContent as $content) {
            $this->linkContent[] = $content;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @param string $classes
     *
     * @return $this
     */
    public function addClasses($classes)
    {
        if (!is_array($classes)) {
            $classes = [$classes];
        }
        foreach ($classes as $class) {
            $this->classes[] = $class;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternativeShow()
    {
        return $this->alternativeShow;
    }

    /**
     * @param string $alternativeShow
     */
    public function setAlternativeShow($alternativeShow)
    {
        $this->alternativeShow = $alternativeShow;
    }

    /**
     * @param string $showOptions
     */
    public function setShowOptions($showOptions)
    {
        $this->showOptions = $showOptions;
    }

    /**
     * @param EntityAbstract $entity
     * @param string         $assertion
     * @param string         $action
     *
     * @return bool
     */
    public function hasAccess(EntityAbstract $entity, $assertion, $action)
    {
        $assertion = $this->getAssertion($assertion);
        if (!is_null($entity) && !$this->getAuthorizeService()->getAcl()->hasResource($entity)) {
            $this->getAuthorizeService()->getAcl()->addResource($entity);
            $this->getAuthorizeService()->getAcl()->allow([], $entity, [], $assertion);
        }
        if (!$this->isAllowed($entity, $action)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $assertion
     *
     * @return mixed
     */
    public function getAssertion($assertion)
    {
        return $this->getServiceLocator()->get($assertion);
    }

    /**
     * Get the service locator.
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator->getServiceLocator();
    }

    /**
     * Set the service locator.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AbstractHelper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService()
    {
        return $this->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
    }

    /**
     * @param null|EntityAbstract $resource
     * @param string              $privilege
     *
     * @return bool
     */
    public function isAllowed($resource, $privilege = null)
    {
        /**
         * @var $isAllowed IsAllowed
         */
        $isAllowed = $this->serviceLocator->get('isAllowed');

        return $isAllowed($resource, $privilege);
    }

    /**
     * Add a parameter to the list of parameters for the router
     *
     * @param string $key
     * @param        $value
     * @param bool   $allowNull
     */
    public function addRouterParam($key, $value, $allowNull = true)
    {
        if (!$allowNull && is_null($value)) {
            throw new \InvalidArgumentException(sprintf("null is not allowed for %s", $key));
        }
        if (!is_null($value)) {
            $this->routerParams[$key] = $value;
        }
    }

    /**
     * @return string
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param string $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getRouterParams()
    {
        return $this->routerParams;
    }

    /**
     * RouteInterface match returned by the router.
     * Use a test on is_null to have the possibility to overrule the serviceLocator lookup for unit tets reasons
     *
     * @return RouteMatch.
     */
    public function getRouteMatch()
    {
        if (is_null($this->routeMatch)) {
            $this->routeMatch = $this->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch();
        }

        return $this->routeMatch;
    }

    /**
     * @param RouteMatch $routeMatch
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function translate($string)
    {
        return $this->serviceLocator->get('translate')->__invoke($string);
    }
}
