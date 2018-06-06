<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2018 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 */
declare(strict_types=1);

namespace Calendar\View\Handler;

use Content\Entity\Content;
use Content\Navigation\Service\UpdateNavigationService;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Application;
use Zend\Router\Http\RouteMatch;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadMeta;
use Zend\View\Helper\HeadStyle;
use Zend\View\Helper\HeadTitle;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class AbstractHandler
 *
 * @package Calendar\View
 */
abstract class AbstractHandler extends AbstractHelper
{
    /**
     * @var HelperPluginManager
     */
    protected $helperPluginManager;
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var TwigRenderer
     */
    protected $renderer;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;
    /**
     * @var UpdateNavigationService
     */
    protected $updateNavigationService;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * AbstractHandler constructor.
     *
     * @param Application             $application
     * @param HelperPluginManager     $helperPluginManager
     * @param TwigRenderer            $renderer
     * @param AuthenticationService   $authenticationService
     * @param UpdateNavigationService $updateNavigationService
     * @param TranslatorInterface     $translator
     */
    public function __construct(
        Application $application,
        HelperPluginManager $helperPluginManager,
        TwigRenderer $renderer,
        AuthenticationService $authenticationService,
        UpdateNavigationService $updateNavigationService,
        TranslatorInterface $translator
    ) {
        $this->helperPluginManager = $helperPluginManager;
        $this->renderer = $renderer;
        $this->authenticationService = $authenticationService;
        $this->updateNavigationService = $updateNavigationService;
        $this->translator = $translator;

        //Take the last remaining properties from the application
        $this->routeMatch = $application->getMvcEvent()->getRouteMatch();
        $this->response = $application->getMvcEvent()->getResponse();
        $this->request = $application->getMvcEvent()->getRequest();
    }

    /**
     * @param Content $content
     *
     * @return array
     */
    public function extractContentParam(Content $content): array
    {
        $params = [
            'id'     => null,
            'docRef' => null,
            'year'   => null,
            'limit'  => null,
        ];

        foreach ($content->getContentParam() as $contentParam) {
            if (!empty($contentParam->getParameterId())) {
                $params[$contentParam->getParameter()->getParam()] = $contentParam->getParameterId();
            }
        }

        //Overrule all the params, except when we are dealing with docRef
        foreach ($this->routeMatch->getParams() as $routeParam => $value) {
            if ($routeParam !== 'docRef' || null === $params['docRef']) {
                $params[$routeParam] = $value;
            }
        }

        //Convert the ints to ints (it they are null
        null === $params['id'] ?: $params['id'] = (int)$params['id'];
        null === $params['year'] ?: $params['year'] = (int)$params['year'];
        null === $params['limit'] ?: $params['limit'] = (int)$params['limit'];

        return $params;
    }

    /**
     * @return bool
     */
    public function hasDocRef(): bool
    {
        return null !== $this->getDocRef();
    }

    /**
     * @return null|string
     */
    public function getDocRef(): ?string
    {
        return $this->routeMatch->getParam('routeMatch');
    }

    /**
     * @return HeadTitle|AbstractContainer
     */
    public function getHeadTitle(): HeadTitle
    {
        return $this->helperPluginManager->get('headTitle');
    }

    /**
     * @return HeadMeta
     */
    public function getHeadMeta(): HeadMeta
    {
        return $this->helperPluginManager->get('headMeta');
    }

    /**
     * @return HeadStyle
     */
    public function getHeadStyle(): HeadStyle
    {
        return $this->helperPluginManager->get('headStyle');
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function translate($string): string
    {
        return $this->translator->translate($string);
    }
}
