<?php

namespace App\Traits;

trait InteractsWithDropdowns
{
    /**
     * Set value for select input.
     */
    protected function setSelectValue(string $valueProp, ?int $value): void
    {
        $this->{$valueProp} = $value;

        // Reset the page if the component is paginated
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    /**
     * Set value and form value for select input.
     */
    protected function setFormSelectValue(string $formProp, ?int $value, string $form = 'form'): void
    {
        $this->{$form}->{$formProp} = $value;

        // Reset the page if the component is paginated
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }
}
