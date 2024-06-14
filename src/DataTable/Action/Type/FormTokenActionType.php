<?php

declare(strict_types=1);

namespace App\DataTable\Action\Type;

use Kreyu\Bundle\DataTableBundle\Action\ActionInterface;
use Kreyu\Bundle\DataTableBundle\Action\ActionView;
use Kreyu\Bundle\DataTableBundle\Action\Type\AbstractActionType;
use Kreyu\Bundle\DataTableBundle\Action\Type\FormActionType;
use Kreyu\Bundle\DataTableBundle\Column\ColumnValueView;
use Kreyu\Bundle\DataTableBundle\DataTableView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormTokenActionType extends AbstractActionType
{
    /**
     * @param array<array-key, mixed> $options
     */
    #[\Override]
    public function buildView(ActionView $view, ActionInterface $action, array $options): void
    {
        if ($view->parent instanceof ColumnValueView) {
            $value = $view->parent->value;

            foreach (['method', 'action', 'button_attr', 'token_id'] as $optionName) {
                if (is_callable($options[$optionName])) {
                    $options[$optionName] = $options[$optionName]($value);
                }
            }
        }

        $method = strtoupper((string) $options['method']);
        $htmlFriendlyMethod = $method;

        if ('GET' !== $method) {
            $htmlFriendlyMethod = 'POST';
        }

        $view->vars = array_replace($view->vars, [
            'method' => $method,
            'html_friendly_method' => $htmlFriendlyMethod,
            'action' => $options['action'],
            'button_attr' => $options['button_attr'],
            'token_id' => $options['token_id'],
            'form_id' => $this->getFormId($view, $action),
        ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'method' => 'GET',
                'action' => '#',
                'button_attr' => [],
            ])
            ->setRequired('token_id')
            ->setAllowedTypes('token_id', ['string', 'callable'])
            ->setAllowedTypes('method', ['string', 'callable'])
            ->setAllowedTypes('action', ['string', 'callable'])
            ->setAllowedTypes('button_attr', ['array', 'callable'])
        ;
    }

    private function getFormId(ActionView $view, ActionInterface $action): string
    {
        /** @var DataTableView $dataTable */
        $dataTable = $view->vars['data_table'];

        $formId = $dataTable->vars['name'].'-action-'.$action->getName().'-form';

        if ($view->parent instanceof ColumnValueView) {
            $formId .= '-'.$view->parent->parent->index;
        }

        return $formId;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return FormActionType::class;
    }
}
