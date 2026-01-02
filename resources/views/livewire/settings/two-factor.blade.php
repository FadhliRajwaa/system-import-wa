<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Two Factor Authentication')"
        :subheading="__('Add additional security to your account using two factor authentication.')"
    >
        <div class="space-y-6" wire:cloak>
            @if ($twoFactorEnabled)
                <div class="bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/20 rounded-xl p-5">
                    <div class="flex items-start gap-4">
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400">
                            <flux:icon name="shield-check" class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-emerald-800 dark:text-emerald-100">2FA is Enabled</h3>
                            <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">
                                {{ __('Your account is secure. You will be prompted for a secure code when logging in.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <flux:button variant="ghost" class="text-emerald-700 hover:bg-emerald-100" wire:click="regenerateRecoveryCodes">
                            Regenerate Codes
                        </flux:button>
                        <flux:button variant="ghost" class="text-emerald-700 hover:bg-emerald-100" wire:click="showRecoveryCodes">
                            Show Recovery Codes
                        </flux:button>
                        <flux:button variant="danger" wire:click="disable">
                            Disable 2FA
                        </flux:button>
                    </div>
                </div>

                @if($showingRecoveryCodes)
                    <div class="mt-6 p-4 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                        <h4 class="font-bold text-slate-900 dark:text-white mb-3">Recovery Codes</h4>
                        <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                            @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                                <div class="bg-white dark:bg-slate-900 p-2 rounded text-center select-all">{{ $code }}</div>
                            @endforeach
                        </div>
                        <div class="mt-3 text-xs text-slate-500">
                            Store these codes in a secure password manager. They can be used to recover access to your account if you lose your 2FA device.
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-xl p-5">
                     <div class="flex items-start gap-4">
                        <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg text-slate-500">
                            <flux:icon name="shield-exclamation" class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">2FA is Disabled</h3>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ __('When enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <flux:button variant="primary" wire:click="enable">
                            Enable Two-Factor Authentication
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    </x-settings.layout>

    <flux:modal name="two-factor-setup-modal" class="max-w-md" wire:model="showModal">
        <div class="space-y-6 text-center">
            <div>
                <flux:heading size="lg">{{ __('Setup 2FA') }}</flux:heading>
                <flux:subheading>{{ __('Scan the QR code using your authenticator app.') }}</flux:subheading>
            </div>

            @if ($qrCodeSvg)
                <div class="flex justify-center p-4 bg-white rounded-xl border border-slate-200">
                    {!! $qrCodeSvg !!}
                </div>
            @endif

            <div class="text-left bg-slate-50 p-3 rounded-lg border border-slate-200">
                <p class="text-xs text-slate-500 mb-1">Setup Key:</p>
                <code class="text-sm font-mono block break-all select-all">{{ $manualSetupKey }}</code>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="code" label="Enter OTP Code" placeholder="123456" class="text-center font-mono text-lg tracking-widest" />
                
                <div class="flex gap-2 justify-end">
                    <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>
                    <flux:button variant="primary" wire:click="confirmTwoFactor" :disabled="strlen($code) < 6">Confirm & Enable</flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</section>
