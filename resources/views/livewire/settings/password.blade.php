<section class="w-full max-w-4xl mx-auto animate-fade-in-up">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Ganti Password</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Pastikan akun Anda aman dengan menggunakan password yang kuat.</p>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 md:p-8">
            <form method="POST" wire:submit="updatePassword" class="space-y-6 max-w-xl">
                
                <flux:field>
                    <flux:label>Password Saat Ini</flux:label>
                    <flux:input wire:model="current_password" type="password" required autocomplete="current-password" viewable />
                    <flux:error name="current_password" />
                </flux:field>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:field>
                        <flux:label>Password Baru</flux:label>
                        <flux:input wire:model="password" type="password" required autocomplete="new-password" viewable />
                        <flux:error name="password" />
                    </flux:field>
                    
                    <flux:field>
                        <flux:label>Konfirmasi Password</flux:label>
                        <flux:input wire:model="password_confirmation" type="password" required autocomplete="new-password" viewable />
                        <flux:error name="password_confirmation" />
                    </flux:field>
                </div>

                <div class="pt-4 flex items-center gap-4">
                    <flux:button variant="primary" type="submit" class="shadow-lg shadow-brand-500/20">Update Password</flux:button>
                    <x-action-message class="text-sm text-emerald-600 dark:text-emerald-400 flex items-center gap-1" on="password-updated">
                        <flux:icon name="check" class="w-4 h-4" /> Password Berhasil Diubah
                    </x-action-message>
                </div>
            </form>
        </div>
    </div>
</section>