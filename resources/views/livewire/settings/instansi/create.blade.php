<div>
    <!-- Quill JS & CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <div class="space-y-6 animate-fade-in-up">
        <!-- Header with Back Button -->
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" icon="arrow-left" wire:click="cancel" class="hover:bg-slate-100 dark:hover:bg-slate-800" />
            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <flux:icon name="plus" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                    </div>
                    Tambah Instansi Baru
                </h1>
                <p class="text-slate-500 dark:text-slate-400 ml-0 sm:ml-15">Lengkapi data instansi baru</p>
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
                        <!-- Hidden input for Livewire binding -->
                        <input type="hidden" wire:model.blur="templateProlog" id="templatePrologInput" />
                        
                        <!-- Quill Editor Wrapper with specific styles -->
                        <div id="editorProlog" class="quill-editor-container"></div>
                        
                        <style>
                            .ql-toolbar {
                                display: block !important;
                                visibility: visible !important;
                                opacity: 1 !important;
                                position: relative !important;
                                z-index: 1000 !important;
                                border-bottom: 1px solid #e2e8f0 !important;
                            }
                            .ql-container {
                                font-family: inherit;
                                min-height: 250px;
                                border: 1px solid #e2e8f0;
                                border-top: none;
                            }
                            .quill-editor-container {
                                display: block;
                            }
                        </style>
                        
                        <p class="text-xs text-slate-500 mt-2">
                            Variabel yang tersedia: 
                            <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-indigo-600">@{{waktu}}</code>, 
                            <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-indigo-600">@{{no_lab}}</code>, 
                            <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-indigo-600">@{{nama_pasien}}</code>, 
                            <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-indigo-600">@{{pangkat}}</code>, 
                            <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-indigo-600">@{{nrp}}</code>, 
                            <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-indigo-600">@{{link}}</code>
                        </p>
                        <flux:error name="templateProlog" />
                    </flux:field>

                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                        <flux:button variant="ghost" wire:click="cancel">Batal</flux:button>
                        <flux:button variant="primary" wire:click="save" icon="check" class="shadow-xl shadow-indigo-500/25">
                            Simpan Instansi
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

            // Sync Quill content to hidden input on every change
            quillProlog.on('text-change', function() {
                document.getElementById('templatePrologInput').value = quillProlog.root.innerHTML;
            });
            
            // Only sync to Livewire when editor loses focus (prevents form disappearing)
            quillProlog.root.addEventListener('blur', function() {
                const wireId = document.querySelector('[wire\\:id]');
                if (wireId) {
                    Livewire.find(wireId.getAttribute('wire:id')).set('templateProlog', quillProlog.root.innerHTML);
                }
            });
        });
    </script>
</div>
