<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ConfirmDelete extends Component
{
    public string $id;

    public string $action;

    public string $title;

    public string $message;

    public string $method;

    public ?string $methodOverride = null;

    public string $confirmText;

    public string $cancelText;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $id = 'confirm-delete-modal',
        string $action = '',
        ?string $title = null,
        ?string $message = null,
        string $method = 'DELETE',
        ?string $confirmText = null,
        ?string $cancelText = null
    ) {
        $this->id = $id;
        $this->action = $action;
        $this->title = $title ?? __('messages.confirm_delete_title');
        $this->message = $message ?? __('messages.confirm_delete_message');
        $this->method = strtoupper($method);
        $this->methodOverride = ! in_array($this->method, ['GET', 'POST']) ? $this->method : null;
        $this->confirmText = $confirmText ?? __('messages.delete');
        $this->cancelText = $cancelText ?? __('messages.cancel');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.confirm-delete');
    }
}
