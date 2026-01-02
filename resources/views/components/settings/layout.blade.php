<div class="flex flex-col md:flex-row items-start gap-8 max-w-6xl mx-auto">
    <!-- Sidebar Navigation -->
    <div class="w-full md:w-64 flex-shrink-0">
        <div class="sticky top-6">
            <div class="md:hidden mb-6">
                <flux:select variant="listbox" placeholder="Select setting..." x-on:change="window.location.href = $event.target.value">
                    <flux:option value="{{ route('profile.edit') }}" :selected="request()->routeIs('profile.edit')">Profile</flux:option>
                    <flux:option value="{{ route('user-password.edit') }}" :selected="request()->routeIs('user-password.edit')">Password</flux:option>
                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <flux:option value="{{ route('two-factor.show') }}" :selected="request()->routeIs('two-factor.show')">Two-Factor Auth</flux:option>
                    @endif
                    <flux:option value="{{ route('appearance.edit') }}" :selected="request()->routeIs('appearance.edit')">Appearance</flux:option>
                    <flux:option value="{{ route('settings.saungwa') }}" :selected="request()->routeIs('settings.saungwa')">SaungWA API</flux:option>
                </flux:select>
            </div>

            <div class="hidden md:block bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <p class="px-2 mb-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Account</p>
                <flux:navlist>
                    <flux:navlist.item :href="route('profile.edit')" wire:navigate icon="user">
                        {{ __('Profile') }}
                    </flux:navlist.item>
                    <flux:navlist.item :href="route('user-password.edit')" wire:navigate icon="lock-closed">
                        {{ __('Password') }}
                    </flux:navlist.item>
                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <flux:navlist.item :href="route('two-factor.show')" wire:navigate icon="shield-check">
                            {{ __('Two-Factor Auth') }}
                        </flux:navlist.item>
                    @endif
                    <flux:navlist.item :href="route('appearance.edit')" wire:navigate icon="paint-brush">
                        {{ __('Appearance') }}
                    </flux:navlist.item>
                    <flux:navlist.item :href="route('settings.saungwa')" wire:navigate icon="chat-bubble-left-right">
                        {{ __('SaungWA API') }}
                    </flux:navlist.item>
                </flux:navlist>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 w-full min-w-0">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                <flux:heading size="lg" level="1">{{ $heading ?? '' }}</flux:heading>
                <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            </div>
            
            <div class="p-6 md:p-8">
                <div class="max-w-xl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
