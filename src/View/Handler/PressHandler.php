<?php
/**
 * ITEA Office all rights reserved
 *
 * @category   Content
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

declare(strict_types=1);

namespace Press\View\Handler;

use Content\Entity\Content;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Press\Entity\Article;
use Zend\Paginator\Paginator;

/**
 * Class PressHandler
 * @package Press\View\Handler
 */
class PressHandler extends AbstractHandler
{
    /**
     * @param Content $content
     * @return null|string|\Zend\Http\Response
     */
    public function __invoke(Content $content): ?string
    {
        $params = $this->extractContentParam($content);

        switch ($content->getHandler()->getHandler()) {
            case 'press_article':
                $article = $this->getArticleByParams($params);

                if (null === $article) {
                    return 'Article cannot be found';
                }

                $this->getHeadTitle()->append($this->translate("txt-press-article"));
                $this->getHeadTitle()->append($article->getArticle());
                $this->getHeadMeta()->setProperty('og:type', $this->translate("txt-press-article"));
                $this->getHeadMeta()->setProperty('og:title', $article->getArticle());
                $this->getHeadMeta()->setProperty('og:url', $this->getArticleLink()($article, 'view', 'social'));

                return $this->parseArticle($article);
            case 'press_list':
                $this->getHeadTitle()->append($this->translate("txt-press-article-list"));
                $page = $this->getRouteMatch()->getParam('page');
                $year = $this->getRouteMatch()->getParam('year');

                if (null !== $year) {
                    $year = (int)$year;
                }

                return $this->parseArticleList($page, $year);
            case 'press_year_selector':
                return $this->parseYearSelector();
            case 'press_project':
                $article = $this->getArticleByParams($params);

                if (null === $article) {
                    return 'Article cannot be found';
                }

                return $this->parseArticleProjectList($article);
        }

        return null;
    }

    /**
     * @param array $params
     * @return Article|null
     */
    private function getArticleByParams(array $params): ?Article
    {
        $article = null;
        if (null !== $params['id']) {
            $article = $this->getArticleService()->findArticleById((int)$params['id']);
        }

        if (null !== $params['docRef']) {
            //The docRef can contain also an articleId, so try first to find that, and do a fallback on the docRef
            $article = $this->getArticleService()->findArticleById((int)$params['docRef']);

            if (null === $article) {
                /** @var Article $article */
                $article = $this->getArticleService()->findArticleByDocRef($params['docRef']);
            }
        }

        return $article;
    }

    /**
     * @param $page
     * @param int|null $year
     * @return null|string
     */
    public function parseArticleList($page, ?int $year): ?string
    {
        $articleQuery = $this->articleService->findAllArticleByYear($year);
        $yearSpan = $this->articleService->findMinAndMaxYear();

        //Push the yearSpan into the navigation, only when we have a docRef
        if ($this->hasDocRef()) {
            $this->getUpdateNavigationService()
                ->updateNavigationWithYearSpan(
                    $this->getRouteMatch()->getParam('docRef'),
                    $yearSpan->minYear,
                    $yearSpan->maxYear,
                    $year
                );
        }

        $paginator = new Paginator(new PaginatorAdapter(new ORMPaginator($articleQuery)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 25);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $years = range($yearSpan->minYear, $yearSpan->maxYear);

        return $this->getRenderer()->render(
            'cms/press/list',
            [
                'paginator'    => $paginator,
                'years'        => $years,
                'selectedYear' => $year,
            ]
        );
    }

    /**
     * @param Article $article
     *
     * @return string
     */
    public function parseArticle(Article $article): string
    {
        return $this->getRenderer()->render(
            'cms/press/article',
            [
                'article' => $article,
            ]
        );
    }

    /**
     * @param Article $article
     * @return null|string
     */
    public function parseArticleProjectList(Article $article): string
    {
        return $this->getRenderer()->render(
            'press/partial/list/project',
            [
                'projects'       => $article->getProject(),
                'projectService' => $this->projectService,
            ]
        );
    }


    /**
     * @return string
     */
    public function parseYearSelector(): string
    {
        $yearSpan = $this->articleService->findMinAndMaxYear();
        $years = range($yearSpan->minYear, $yearSpan->maxYear);

        return $this->getRenderer()->render(
            'press/partial/year-selector',
            [
                'years'        => $years,
                'selectedYear' => $this->getRouteMatch()->getParam('year'),
            ]
        );
    }
}
