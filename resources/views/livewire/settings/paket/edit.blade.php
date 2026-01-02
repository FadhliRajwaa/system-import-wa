<div class="space-y-6 animate-fade-in-up">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" wire:click="cancel" class="hover:bg-slate-100 dark:hover:bg-slate-800" />
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <flux:icon name="pencil-square" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                Edit Paket
            </h1>
            <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Update informasi paket: {{ $paket->nama }}</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="max-w-xl space-y-6">
                <flux:field>
                    <flux:label>Kode Paket</flux:label>
                    <flux:input wire:model="kode" placeholder="Contoh: PKT001" icon="qr-code" />
                    <flux:error name="kode" />
                </flux:field>

                <flux:field>
                    <flux:label>Nama Paket</flux:label>
                    <flux:input wire:model="nama" placeholder="Contoh: Paket Pemeriksaan Lengkap" icon="cube" />
                    <flux:error name="nama" />
                </flux:field>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <flux:button variant="ghost" wire:click="cancel">Batal</flux:button>
                    <flux:button variant="primary" wire:click="save" icon="check" class="shadow-xl shadow-amber-500/25">
                        Simpan Perubahan
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
