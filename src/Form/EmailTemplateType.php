<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Awarder;
use App\Entity\EmailTemplate;
use App\Repository\AwarderRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailTemplateType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('from')
            ->add('subject')
            ->add('template', TextareaType::class, [
                'attr' => [
                    'rows' => 10,
                ],
            ])
            ->add('deleteFiles', ChoiceType::class, [
                'label' => 'Delete Attachment',
                'choices' => [],
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'translation_domain' => false,
            ])
            ->add('attachments', FileType::class, [
                'label' => 'Add Attachment',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'translation_domain' => false,
            ])
            ->add('awarders', EntityType::class, [
                'placeholder' => 'Select awarders',
                'class' => Awarder::class,
                'query_builder' => static fn(AwarderRepository $er): QueryBuilder => $er->createQueryBuilder('a')
                    ->orderBy('a.name', 'ASC'),
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            $form = $event->getForm();

            $isDirty = false;
            $choices = $form->get('deleteFiles')->getConfig()->getOption('choices');
            $submittedOpts = $data['deleteFiles'] ?? [];
            foreach ($submittedOpts as $opt) {
                if (!\in_array($opt, $choices, true)) {
                    $choices[$opt] = $opt;
                    $isDirty = true;
                }
            }

            if ($isDirty) {
                $form->add('deleteFiles', ChoiceType::class, [
                    'choices' => $choices,
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                    'translation_domain' => false,
                ]);
            }
        });
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmailTemplate::class,
        ]);
    }
}
