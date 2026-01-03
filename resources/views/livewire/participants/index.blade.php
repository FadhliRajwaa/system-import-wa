<div class="space-y-6"
    x-data
    x-init="
        @if(session('toast'))
            $dispatch('show-toast', { type: '{{ session('toast.type') }}', message: '{{ session('toast.message') }}' });
        @endif
    "
    @open-wa-links.window="($event.detail.urls || []).forEach((url, index) => setTimeout(() => window.open(url, '_blank'), index * 500))">
    
    <!-- Header -->
    <div class="flex flex-wrap gap-4 items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Data Peserta</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola data peserta pemeriksaan kesehatan</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            {{-- Delete Selected Button (shows when rows selected) --}}
            @if(count($selectedRows) > 0)
                <flux:button variant="danger" icon="trash" wire:click="confirmDeleteSelected">
                    Hapus ({{ count($selectedRows) }})
                </flux:button>
            @endif
            <flux:button variant="subtle" icon="arrow-down-tray" wire:click="exportData">Export</flux:button>
            <flux:button variant="primary" wire:click="openBulkWaModal">
                <svg class="w-4 h-4 mr-1.5 -ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                WA Blast
            </flux:button>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        
    <!-- Toolbar -->
    <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
        <div class="flex flex-col lg:flex-row gap-4 items-center">
            <!-- Per Page Selector -->
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">Tampilkan</span>
                <select 
                    wire:model.live="perPage" 
                    class="block w-20 rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-sm py-2"
                >
                    <option value="10">10</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="-1">Semua</option>
                </select>
                <span class="text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">data</span>
            </div>
            
            <!-- Search -->
            <div class="w-full lg:flex-1 relative">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <flux:icon name="magnifying-glass" class="h-5 w-5 text-slate-400" />
                    </div>
                    <input 
                        type="text"
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Cari nama, NRP/NIP, No Lab, atau No HP..." 
                        class="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-sm"
                    />
                </div>
            </div>
            
            <!-- Filter Actions -->
            <div class="flex items-center gap-2 w-full lg:w-auto justify-between lg:justify-start">
                <div x-data="{ open: false }" class="relative w-full lg:w-auto">
                    <flux:button 
                        variant="subtle" 
                        icon="funnel" 
                        @click="open = !open"
                        class="w-full lg:w-auto shadow-sm"
                        x-bind:class="open ? 'bg-slate-200 dark:bg-slate-700' : ''"
                    >
                        Filter
                        @if(!empty(array_filter($filters)))
                            <span class="ml-2 px-1.5 py-0.5 text-[10px] font-bold bg-brand-500 text-white rounded-full leading-none">
                                {{ count(array_filter($filters)) }}
                            </span>
                        @endif
                    </flux:button>

                    <!-- Advanced Filters Dropdown/Collapsible -->
                    <div 
                        x-show="open" 
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute right-0 mt-2 w-full lg:w-[800px] z-50 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6"
                        style="display: none;"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Date Filter -->
                            <flux:field>
                                <flux:label class="text-xs font-bold uppercase text-slate-500 mb-2">Tanggal Pemeriksaan</flux:label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <flux:icon name="calendar" class="h-4 w-4 text-slate-400" />
                                    </div>
                                    <input 
                                        type="date" 
                                        wire:model.live="filters.tanggal_mulai" 
                                        class="pl-9 block w-full rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:border-brand-500 focus:ring-brand-500 dark:text-white shadow-sm transition-all"
                                    >
                                    @if($filters['tanggal_mulai'])
                                        <button wire:click="$set('filters.tanggal_mulai', null)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-red-500">
                                            <flux:icon name="x-mark" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </flux:field>

                            <!-- Satuan Kerja Filter -->
                            <flux:field>
                                <flux:label class="text-xs font-bold uppercase text-slate-500 mb-2">Satuan Kerja</flux:label>
                                <flux:select wire:model.live="filters.satuan_kerja" placeholder="Semua Satuan Kerja">
                                    <option value="">Semua Satuan Kerja</option>
                                    @foreach($satuanKerjaList as $satker)
                                        <option value="{{ $satker }}">{{ $satker }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                            
                            <!-- File Status Filter -->
                            <flux:field>
                                <flux:label class="text-xs font-bold uppercase text-slate-500 mb-2">Status File</flux:label>
                                <flux:select wire:model.live="filters.status_pdf" placeholder="Semua File">
                                    <option value="">Semua File</option>
                                    <option value="uploaded">Ada PDF</option>
                                    <option value="not_uploaded">Belum Ada PDF</option>
                                </flux:select>
                            </flux:field>
                            
                            <!-- Status WA -->
                            <flux:field>
                                <flux:label class="text-xs font-bold uppercase text-slate-500 mb-2">Status WA</flux:label>
                                <flux:select wire:model.live="filters.status_wa" placeholder="Semua Status">
                                    <option value="">Semua Status</option>
                                    <option value="belum_kirim">Belum Kirim</option>
                                    <option value="success">Sukses</option>
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="mt-8 pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                            @if($search || !empty(array_filter($filters)))
                                <flux:button wire:click="resetFilters" variant="subtle" size="sm" class="text-red-600 hover:text-red-700">
                                    Reset Semua Filter
                                </flux:button>
                            @else
                                <div></div>
                            @endif
                            <flux:button variant="primary" size="sm" @click="open = false">Terapkan Filter</flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
        
        <!-- Bulk Actions -->
        @if(count($selectedRows) > 0)
            <div class="px-4 py-3 bg-brand-50 dark:bg-brand-900/20 border-b border-brand-100 dark:border-brand-800 flex items-center justify-between">
                <p class="text-sm font-medium text-brand-700 dark:text-brand-300">
                    <span class="font-bold">{{ count($selectedRows) }}</span> terpilih
                </p>
                <div class="flex gap-2">
                    <flux:button variant="filled" size="sm" wire:click="queueBulkWa" icon="paper-airplane">Kirim WA</flux:button>
                    <flux:button variant="ghost" size="sm" wire:click="$set('selectedRows', [])">Batal</flux:button>
                </div>
            </div>
        @endif

        <!-- Table with optimized scroll -->
        <div class="relative">
            <!-- Mobile scroll hint -->
            <div class="lg:hidden absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-white dark:from-slate-900 to-transparent pointer-events-none z-10"></div>
            
            <div class="overflow-x-auto scroll-smooth overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
                <table class="w-full text-sm text-left table-auto">
                    <thead class="text-xs uppercase bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-10">
                        <tr class="text-slate-700 dark:text-slate-300 font-bold">
                        <th class="px-4 py-3 w-10 font-bold text-xs">
                            <input 
                                type="checkbox" 
                                wire:model.live="selectAll"
                                class="w-5 h-5 rounded border-2 border-gray-400 bg-white checked:bg-blue-600 checked:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all"
                                title="Pilih semua data di halaman ini"
                            />
                        </th>
                        <th class="px-4 py-3 w-12 font-bold text-xs text-center">No</th>
                        <th class="px-4 py-3 cursor-pointer hover:text-brand-600 transition-colors font-bold text-xs" wire:click="sortBy('nama')">
                            <div class="flex items-center gap-1">
                                Nama
                                @if($sortField === 'nama')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                @else
                                    <flux:icon name="arrows-up-down" class="w-3 h-3 text-slate-400 opacity-50" />
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 font-bold text-xs">Pangkat</th>
                        <th class="px-4 py-3 font-bold text-xs">NRP/NIP</th>
                        <th class="px-4 py-3 font-bold text-xs">Jabatan</th>
                        <th class="px-4 py-3 font-bold text-xs">Satuan Kerja</th>
                        <th class="px-4 py-3 font-bold text-xs">No HP</th>
                        <th class="px-4 py-3 cursor-pointer hover:text-brand-600 transition-colors font-bold text-xs" wire:click="sortBy('no_lab')">
                            <div class="flex items-center gap-1">
                                No Lab
                                @if($sortField === 'no_lab')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                @else
                                    <flux:icon name="arrows-up-down" class="w-3 h-3 text-slate-400 opacity-50" />
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 cursor-pointer hover:text-brand-600 transition-colors font-bold text-xs" wire:click="sortBy('tanggal_periksa')">
                            <div class="flex items-center gap-1">
                                Tgl Periksa
                                @if($sortField === 'tanggal_periksa')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3" />
                                @else
                                    <flux:icon name="arrows-up-down" class="w-3 h-3 text-slate-400 opacity-50" />
                                @endif
                            </div>
                        </th>
                        <th class="px-4 py-3 font-bold text-xs">Paket</th>
                        <th class="px-4 py-3 text-center font-bold text-xs">File Status</th>
                        <th class="px-4 py-3 text-center font-bold text-xs">Status Send</th>
                        @if(auth()->user()->isAdmin())
                            <th class="px-4 py-3 text-center font-bold text-xs">Kode User</th>
                        @endif
                        <th class="px-4 py-3 text-right font-bold text-xs">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pesertaList as $peserta)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 group" wire:key="row-{{ $peserta->nrp_nip }}-{{ $peserta->tanggal_periksa->format('Ymd') }}">
                            <td class="px-4 py-3">
                                <input 
                                    type="checkbox" 
                                    wire:click="toggleRow('{{ $peserta->nrp_nip }}|{{ $peserta->tanggal_periksa->format('Y-m-d') }}')"
                                    @checked(in_array($peserta->nrp_nip . '|' . $peserta->tanggal_periksa->format('Y-m-d'), $selectedRows))
                                    class="w-5 h-5 rounded border-2 border-gray-400 bg-white checked:bg-blue-600 checked:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                                />
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-xs text-slate-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-bold text-xs text-slate-900 dark:text-white whitespace-nowrap">{{ $peserta->nama }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap font-bold text-xs">
                                {{ $peserta->pangkat ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap font-bold text-xs">
                                {{ $peserta->nrp_nip ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap font-bold text-xs max-w-xs truncate" title="{{ $peserta->jabatan }}">
                                {{ $peserta->jabatan ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap font-bold text-xs max-w-xs truncate" title="{{ $peserta->satuan_kerja }}">
                                {{ $peserta->satuan_kerja ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap font-bold text-xs">
                                {{ $peserta->no_hp_raw ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap font-bold text-xs">
                                {{ $peserta->no_lab ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap font-bold text-xs">
                                {{ $peserta->tanggal_periksa?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 font-bold text-xs">
                                <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded text-xs">
                                    {{ $peserta->kode_paket ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-xs">
                                @if($peserta->status_pdf === 'uploaded')
                                    <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded text-xs">
                                        <flux:icon name="check" class="w-3 h-3 inline" /> Ada
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded text-xs">
                                        <flux:icon name="x-mark" class="w-3 h-3 inline" /> Belum
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-xs">
                                <span class="px-2 py-0.5 rounded text-xs {{ $this->statusWaBadgeClass[$peserta->status_wa] ?? 'bg-slate-100 text-slate-800' }}">
                                    @switch($peserta->status_wa)
                                        @case('sent') Terkirim @break
                                        @case('failed') Gagal @break
                                        @case('queued') Antrian @break
                                        @case('not_sent') Belum @break
                                        @default Belum
                                    @endswitch
                                </span>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td class="px-4 py-3 text-center font-bold text-xs">
                                    @if($peserta->diupload_oleh && $peserta->uploader && !$peserta->uploader->isAdmin())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded text-xs" title="{{ $peserta->uploader->name }}">
                                            <flux:icon name="user" class="w-3 h-3" />
                                            User {{ $peserta->diupload_oleh }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @if($peserta->no_hp_wa && $peserta->status_wa !== 'success' && $peserta->status_pdf === 'uploaded')
                                        @php
                                            $waPhone = preg_replace('/[^0-9]/', '', $peserta->no_hp_wa);
                                            if (str_starts_with($waPhone, '0')) {
                                                $waPhone = '62' . substr($waPhone, 1);
                                            }
                                            
                                            // Ambil template dari instansi peserta, fallback ke DEFAULT jika tidak ada
                                            $instansi = $peserta->instansi;
                                            if (!$instansi) {
                                                $instansi = \App\Models\Instansi::where('kode', 'DEFAULT')->first();
                                            }

                                            // Ambil template dari database - TIDAK ADA hardcoded template
                                            $template = $instansi?->template_prolog ?? '';

                                            // Jika template kosong dan tidak ada DEFAULT, skip (tidak bisa kirim WA)
                                            if (empty(trim($template))) {
                                                $template = 'Hasil pemeriksaan kesehatan: {{link}}';
                                            }

                                            // Strip HTML tags dari Quill editor
                                            $template = strip_tags($template);
                                            $template = html_entity_decode($template, ENT_QUOTES, 'UTF-8');
                                            $template = preg_replace("/\n{3,}/", "\n\n", trim($template));

                                            // Generate link PDF menggunakan Storage::url() untuk kompatibilitas
                                            $pdfLink = $peserta->path_pdf ? \Illuminate\Support\Facades\Storage::disk('public')->url($peserta->path_pdf) : '';

                                            // Hitung tahun anggaran
                                            $tahunAnggaran = $peserta->tanggal_periksa ? $peserta->tanggal_periksa->format('Y') : date('Y');

                                            // Replace variabel placeholder dengan data peserta
                                            $waMessage = str_replace(
                                                ['{{waktu}}', '{{no_lab}}', '{{nama_pasien}}', '{{pangkat}}', '{{nrp}}', '{{satuan_kerja}}', '{{tahun_anggaran}}', '{{link}}'],
                                                [
                                                    $peserta->tanggal_periksa->format('d/m/Y'),
                                                    $peserta->no_lab ?? '-',
                                                    $peserta->nama ?? '-',
                                                    $peserta->pangkat ?? '-',
                                                    $peserta->nrp_nip ?? '-',
                                                    $peserta->satuan_kerja ?? '-',
                                                    $tahunAnggaran,
                                                    $pdfLink
                                                ],
                                                $template
                                            );
                                            
                                            $waUrl = "https://wa.me/{$waPhone}?text=" . urlencode($waMessage);
                                        @endphp
                                        <a 
                                            href="{{ $waUrl }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-green-700 bg-green-100 hover:bg-green-200 dark:text-green-300 dark:bg-green-900/30 dark:hover:bg-green-900/50 rounded-lg transition-colors"
                                            title="Kirim WA Manual (buka WhatsApp Web)"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                            WA
                                        </a>
                                    @endif
                                    <a 
                                        href="{{ route('participants.edit', ['nrp_nip' => $peserta->nrp_nip, 'tanggal_periksa' => $peserta->tanggal_periksa->format('Y-m-d')]) }}"
                                        wire:navigate
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 rounded-lg transition-colors"
                                    >
                                        <flux:icon name="pencil" class="w-3.5 h-3.5" />
                                        Edit
                                    </a>
                                    <button 
                                        type="button"
                                        wire:click="confirmDelete('{{ $peserta->nrp_nip }}', '{{ $peserta->tanggal_periksa->format('Y-m-d') }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 dark:text-red-300 dark:bg-red-900/30 dark:hover:bg-red-900/50 rounded-lg transition-colors"
                                    >
                                        <flux:icon name="trash" class="w-3.5 h-3.5" />
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <flux:icon name="document-magnifying-glass" class="w-12 h-12 mb-3" />
                                    <p class="font-medium text-slate-600 dark:text-slate-300">Tidak ada data ditemukan</p>
                                    <p class="text-sm">Coba ubah filter atau kata kunci pencarian</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            {{ $pesertaList->links() }}
        </div>
    </div>

    <!-- Delete Confirmation Modal - Mobile Optimized -->
    @if($showDeleteModal)
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[100] overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
            @keydown.escape.window="$wire.closeDeleteModal()"
        >
            <!-- Background overlay -->
            <div class="flex min-h-full items-end justify-center p-0 text-center sm:items-center sm:p-4">
                <div 
                    class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" 
                    wire:click="closeDeleteModal"
                    aria-hidden="true"
                ></div>

                <!-- Modal panel - Mobile slides up from bottom, desktop centered -->
                <div 
                    x-show="show"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
                    class="relative w-full transform bg-white dark:bg-slate-900 text-left shadow-2xl transition-all sm:my-8 sm:max-w-md sm:rounded-2xl rounded-t-3xl"
                >
                    <!-- Mobile drag handle -->
                    <div class="flex justify-center pt-3 pb-1 sm:hidden">
                        <div class="w-12 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                    </div>

                    <!-- Header with icon -->
                    <div class="px-5 pt-4 pb-0 sm:px-6 sm:pt-6">
                        <div class="flex flex-col items-center text-center gap-3">
                            <!-- Warning Icon -->
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                <svg class="h-7 w-7 text-red-600 dark:text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            
                            <!-- Title -->
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white" id="modal-title">
                                    Hapus Data Peserta?
                                </h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    Tindakan ini tidak dapat dibatalkan
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Data Detail Card -->
                    <div class="px-5 py-4 sm:px-6">
                        <div class="rounded-xl border-2 border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20 p-4">
                            <div class="text-center mb-3">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-red-600 dark:text-red-400">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Data yang akan dihapus
                                </span>
                            </div>
                            
                            <!-- Nama -->
                            <div class="text-center border-b border-red-200 dark:border-red-700/50 pb-3 mb-3">
                                <span class="text-lg font-bold text-slate-800 dark:text-white break-words">{{ $deleteNama }}</span>
                            </div>
                            
                            <!-- Details Grid -->
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="bg-white/60 dark:bg-slate-800/40 rounded-lg p-2.5 text-center">
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">NRP/NIP</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-200 font-mono text-sm">{{ $deleteNrpNip }}</span>
                                </div>
                                <div class="bg-white/60 dark:bg-slate-800/40 rounded-lg p-2.5 text-center">
                                    <span class="block text-xs text-slate-500 dark:text-slate-400 mb-0.5">Tgl Periksa</span>
                                    <span class="font-semibold text-slate-700 dark:text-slate-200 font-mono text-sm">{{ $deleteTanggalPeriksa }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons - Stack on mobile -->
                    <div class="px-5 pb-5 sm:px-6 sm:pb-6 space-y-2.5 sm:space-y-0 sm:flex sm:flex-row-reverse sm:gap-3">
                        <button 
                            type="button" 
                            wire:click="deletePeserta"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-not-allowed"
                            class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-red-500/25 hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all active:scale-[0.98]"
                        >
                            <span wire:loading.remove wire:target="deletePeserta">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </span>
                            <span wire:loading wire:target="deletePeserta">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="deletePeserta">Ya, Hapus Data</span>
                            <span wire:loading wire:target="deletePeserta">Menghapus...</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="closeDeleteModal" 
                            class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl bg-slate-100 dark:bg-slate-800 px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 transition-all active:scale-[0.98]"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bulk Delete Confirmation Modal --}}
    @if($showBulkDeleteModal)
        <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 transition-opacity"></div>

            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800">
                        <!-- Header -->
                        <div class="bg-red-50 dark:bg-red-900/20 px-6 py-5 border-b border-red-200 dark:border-red-800">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                                    <flux:icon name="trash" class="w-6 h-6 text-red-600 dark:text-red-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-red-700 dark:text-red-300" id="modal-title">Hapus {{ $bulkDeleteCount }} Data Terpilih?</h3>
                                    <p class="text-sm text-red-600 dark:text-red-400">Tindakan ini tidak dapat dibatalkan!</p>
                                </div>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="bg-white dark:bg-slate-900 px-6 py-5">
                            <div class="flex items-start gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                                <flux:icon name="exclamation-triangle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                                <div class="text-sm text-red-700 dark:text-red-300">
                                    <p class="font-medium mb-2">Anda akan menghapus:</p>
                                    <ul class="list-disc list-inside space-y-1 text-red-600 dark:text-red-400">
                                        <li><strong>{{ $bulkDeleteCount }}</strong> data peserta yang dipilih</li>
                                        <li>Semua file PDF terkait</li>
                                        <li>Riwayat pengiriman WhatsApp</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="bg-gray-50 dark:bg-slate-800/50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3">
                            <button 
                                type="button" 
                                wire:click="deleteSelected" 
                                class="inline-flex w-full justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors"
                            >
                                <flux:icon name="trash" class="w-4 h-4 mr-2" />
                                Ya, Hapus {{ $bulkDeleteCount }} Data
                            </button>
                            <button 
                                type="button" 
                                wire:click="closeBulkDeleteModal" 
                                class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-900 dark:text-white shadow-sm ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 sm:mt-0 sm:w-auto transition-colors"
                            >
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>