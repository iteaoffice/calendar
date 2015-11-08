<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category   Calendar
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright  2004-2015 ITEA Office
 * @license    https://itea3.org/license.txt proprietary
 *
 * @link       https://itea3.org
 */

namespace Calendar\Controller\Plugin;

/**
 * Create a link to an project.
 *
 * @category   Calendar
 *
 * @author     Johan van der Heide <johan.van.der.heide@itea3.org>
 * @license    https://itea3.org/licence.txt proprietary
 *
 * @link       https://itea3.org
 */
class CalendarPdf extends \FPDI
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
        if (is_null($this->_tplIdx)) {
            if (!file_exists($this->template)) {
                throw new \InvalidArgumentException(sprintf("Template %s cannot be found", $this->template));
            }
            $this->setSourceFile($this->template);
            $this->_tplIdx = $this->importPage(1);
        }
        $this->SetTopMargin(35);
        $this->useTemplate($this->_tplIdx, 0, 0);
        $this->SetFont('freesans', 'N', 15);
        $this->SetTextColor(0);
        $this->SetXY(15, 5);
    }

    public function footer()
    {
        // emtpy method body
    }

    /**
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
