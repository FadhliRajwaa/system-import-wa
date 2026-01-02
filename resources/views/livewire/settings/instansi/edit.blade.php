<div>
<div class="space-y-6 animate-fade-in-up">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" icon="arrow-left" wire:click="cancel" class="hover:bg-slate-100 dark:hover:bg-slate-800" />
        <div class="space-y-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <flux:icon name="pencil-square" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                </div>
                Edit Instansi
            </h1>
            <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Update informasi instansi: {{ $instansi->nama }}</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg shadow-slate-900/5 dark:shadow-slate-950/50 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="max-w-2xl space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <flux:field>
                        <flux:label>Kode Instansi <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="kode" placeholder="Contoh: A001" icon="qr-code" />
                        <flux:error name="kode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Nama Instansi <span class="text-red-500">*</span></flux:label>
                        <flux:input wire:model="nama" placeholder="Contoh: KORPS BRIMOB POLRI" icon="building-office" />
                        <flux:error name="nama" />
                    </flux:field>
                </div>

                <flux:field wire:ignore>
                    <flux:label>Template Pesan WA <span class="text-red-500">*</span></flux:label>
                    <!-- Hidden input for Livewire binding with blur -->
                    <input type="hidden" wire:model.blur="templateProlog" id="templatePrologInput" />
                    
                    <!-- Quill Editor -->
                    <div id="editorProlog" style="min-height: 250px;"></div>
                    
                    <p class="text-xs text-slate-500 mt-2">
                        Variabel yang tersedia: 
                        <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-amber-600">@{{waktu}}</code>, 
                        <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-amber-600">@{{no_lab}}</code>, 
                        <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-amber-600">@{{nama_pasien}}</code>, 
                        <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-amber-600">@{{pangkat}}</code>, 
                        <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-amber-600">@{{nrp}}</code>, 
                        <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-amber-600">@{{link}}</code>
                    </p>
                    <flux:error name="templateProlog" />
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

<!-- Quill JS & CSS -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
    document.addEventListener('livewire:init', function() {
        // Initialize Quill Editor for Template Pesan
        const quillProlog = new Quill('#editorProlog', {
            theme: 'snow',
            placeholder: 'Selamat @{{waktu}} Bapak/Ibu, Kami dari Lab Klinik mengirimkan hasil rikkes...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Load initial content for edit - set as plain text
        quillProlog.setText(@js($templateProlog));

        // Sync Quill content to Livewire on blur (not on every keystroke)
        // This prevents form from disappearing during typing
        quillProlog.on('text-change', function() {
            document.getElementById('templatePrologInput').value = quillProlog.getText().trim();
        });

        // Only sync to Livewire when editor loses focus (blur)
        quillProlog.root.addEventListener('blur', function() {
            const wireId = document.querySelector('[wire\\:id]');
            if (wireId) {
                Livewire.find(wireId.getAttribute('wire:id')).set('templateProlog', quillProlog.getText().trim());
            }
        });
    });
</script>
</div>
