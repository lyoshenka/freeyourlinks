<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\Extension\Core\EventListener\MergeCollectionListener;

class CollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        if ($options['allow_add'] && $options['prototype']) {
            $prototype = $builder->create($options['prototype_name'], $options['type'], $options['options']);
            $builder->setAttribute('prototype', $prototype->getForm());
        }

        $resizeListener = new ResizeFormListener(
            $builder->getFormFactory(),
            $options['type'],
            $options['options'],
            $options['allow_add'],
            $options['allow_delete']
        );

        $builder
            ->addEventSubscriber($resizeListener)
            ->setAttribute('allow_add', $options['allow_add'])
            ->setAttribute('allow_delete', $options['allow_delete'])
        ;

        // Enable support for adders/removers unless "by_reference" is disabled
        // (explicit calling of the setter is desired)
        if ($options['by_reference']) {
            $builder->addEventSubscriber(new MergeCollectionListener(
                $options['allow_add'],
                $options['allow_delete'],
                MergeCollectionListener::MERGE_INTO_PARENT,
                $options['add_method'],
                $options['remove_method']
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $view
            ->set('allow_add', $form->getAttribute('allow_add'))
            ->set('allow_delete', $form->getAttribute('allow_delete'))
        ;

        if ($form->hasAttribute('prototype')) {
            $view->set('prototype', $form->getAttribute('prototype')->createView($view));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildViewBottomUp(FormView $view, FormInterface $form)
    {
        if ($form->hasAttribute('prototype') && $view->get('prototype')->get('multipart')) {
            $view->set('multipart', true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'allow_add'      => false,
            'allow_delete'   => false,
            'add_method'     => null,
            'remove_method'  => null,
            'prototype'      => true,
            'prototype_name' => '__name__',
            'type'           => 'text',
            'options'        => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'collection';
    }
}
