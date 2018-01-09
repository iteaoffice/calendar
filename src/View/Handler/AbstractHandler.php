<?php
/**
 * ITEA Office copyright message placeholder
 *
 * @category  Calendar
 * @package   View\Handler
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Press\View\Handler;

use Content\Entity\Content;
use Content\Navigation\Service\UpdateNavigationService;
use Press\Service\ArticleService;
use Press\View\Helper\ArticleLink;
use Project\Service\ProjectService;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Response;
use Zend\Router\Http\RouteMatch;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadMeta;
use Zend\View\Helper\HeadStyle;
use Zend\View\Helper\HeadTitle;
use Zend\View\Helper\Placeholder\Container\AbstractContainer;
use Zend\View\HelperPluginManager;
use ZfcTwig\View\TwigRenderer;

/**
 * Class AbstractViewHelper
 *
 * @package News\View\Helper
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
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;
    /**
     * @var UpdateNavigationService
     */
    protected $updateNavigationService;
    /**
     * @var ArticleService
     */
    protected $articleService;
    /**
     * @var ProjectService
     */
    protected $projectService;

    /**
     * @param Content $content
     * @return array
     */
    public function extractContentParam(Content $content): array
    {
        $params = [
            'id'           => null,
            'docRef'       => null,
            'year'         => null,
            'limit'        => null,
        ];


        foreach ($content->getContentParam() as $contentParam) {
            $params[$contentParam->getParameter()->getParam()] = $contentParam->getParameterId();
        }

        foreach ($this->getRouteMatch()->getParams() as $routeParam => $value) {
            null === $value ?: $params[$routeParam] = $value;
        }

        return $params;
    }


    /**
     * @return null|string
     */
    public function getDocRef(): ?string
    {
        return $this->getRouteMatch()->getParam('routeMatch');
    }

    /**
     * @return bool
     */
    public function hasDocRef(): bool
    {
        return null !== $this->getDocRef();
    }

    /**
     * @return HelperPluginManager
     */
    public function getHelperPluginManager(): HelperPluginManager
    {
        return $this->helperPluginManager;
    }

    /**
     * @param HelperPluginManager $helperPluginManager
     * @return AbstractHandler
     */
    public function setHelperPluginManager(HelperPluginManager $helperPluginManager): AbstractHandler
    {
        $this->helperPluginManager = $helperPluginManager;

        return $this;
    }

    /**
     * @return RouteMatch
     */
    public function getRouteMatch(): RouteMatch
    {
        return $this->routeMatch;
    }

    /**
     * @param RouteMatch $routeMatch
     * @return AbstractHandler
     */
    public function setRouteMatch(RouteMatch $routeMatch): AbstractHandler
    {
        $this->routeMatch = $routeMatch;

        return $this;
    }

    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService(): AuthenticationService
    {
        return $this->authenticationService;
    }

    /**
     * @param AuthenticationService $authenticationService
     * @return AbstractHandler
     */
    public function setAuthenticationService(AuthenticationService $authenticationService): AbstractHandler
    {
        $this->authenticationService = $authenticationService;

        return $this;
    }

    /**
     * @return TwigRenderer
     */
    public function getRenderer(): TwigRenderer
    {
        return $this->renderer;
    }

    /**
     * @param TwigRenderer $renderer
     * @return AbstractHandler
     */
    public function setRenderer(TwigRenderer $renderer): AbstractHandler
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @return ResponseInterface|Response
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     * @return AbstractHandler
     */
    public function setResponse(ResponseInterface $response): AbstractHandler
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return UpdateNavigationService
     */
    public function getUpdateNavigationService(): UpdateNavigationService
    {
        return $this->updateNavigationService;
    }

    /**
     * @param UpdateNavigationService $updateNavigationService
     * @return AbstractHandler
     */
    public function setUpdateNavigationService(UpdateNavigationService $updateNavigationService): AbstractHandler
    {
        $this->updateNavigationService = $updateNavigationService;

        return $this;
    }

    /**
     * @return ArticleService
     */
    public function getArticleService(): ArticleService
    {
        return $this->articleService;
    }

    /**
     * @param ArticleService $articleService
     * @return AbstractHandler
     */
    public function setArticleService(ArticleService $articleService): AbstractHandler
    {
        $this->articleService = $articleService;

        return $this;
    }

    /**
     * @return HeadTitle|AbstractContainer
     */
    public function getHeadTitle(): HeadTitle
    {
        return $this->getHelperPluginManager()->get('headTitle');
    }

    /**
     * @return HeadMeta
     */
    public function getHeadMeta(): HeadMeta
    {
        return $this->getHelperPluginManager()->get('headMeta');
    }

    /**
     * @return HeadStyle
     */
    public function getHeadStyle(): HeadStyle
    {
        return $this->getHelperPluginManager()->get('headStyle');
    }

    /**
     * @return ArticleLink
     */
    public function getArticleLink(): ArticleLink
    {
        return $this->getHelperPluginManager()->get(ArticleLink::class);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function translate($string): string
    {
        return $this->getHelperPluginManager()->get('translate')($string);
    }

    /**
     * @return ProjectService
     */
    public function getProjectService(): ProjectService
    {
        return $this->projectService;
    }

    /**
     * @param ProjectService $projectService
     * @return AbstractHandler
     */
    public function setProjectService(ProjectService $projectService): AbstractHandler
    {
        $this->projectService = $projectService;

        return $this;
    }
}
