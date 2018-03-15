<?php

/**
 * ITEA Office all rights reserved
 *
 * @category   Calendar
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Calendar\View\Helper;

use BjyAuthorize\Controller\Plugin\IsAllowed;
use BjyAuthorize\Service\Authorize;
use Calendar\Entity\Calendar;
use Calendar\Entity\EntityAbstract;
use Project\Entity\Project;
use Zend\Router\Http\RouteMatch;
use Zend\View\Helper\ServerUrl;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;

/**
 * Class LinkAbstract.
 */
abstract class LinkAbstract extends AbstractViewHelper
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
     * @var Calendar
     */
    protected $calendar;
    /**
     * @var int
     */
    protected $year;
    /**
     * @var
     */
    protected $which;
    /**
     * @var Project
     */
    protected $project;

    /**
     * @return string
     * @throws \Exception
     */
    public function createLink(): string
    {
        /**
         * @var $url Url
         */
        $url = $this->getHelperPluginManager()->get('url');
        /**
         * @var $serverUrl ServerUrl
         */
        $serverUrl = $this->getHelperPluginManager()->get('serverUrl');
        $this->linkContent = [];

        $this->parseAction();
        $this->parseShow();
        if ('social' === $this->getShow()) {
            return $serverUrl() . $url($this->router, $this->routerParams);
        }
        $uri = '<a href="%s" title="%s" class="%s">%s</a>';

        return sprintf(
            $uri,
            $serverUrl() . $url($this->router, $this->routerParams),
            htmlentities((string)$this->text),
            implode(' ', $this->classes),
            \in_array($this->getShow(), ['icon', 'button', 'alternativeShow']) ? implode('', $this->linkContent)
                : htmlentities(implode('', $this->linkContent))
        );
    }

    /**
     *
     */
    public function parseAction(): void
    {
        $this->action = null;
    }

    /**
     * @throws \Exception
     */
    public function parseShow(): void
    {
        switch ($this->getShow()) {
            case 'icon':
            case 'button':
                switch ($this->getAction()) {
                    case 'new':
                    case 'new-community':
                        $this->addLinkContent('<i class="fa fa-plus"></i>');
                        break;
                    case 'review-calendar':
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
                    case 'download-review-calendar':
                        $this->addLinkContent('<i class="fa fa-download"></i>');
                        break;
                    case 'presence-list':
                    case 'signature-list':
                        $this->addLinkContent('<i class="fa fa-file-pdf-o"></i>');
                        break;
                    case 'send-message':
                        $this->addLinkContent('<i class="fa fa-envelope"></i>');
                        break;
                    case 'select-attendees':
                        $this->addLinkContent('<i class="fa fa-users"></i>');
                        break;
                    case 'view-admin':
                        $this->addLinkContent('<i class="fa fa-link"></i>');
                        break;
                    default:
                        $this->addLinkContent('<i class="fa fa-file-o"></i>');
                        break;
                }
                if ($this->getShow() === 'button') {
                    $this->addLinkContent(' ' . $this->getText());
                    $this->addClasses('btn btn-primary');
                }
                break;
            case 'text':
                $this->addLinkContent($this->getText());
                break;
            case 'paginator':
                if (null === $this->getAlternativeShow()) {
                    throw new \InvalidArgumentException(
                        sprintf("this->alternativeShow cannot be null for a paginator link")
                    );
                }
                $this->addLinkContent($this->getAlternativeShow());
                break;
            case 'social':
                /*
                 * Social is treated in the createLink function, no content needs to be created
                 */

                return;
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
        foreach ((array)$classes as $class) {
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
     * @param array $showOptions
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
        if (!\is_null($entity)
            && !$this->getAuthorizeService()->getAcl()->hasResource($entity)
        ) {
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
        return $this->getServiceManager()->get($assertion);
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService()
    {
        return $this->getServiceManager()->get('BjyAuthorize\Service\Authorize');
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
        $isAllowed = $this->getHelperPluginManager()->get('isAllowed');

        return $isAllowed($resource, $privilege);
    }

    /**
     * Add a parameter to the list of parameters for the router.
     *
     * @param string $key
     * @param        $value
     * @param bool   $allowNull
     */
    public function addRouterParam($key, $value, $allowNull = true)
    {
        if (!$allowNull && null === $value) {
            throw new \InvalidArgumentException(sprintf("null is not allowed for %s", $key));
        }
        if (null !== $value) {
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
     * @return Project
     */
    public function getProject()
    {
        if (\is_null($this->project)) {
            $this->project = new Project();
        }

        return $this->project;
    }

    /**
     * @param Project $project
     *
     * @return LinkAbstract
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }


    /**
     * @return Calendar
     */
    public function getCalendar()
    {
        if (\is_null($this->calendar)) {
            $this->calendar = new Calendar();
        }

        return $this->calendar;
    }

    /**
     * @param Calendar $calendar
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getWhich()
    {
        return $this->which;
    }

    /**
     * @param mixed $which
     */
    public function setWhich($which)
    {
        $this->which = $which;
    }
}
