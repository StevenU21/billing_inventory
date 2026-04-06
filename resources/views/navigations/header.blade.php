@auth
    @php
        /** @var \App\Models\User $user */
        $user = auth()->user();
    @endphp
@endauth

<header class="z-10 py-4 bg-gray-100 shadow-md dark:bg-gray-800">
    <div class="container flex items-center h-full px-6 mx-auto text-purple-600 dark:text-purple-300">
        <div class="flex items-center gap-3 min-w-0">
            <!-- Mobile hamburger -->
            <button class="p-1 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple"
                @click="toggleSideMenu" aria-label="Menu">
                <i class="fas fa-bars w-6 h-6"></i>
            </button>
            <!-- Desktop hamburger -->
            <button class="p-1 -ml-1 rounded-md hidden md:block focus:outline-none focus:shadow-outline-purple"
                @click="toggleSidebarCollapse" aria-label="Menu">
                <i class="fas fa-bars w-6 h-6"></i>
            </button>

        </div>

        <!-- Center intentionally empty (negative space) -->
        <div class="flex-1"></div>

        <ul class="flex items-center flex-shrink-0 gap-4 sm:gap-6">
            {{-- Cash Register Widget --}}
            @can('viewAny', \App\Models\CashRegisterSession::class)
                <li>
                    <x-cash-register-widget />
                </li>
            @endcan

            <li class="hidden sm:block">
                <div x-data="{
                        now: new Date(),
                        months: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                        formattedDateTime() {
                            const d = this.now;
                            const day = String(d.getDate()).padStart(2, '0');
                            const month = this.months[d.getMonth()];
                            const hours = String(d.getHours()).padStart(2, '0');
                            const minutes = String(d.getMinutes()).padStart(2, '0');
                            return `${day} ${month}, ${hours}:${minutes}`;
                        }
                    }" x-init="setInterval(() => now = new Date(), 1000)">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400"
                        x-text="formattedDateTime()"></span>
                </div>
            </li>

            <li class="relative">
                <button class="flex items-center gap-3 focus:shadow-outline-purple focus:outline-none"
                    @click="toggleProfileMenu" @keydown.escape="closeProfileMenu" aria-label="Account"
                    aria-haspopup="true">
                    @auth
                        <div class="hidden sm:flex flex-col items-end leading-tight">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ $user->short_name }}
                            </span>
                            @if ($user->formatted_role_name)
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->formatted_role_name }}
                                </span>
                            @endif
                        </div>
                        <img class="object-cover w-8 h-8 rounded-full"
                            src="{{ $user->profile?->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->short_name . ' ' . $user->last_name) . '&background=6D28D9&color=fff&size=128' }}"
                            alt="Avatar de {{ $user->first_name }} {{ $user->last_name }}" />
                    @else
                        <img class="object-cover w-8 h-8 rounded-full"
                            src="https://ui-avatars.com/api/?name=Usuario&background=6D28D9&color=fff&size=128"
                            alt="Avatar" />
                    @endauth
                </button>
                <ul x-show="isProfileMenuOpen" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    @click.outside="closeProfileMenu" @keydown.escape="closeProfileMenu"
                    class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:border-gray-700 dark:text-gray-300 dark:bg-gray-700"
                    aria-label="submenu" style="display: none;">

                    <li class="flex">
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                <i class="fas fa-sign-out-alt w-4 h-4 mr-3"></i>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>