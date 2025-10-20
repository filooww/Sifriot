<?php

declare(strict_types=1);

namespace App\Livewire\User;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UserProfile extends Component
{
    public function render()
    {
        return view('livewire.user.user-profile');
    }
}
