<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

$options = [
    'calendar_contact_template' => __DIR__ . '/../../../../styles/itea/template/pdf/blank-template.pdf',
    'review_calendar_template'  => __DIR__ . '/../../../../styles/itea/template/pdf/review-calendar-template.pdf',
];

return [
    'calendar_option' => $options,
];
