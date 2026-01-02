<div class="space-y-6 animate-fade-in-up">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" href="{{ route('participants.index') }}" icon="arrow-left">Kembali</flux:button>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $participant->name }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $participant->rank ?? '-' }} | {{ $participant->nrp_nip ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if($this->canSendWhatsApp)
                <flux:button variant="filled" icon="paper-airplane" wire:click="prepareWaMessage">Kirim WhatsApp</flux:button>
            @else
                <flux:button variant="subtle" icon="paper-airplane" disabled>
                    @if(!$participant->wa_opt_in_at)
                        Belum Opt-in
                    @elseif($participant->wa_status === 'sent')
                        Sudah Terkirim
                    @else
                        Tidak Dapat Dikirim
                    @endif
                </flux:button>
            @endif
            <flux:button variant="subtle" icon="arrow-up-tray" wire:click="$set('showUploadModal', true)">Upload File</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="user" class="w-5 h-5 text-slate-400" />
                        Informasi Peserta
                    </h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-white">{{ $participant->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pangkat</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $participant->rank ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">NRP/NIP</dt>
                            <dd class="mt-1 text-sm font-mono text-slate-700 dark:text-slate-300">{{ $participant->nrp_nip ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Jabatan</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $participant->position ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Satuan Kerja</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $participant->unit ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Gender</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $participant->gender ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal Lahir</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $participant->birth_date?->format('d/m/Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">No HP</dt>
                            <dd class="mt-1 text-sm font-mono text-slate-700 dark:text-slate-300">{{ $participant->phone_raw }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Examination Info -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="clipboard-document-check" class="w-5 h-5 text-slate-400" />
                        Informasi Pemeriksaan
                    </h3>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">No Lab</dt>
                            <dd class="mt-1 text-sm font-mono font-medium text-slate-900 dark:text-white">{{ $participant->lab_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Tanggal Pemeriksaan</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $participant->exam_date?->format('d/m/Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Paket</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $participant->package?->name ?? $participant->package_code ?? '-' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider">Instansi</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                    {{ $participant->company?->name ?? $participant->company_code ?? '-' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Attachments -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="paper-clip" class="w-5 h-5 text-slate-400" />
                        File Lampiran
                    </h3>
                    <flux:button variant="subtle" size="sm" icon="plus" wire:click="$set('showUploadModal', true)">Tambah</flux:button>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($participant->participantAttachments as $attachment)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="p-2 bg-slate-100 dark:bg-slate-800 rounded-lg shrink-0">
                                    <flux:icon name="document" class="w-5 h-5 text-slate-500" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $attachment->original_name }}</p>
                                    <p class="text-xs text-slate-500">{{ number_format($attachment->size / 1024, 2) }} KB | {{ $attachment->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <flux:button variant="ghost" size="sm" icon="arrow-down-tray" wire:click="downloadAttachment({{ $attachment->id }})">
                                    <span class="hidden sm:inline">Download</span>
                                </flux:button>
                                <flux:button variant="ghost" size="sm" icon="trash" wire:click="deleteAttachment({{ $attachment->id }})" wire:confirm="Yakin ingin menghapus file ini?">
                                    <span class="hidden sm:inline">Hapus</span>
                                </flux:button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="p-3 bg-slate-100 dark:bg-slate-800 rounded-full mb-3">
                                    <flux:icon name="paper-clip" class="w-6 h-6 text-slate-400" />
                                </div>
                                <p class="text-sm text-slate-500 mb-3">Belum ada file lampiran</p>
                                <flux:button variant="subtle" size="sm" icon="plus" wire:click="$set('showUploadModal', true)">Upload File</flux:button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- WhatsApp Status -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="chat-bubble-left-right" class="w-5 h-5 text-slate-400" />
                        Status WhatsApp
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Status</span>
                        @php
                            $statusClasses = $this->waStatusBadgeClass[$participant->wa_status] ?? 'bg-gray-100 text-gray-800';
                            $statusLabel = $this->waStatusLabel[$participant->wa_status] ?? ucfirst($participant->wa_status);
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Opt-in</span>
                        @if($participant->wa_opt_in_at)
                            <span class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">{{ $participant->wa_opt_in_at->format('d/m/Y') }}</span>
                        @else
                            <span class="text-sm text-slate-400">Belum opt-in</span>
                        @endif
                    </div>
                    @if($participant->wa_sent_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Terkirim</span>
                            <span class="text-sm text-slate-700 dark:text-slate-300">{{ $participant->wa_sent_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                    @if($participant->wa_last_error)
                        <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <p class="text-xs text-red-700 dark:text-red-300">{{ $participant->wa_last_error }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- WhatsApp History -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="clock" class="w-5 h-5 text-slate-400" />
                        Riwayat Pengiriman
                    </h3>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-80 overflow-y-auto">
                    @forelse($participant->waMessages as $message)
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-500">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                                @php
                                    $msgStatusClasses = match($message->status) {
                                        'sent' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                        'failed' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                        default => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $msgStatusClasses }}">
                                    {{ ucfirst($message->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ Str::limit($message->message_body, 100) }}</p>
                            @if($message->error_message)
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message->error_message }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center">
                            <p class="text-sm text-slate-500">Belum ada riwayat pengiriman</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Metadata -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white flex items-center gap-2">
                        <flux:icon name="information-circle" class="w-5 h-5 text-slate-400" />
                        Metadata
                    </h3>
                </div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Phone E164</span>
                        <span class="text-slate-700 dark:text-slate-300 font-mono text-xs">{{ $participant->phone_e164 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Dibuat</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $participant->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Diperbarui</span>
                        <span class="text-slate-700 dark:text-slate-300">{{ $participant->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <flux:modal wire:model="showUploadModal" class="max-w-md">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Upload File Lampiran</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Pilih File</label>
                    <input type="file" wire:model="attachmentFile" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/20 dark:file:text-brand-400" />
                    @error('attachmentFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <div wire:loading wire:target="attachmentFile" class="flex items-center gap-2 text-sm text-slate-500">
                    <flux:icon name="arrow-path" class="w-4 h-4 animate-spin" />
                    Mengupload...
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showUploadModal', false)">Batal</flux:button>
                <flux:button variant="filled" wire:click="uploadAttachment" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadAttachment">Upload</span>
                    <span wire:loading wire:target="uploadAttachment">Uploading...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- WhatsApp Preview Modal -->
    <flux:modal wire:model="showWaModal" class="max-w-lg">
        <div class="p-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <flux:icon name="paper-airplane" class="w-5 h-5 text-emerald-500" />
                Preview Pesan WhatsApp
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Penerima</label>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-lg">
                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                            <flux:icon name="user" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <p class="font-medium text-slate-900 dark:text-white">{{ $participant->name }}</p>
                            <p class="text-sm text-slate-500 font-mono">{{ $participant->phone_e164 }}</p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Isi Pesan</label>
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-100 dark:border-emerald-800">
                        <pre class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap font-sans">{{ $waMessagePreview }}</pre>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showWaModal', false)">Batal</flux:button>
                <flux:button variant="filled" wire:click="sendWhatsApp" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="sendWhatsApp">Kirim WhatsApp</span>
                    <span wire:loading wire:target="sendWhatsApp">Mengirim...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
