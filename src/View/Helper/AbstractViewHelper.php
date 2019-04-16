<?php
/**
 * Jield BV All rights reserved
 *
 * @category    Safety Form
 * @package     Substrate
 * @subpackage  Entity
 * @author      Dr. ir. Johan van der Heide <info@jield.nl>
 * @copyright   Copyright (c) 2004-2017 Jield BV (https://jield.nl)
 * @version     5.0
 */

declare(strict_types=1);

namespace Calendar\View\Helper;

use Application\Service\AssertionService;
use Zend\Http\Request;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\Application;
use Zend\Router\Http\RouteMatch;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class AbstractViewHelper
 *
 * @package Calender\View\Helper
 */
abstract class AbstractViewHelper extends AbstractHelper
{
    /**
     * @var HelperPluginManager
     */
    protected $helperPluginManager;
    /**
     * @var AssertionService
     */
    protected $assertionService;
    /**
     * @var TwigRenderer
     */
    protected $renderer;
    /**
     * @var RouteMatch|null
     */
    protected $routeMatch;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var array
     */
    private $config;

    public function __construct(
        Application $application,
        HelperPluginManager $helperPluginManager,
        AssertionService $assertionService,
        TwigRenderer $renderer,
        TranslatorInterface $translator
    ) {
        $this->helperPluginManager = $helperPluginManager;
        $this->assertionService = $assertionService;
        $this->renderer = $renderer;
        $this->translator = $translator;

        $this->routeMatch = $application->getMvcEvent()->getRouteMatch();
        $this->request = $application->getMvcEvent()->getRequest();
        $this->config = $application->getServiceManager()->get('config');
    }

    public function getUrl(): Url
    {
        return $this->helperPluginManager->get('url');
    }

    public function getServerUrl(): string
    {
        //Grab the ServerURL from the config to avoid problems with CLI code
        return $this->config['deeplink']['serverUrl'];
    }
}
