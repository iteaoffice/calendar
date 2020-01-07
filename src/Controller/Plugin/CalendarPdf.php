<?php
/**
 * ITEA Office all rights reserved
 *
 * @category   Calendar
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

declare(strict_types=1);

namespace Calendar\Controller\Plugin;

use setasign\Fpdi\TcpdfFpdi;

/**
 * Class CalendarPdf
 *
 * @package Calendar\Controller\Plugin
 */
class CalendarPdf extends TcpdfFpdi
{
    /**
     * "Remembers" the template id of the imported page.
     */
    protected $_tplIdx;
    /**
     * @var string Location of the template
     */
    protected $template;

    /**
     * Draw an imported PDF logo on every page.
     */
    public function header()
    {
        if (null === $this->_tplIdx) {
            if (! file_exists($this->template)) {
                throw new \InvalidArgumentException(sprintf("Template %s cannot be found", $this->template));
            }
            $this->setSourceFile($this->template);
            $this->_tplIdx = $this->importPage(1);
        }
        $this->SetTopMargin(35);
        $this->useTemplate($this->_tplIdx);
        $this->SetTextColor(0);
        $this->SetXY(15, 5);
    }

    public function footer(): void
    {
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }
}
