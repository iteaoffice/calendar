<?php
/**
 * ITEA copyright message placeholder
 *
 * @category    CalendarTest
 * @package     Entity
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

namespace CalendarTest\Entity;

use PHPUnit\Framework\TestCase;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Element;

class EntityTest extends TestCase
{
    public function testCanCreateEntitiesAndSaveTxtFields(): void
    {
        $labels = [];
        foreach (glob(__DIR__ . '/../../src/Entity/*.php') as $file) {

            // get the file name of the current file without the extension
            // which is essentially the class name
            $class = basename($file, '.php');
            $className = 'Calendar\Entity\\' . $class;

            $testClass = new \ReflectionClass($className);

            if ($testClass->isInstantiable()) {
                $object = new $className();

                $this->assertInstanceOf($className, $object);

                $builder = new AnnotationBuilder();
                $dataFieldset = $builder->createForm($object);

                /** @var Element $element */
                foreach ($dataFieldset->getElements() as $element) {

                    // Add only when a type is provided
                    if (!array_key_exists('type', $element->getAttributes())) {
                        continue;
                    }

                    if (isset($element->getAttributes()['label'])) {
                        $labels[] = $element->getAttributes()['label'];
                    }
                    if (isset($element->getAttributes()['help-block'])) {
                        $labels[] = $element->getAttributes()['help-block'];
                    }
                    if (isset($element->getAttributes()['placeholder'])) {
                        $labels[] = $element->getAttributes()['placeholder'];
                    }
                    if (isset($element->getOptions()['label'])) {
                        $labels[] = $element->getOptions()['label'];
                    }
                    if (isset($element->getOptions()['help-block'])) {
                        $labels[] = $element->getOptions()['help-block'];
                    }
                    if (isset($element->getOptions()['placeholder'])) {
                        $labels[] = $element->getOptions()['placeholder'];
                    }

                    $this->assertIsArray($element->getAttributes());
                    $this->assertIsArray($element->getOptions());
                }

                foreach ($testClass->getStaticProperties() as $constant) {
                    if (\is_array($constant)) {
                        foreach ($constant as $constantValue) {
                            $labels[] = $constantValue;
                        }
                    }
                }
            }
        }

        file_put_contents(
            __DIR__ . '/../../config/language.php',
            "<?php\n_('" . implode("');\n_('", array_unique($labels)) . "');\n"
        );
    }
}
