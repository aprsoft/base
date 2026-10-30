import './bootstrap';

// 1. Importar y exponer Livewire PRIMERO
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js';
import permissionSelector from './components/permission-selector.js';
window.Livewire = Livewire;
window.Alpine = Alpine;



// 2. Iniciar Livewire (esto también inicia Alpine automáticamente)
Alpine.data('permissionSelector', permissionSelector);
Livewire.start();


// 3. Importar PowerGrid (ya encontrará window.Livewire y window.Alpine)
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';

// NO llamar Alpine.start() - Livewire ya lo hizo

import flatpickr from 'flatpickr';
window.flatpickr = flatpickr;