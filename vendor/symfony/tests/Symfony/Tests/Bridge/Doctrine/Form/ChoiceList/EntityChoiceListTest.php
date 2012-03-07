<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Bridge\Doctrine\Form\ChoiceList;

require_once __DIR__.'/../../DoctrineOrmTestCase.php';
require_once __DIR__.'/../../Fixtures/ItemGroupEntity.php';
require_once __DIR__.'/../../Fixtures/SingleIdentEntity.php';
require_once __DIR__.'/../../Fixtures/NoToStringSingleIdentEntity.php';

use Symfony\Tests\Bridge\Doctrine\DoctrineOrmTestCase;
use Symfony\Tests\Bridge\Doctrine\Fixtures\ItemGroupEntity;
use Symfony\Tests\Bridge\Doctrine\Fixtures\SingleIdentEntity;
use Symfony\Tests\Bridge\Doctrine\Fixtures\NoToStringSingleIdentEntity;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class EntityChoiceListTest extends DoctrineOrmTestCase
{
    const ITEM_GROUP_CLASS = 'Symfony\Tests\Bridge\Doctrine\Fixtures\ItemGroupEntity';

    const SINGLE_IDENT_CLASS = 'Symfony\Tests\Bridge\Doctrine\Fixtures\SingleIdentEntity';

    const COMPOSITE_IDENT_CLASS = 'Symfony\Tests\Bridge\Doctrine\Fixtures\CompositeIdentEntity';

    private $em;

    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->createTestEntityManager();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em = null;
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     * @expectedMessage   Entity "Symfony\Tests\Bridge\Doctrine\Fixtures\SingleIdentEntity" passed to the choice field must have a "__toString()" method defined (or you can also override the "property" option).
     */
    public function testEntitesMustHaveAToStringMethod()
    {
        $entity1 = new NoToStringSingleIdentEntity(1, 'Foo');
        $entity2 = new NoToStringSingleIdentEntity(2, 'Bar');

        // Persist for managed state
        $this->em->persist($entity1);
        $this->em->persist($entity2);

        $choiceList = new EntityChoiceList(
            $this->em,
            self::SINGLE_IDENT_CLASS,
            null,
            null,
            array(
                $entity1,
                $entity2,
            )
        );

        $choiceList->getValues();
    }

    /**
     * @expectedException Symfony\Component\Form\Exception\FormException
     */
    public function testChoicesMustBeManaged()
    {
        $entity1 = new SingleIdentEntity(1, 'Foo');
        $entity2 = new SingleIdentEntity(2, 'Bar');

        // no persist here!

        $choiceList = new EntityChoiceList(
            $this->em,
            self::SINGLE_IDENT_CLASS,
            'name',
            null,
            array(
                $entity1,
                $entity2,
            )
        );

        // triggers loading -> exception
        $choiceList->getChoices();
    }

    public function testFlattenedChoicesAreManaged()
    {
        $entity1 = new SingleIdentEntity(1, 'Foo');
        $entity2 = new SingleIdentEntity(2, 'Bar');

        // Persist for managed state
        $this->em->persist($entity1);
        $this->em->persist($entity2);

        $choiceList = new EntityChoiceList(
            $this->em,
            self::SINGLE_IDENT_CLASS,
            'name',
            null,
            array(
                $entity1,
                $entity2,
            )
        );

        $this->assertSame(array(1 => $entity1, 2 => $entity2), $choiceList->getChoices());
    }

    public function testEmptyChoicesAreManaged()
    {
        $entity1 = new SingleIdentEntity(1, 'Foo');
        $entity2 = new SingleIdentEntity(2, 'Bar');

        // Persist for managed state
        $this->em->persist($entity1);
        $this->em->persist($entity2);

        $choiceList = new EntityChoiceList(
            $this->em,
            self::SINGLE_IDENT_CLASS,
            'name',
            null,
            array()
        );

        $this->assertSame(array(), $choiceList->getChoices());
    }

    public function testNestedChoicesAreManaged()
    {
        $entity1 = new SingleIdentEntity(1, 'Foo');
        $entity2 = new SingleIdentEntity(2, 'Bar');

        // Oh yeah, we're persisting with fire now!
        $this->em->persist($entity1);
        $this->em->persist($entity2);

        $choiceList = new EntityChoiceList(
            $this->em,
            self::SINGLE_IDENT_CLASS,
            'name',
            null,
            array(
                'group1' => array($entity1),
                'group2' => array($entity2),
            )
        );

        $this->assertSame(array(1 => $entity1, 2 => $entity2), $choiceList->getChoices());
        $this->assertEquals(array(
            'group1' => array(1 => new ChoiceView('1', 'Foo')),
            'group2' => array(2 => new ChoiceView('2', 'Bar'))
        ), $choiceList->getRemainingViews());
    }

    public function testGroupBySupportsString()
    {
        $item1 = new ItemGroupEntity(1, 'Foo', 'Group1');
        $item2 = new ItemGroupEntity(2, 'Bar', 'Group1');
        $item3 = new ItemGroupEntity(3, 'Baz', 'Group2');
        $item4 = new ItemGroupEntity(4, 'Boo!', null);

        $this->em->persist($item1);
        $this->em->persist($item2);
        $this->em->persist($item3);
        $this->em->persist($item4);

        $choiceList = new EntityChoiceList(
            $this->em,
            self::ITEM_GROUP_CLASS,
            'name',
            null,
            array(
                $item1,
                $item2,
                $item3,
                $item4,
            ),
            'groupName'
        );

        $this->assertEquals(array(1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4), $choiceList->getChoices());
        $this->assertEquals(array(
            'Group1' => array(1 => new ChoiceView('1', 'Foo'), 2 => new ChoiceView('2', 'Bar')),
            'Group2' => array(3 => new ChoiceView('3', 'Baz')),
            4 => new ChoiceView('4', 'Boo!')
        ), $choiceList->getRemainingViews());
    }

    public function testGroupByInvalidPropertyPathReturnsFlatChoices()
    {
        $item1 = new ItemGroupEntity(1, 'Foo', 'Group1');
        $item2 = new ItemGroupEntity(2, 'Bar', 'Group1');

        $this->em->persist($item1);
        $this->em->persist($item2);

        $choiceList = new EntityChoiceList(
            $this->em,
            self::ITEM_GROUP_CLASS,
            'name',
            null,
            array(
                $item1,
                $item2,
            ),
            'child.that.does.not.exist'
        );

        $this->assertEquals(array(
            1 => $item1,
            2 => $item2
        ), $choiceList->getChoices());
    }

    public function testPossibleToProvideShorthandEntityName()
    {
        $shorthandName = 'SymfonyTestsDoctrine:SingleIdentEntity';

        $item1 = new SingleIdentEntity(1, 'Foo');
        $item2 = new SingleIdentEntity(2, 'Bar');

        $this->em->persist($item1);
        $this->em->persist($item2);

        $choiceList = new EntityChoiceList(
            $this->em,
            $shorthandName,
            null,
            null,
            null,
            null
        );

        $this->assertEquals(array(1, 2), $choiceList->getValuesForChoices(array($item1, $item2)));
        $this->assertEquals(array(1, 2), $choiceList->getIndicesForChoices(array($item1, $item2)));
    }
}
