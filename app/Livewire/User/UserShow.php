<?php

declare(strict_types=1);

namespace App\Livewire\User;

use App\Models\User;

use Livewire\Component;

class UserShow extends Component
{
    


    public function render()
    {
       
        return view('livewire.user.user-show');
    }
}
