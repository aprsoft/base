
<div
    class="relative"
    x-data="{
        dropdownOpen: false,
        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;
        },
        closeDropdown() {
            this.dropdownOpen = false;
        }
    }"
    @click.away="closeDropdown()"
>
    <!-- Botón del usuario -->
    <button
        class="flex items-center text-gray-700 dark:text-gray-400"
        @click.prevent="toggleDropdown()"
        type="button"
    >
        <span
            class="flex items-center justify-center text-center mr-3 overflow-hidden rounded-full h-11 w-11 bg-gray-200 text-black dark:bg-gray-700 dark:text-white"
        >
            {{ Auth::user()->initials() }}
        </span>

        <span class="block mr-1 font-medium text-theme-sm">
            {{ Auth::user()->name }}
        </span>

        <svg
            class="w-5 h-5 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 9l-7 7-7-7"
            />
        </svg>
    </button>

    <!-- Dropdown -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-100 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        <!-- Información del usuario -->
        <div>
            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
                {{ Auth::user()->name }}
            </span>

            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
                {{ Auth::user()->email }}
            </span>
        </div>

        @php
            $user = Auth::user();

            $canViewProfile = $user->can(
                'profile.view',
                $user
            );

            $canChangePassword = $user->can(
                'profile.changePassword',
                $user
            );

            $profileMenuItem = null;

            if ($canViewProfile) {
                $profileMenuItem = [
                    'text' => 'Editar perfil',
                    'path' => route('settings.profile.edit'),
                ];
            } elseif ($canChangePassword) {
                $profileMenuItem = [
                    'text' => 'Cambiar contraseña',
                    'path' => route('settings.password.edit'),
                ];
            }
        @endphp

        <!-- Opciones autorizadas -->
        @if ($profileMenuItem)
            <ul class="flex flex-col gap-1 pt-4 pb-3 border-b border-gray-200 dark:border-gray-800">
                <li>
                    <a
                        href="{{ $profileMenuItem['path'] }}"
                        class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                    >
                        <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                            <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 5.5 12 5.5C7.30558 5.5 3.5 9.30558 3.5 14C3.5 15.1526 3.8 16.1184 4.3 17.1C5.5 14.2 8.3 12.3 12 12.3C15.7 12.3 18.5 14.2 19.7 17.1C20.2 16.1184 20.5 15.1526 20.5 14C20.5 9.30558 16.6944 5.5 12 5.5Z"
                                    fill="currentColor"
                                />
                                <circle
                                    cx="12"
                                    cy="8"
                                    r="3"
                                    fill="currentColor"
                                />
                            </svg>
                        </span>

                        {{ $profileMenuItem['text'] }}
                    </a>
                </li>
            </ul>
        @endif

        <!-- Cerrar sesión -->
        <form
            method="POST"
            action="{{ route('logout') }}"
        >
            @csrf

            <button
                type="submit"
                class="flex items-center w-full gap-3 px-3 py-2 mt-3 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            >
                <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 013-3v1"
                        />
                    </svg>
                </span>

                Cerrar sesión
            </button>
        </form>
    </div>
</div>
