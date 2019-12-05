<?php
/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 */
declare(strict_types=1);

namespace Calendar\View\Handler;

use Content\Entity\Content;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Application;
use Zend\Router\Http\RouteMatch;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadMeta;
use Zend\View\Helper\HeadStyle;
use Zend\View\Helper\HeadTitle;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class AbstractHandler
 *
 * @package Calendar\View
 */
abstract class AbstractHandler extends AbstractHelper
{
    protected HelperPluginManager $helperPluginManager;
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var TwigRenderer
     */
    protected TwigRenderer $renderer;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    public function __construct(
        Application $application,
        HelperPluginManager $helperPluginManager,
        TwigRenderer $renderer,
        TranslatorInterface $translator
    ) {
        $this->helperPluginManager = $helperPluginManager;
        $this->renderer = $renderer;
        $this->translator = $translator;

        //Take the last remaining properties from the application
        $this->routeMatch = $application->getMvcEvent()->getRouteMatch();
        $this->response = $application->getMvcEvent()->getResponse();
        $this->request = $application->getMvcEvent()->getRequest();
    }

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

    public function hasDocRef(): bool
    {
        return null !== $this->getDocRef();
    }

    public function getDocRef(): ?string
    {
        return $this->routeMatch->getParam('routeMatch');
    }

    public function getHeadTitle(): HeadTitle
    {
        return $this->helperPluginManager->get('headTitle');
    }

    public function getHeadMeta(): HeadMeta
    {
        return $this->helperPluginManager->get('headMeta');
    }

    public function getHeadStyle(): HeadStyle
    {
        return $this->helperPluginManager->get('headStyle');
    }

    public function translate(string $string): string
    {
        return $this->translator->translate($string);
    }
}
