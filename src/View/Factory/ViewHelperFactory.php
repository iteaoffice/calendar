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

namespace Calendar\View\Factory;

use Application\Service\AssertionService;
use Calendar\View\Helper\AbstractViewHelper;
use Interop\Container\ContainerInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfcTwig\View\TwigRenderer;

/**
 * Class ViewHelperFactory
 *
 * @package Calendar\View\Factory
 */
final class ViewHelperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AbstractViewHelper
    {
        /** @var AbstractViewHelper $viewHelper */
        return new $requestedName(
            $container->get('application'),
            $container->get('ViewHelperManager'),
            $container->get(AssertionService::class),
            $container->get(TwigRenderer::class),
            $container->get(TranslatorInterface::class)
        );
    }
}
