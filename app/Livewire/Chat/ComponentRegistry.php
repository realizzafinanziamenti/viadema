<?php

namespace App\Livewire\Chat;

/** Keep WireChat's class-targeted events pointing at the extended components. */
class ComponentRegistry extends \Livewire\Mechanisms\ComponentRegistry
{
    private function resolveChatComponent($component)
    {
        $class = is_object($component) ? $component::class : $component;

        return match ($class) {
            \Namu\WireChat\Livewire\Chat\Chat::class => Chat::class,
            \Namu\WireChat\Livewire\Chat\Info::class => Info::class,
            \Namu\WireChat\Livewire\Chat\Group\Info::class => GroupInfo::class,
            default => $component,
        };
    }

    public function getName($nameOrClassOrComponent)
    {
        return parent::getName($this->resolveChatComponent($nameOrClassOrComponent));
    }

    public function new($nameOrClass, $id = null)
    {
        return parent::new($this->resolveChatComponent($nameOrClass), $id);
    }
}
