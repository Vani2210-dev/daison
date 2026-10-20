@extends('layouts.app')

@section('title', 'Bảng Quản Lý Công Việc & Lắp Đặt Máy Lọc Nước | ERP Đại Sơn')

@section('content')

<!-- Include html5-qrcode scanner library -->
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>

<script>
function technicianTasksApp() {
    return {
        searchFilter: '',
        showModal: false,
        qrMode: 'select', // 'select' | 'camera' | 'manual'
        scannerInstance: null,
        submitting: false,
        successState: false,
        successMessage: '',
        successPassportUrl: '',
        activatedQrCode: '',

        installationsData: @json($filterInstallations),
        partsCatalog: @json($filterParts ?? []),
        blankQrList: @json($blankQrCodes ?? []),
        selectedPartId: '',
        partSearchQuery: '',
        partDropdownOpen: false,
        selectedAccessories: [],
        customPartName: '',
        customPartQty: 1,
        customPartUnit: 'Cái',
        customPartDate: new Date().toISOString().slice(0, 10),
        showCustomPartForm: false,

        form: {
            installation_id: '',
            qr_code: '',
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            machine_model: '',
            machine_serial: '',
            water_source_type: 'Nước máy thành phố',
            warranty_months: 24,
            filter_replacement_months: 6,
            tds_in: null,
            tds_out: null,
            pressure: 'Bơm chạy êm, đủ áp',
            accessories: '[]',
            activation_notes: ''
        },

        initApp() {
            const urlParams = new URLSearchParams(window.location.search);
            const openInstallId = urlParams.get('install_id');
            if (openInstallId) {
                this.openQrModal(openInstallId);
            }
        },

        matchesSearch(text) {
            if (!this.searchFilter) return true;
            const q = this.searchFilter.toLowerCase().trim();
            return (text || '').toLowerCase().includes(q);
        },

        get tdsEfficiency() {
            if (this.form.tds_in && this.form.tds_out && this.form.tds_in > 0) {
                const eff = ((this.form.tds_in - this.form.tds_out) / this.form.tds_in) * 100;
                return eff > 0 ? eff.toFixed(1) : 0;
            }
            return null;
        },

        extractQrCode(raw) {
            if (!raw) return '';
            let text = String(raw).trim();
            if (text.includes('/m/')) {
                const parts = text.split('/m/');
                text = parts[parts.length - 1].split(/[?#]/)[0].replace(/\/+$/, '').trim();
            } else if (text.includes('/qr/')) {
                const parts = text.split('/qr/');
                text = parts[parts.length - 1].split(/[?#]/)[0].replace(/\/+$/, '').trim();
            } else if (text.includes('/qr-activation/')) {
                const parts = text.split('/qr-activation/');
                text = parts[parts.length - 1].split(/[?#]/)[0].replace(/\/+$/, '').trim();
            } else if (text.includes('qr=')) {
                try {
                    const url = new URL(text);
                    text = url.searchParams.get('qr') || text;
                } catch(e) {}
            } else if (text.startsWith('http://') || text.startsWith('https://')) {
                try {
                    const url = new URL(text);
                    const segments = url.pathname.split('/').filter(Boolean);
                    if (segments.length > 0) {
                        text = segments[segments.length - 1];
                    }
                } catch(e) {}
            }
            text = text.replace(/\/+$/, '').trim();

            const found = (this.blankQrList || []).find(b => b.qr_token === text || b.qr_code === text);
            if (found && found.qr_code) {
                return found.qr_code;
            }

            return text;
        },

        get filteredPartsCatalog() {
            if (!this.partSearchQuery || !this.partSearchQuery.trim()) {
                return (this.partsCatalog || []).slice(0, 40);
            }
            const q = this.partSearchQuery.toLowerCase().trim();
            return (this.partsCatalog || []).filter(p => {
                const name = (p.name || '').toLowerCase();
                const code = (p.code || '').toLowerCase();
                const cat = (p.category || '').toLowerCase();
                return name.includes(q) || code.includes(q) || cat.includes(q);
            }).slice(0, 40);
        },

        addPartToAccessories(partId = null) {
            const id = partId || this.selectedPartId;
            if (!id) return;
            const part = this.partsCatalog.find(p => p.id == id);
            if (!part) return;

            const existing = this.selectedAccessories.find(a => (a.id && a.id == part.id) || a.name === part.name);
            if (existing) {
                existing.quantity = (parseInt(existing.quantity) || 1) + 1;
            } else {
                this.selectedAccessories.push({
                    id: part.id,
                    code: part.code || '',
                    name: part.name,
                    quantity: 1,
                    unit: part.unit || 'Cái',
                    installed_date: new Date().toISOString().slice(0, 10)
                });
            }
            this.selectedPartId = '';
            this.partSearchQuery = '';
            this.partDropdownOpen = false;
            this.syncAccessoriesJson();
        },

        addCustomAccessory() {
            if (!this.customPartName.trim()) return;
            this.selectedAccessories.push({
                name: this.customPartName.trim(),
                quantity: parseInt(this.customPartQty) || 1,
                unit: this.customPartUnit || 'Cái',
                installed_date: this.customPartDate || new Date().toISOString().slice(0, 10)
            });
            this.customPartName = '';
            this.customPartQty = 1;
            this.customPartUnit = 'Cái';
            this.customPartDate = new Date().toISOString().slice(0, 10);
            this.showCustomPartForm = false;
            this.syncAccessoriesJson();
        },

        removeAccessory(index) {
            this.selectedAccessories.splice(index, 1);
            this.syncAccessoriesJson();
        },

        changeAccessoryQty(index, delta) {
            if (this.selectedAccessories[index]) {
                const current = parseInt(this.selectedAccessories[index].quantity) || 1;
                const next = current + delta;
                if (next <= 0) {
                    this.removeAccessory(index);
                    return;
                }
                this.selectedAccessories[index].quantity = next;
                this.syncAccessoriesJson();
            }
        },

        syncAccessoriesJson() {
            this.form.accessories = JSON.stringify(this.selectedAccessories);
        },

        openQrModal(target = null) {
            this.successState = false;
            this.submitting = false;
            this.selectedAccessories = [];
            this.selectedPartId = '';
            this.partSearchQuery = '';
            this.partDropdownOpen = false;

            let inst = null;
            if (typeof target === 'object' && target !== null) {
                inst = target;
            } else if (target) {
                inst = this.installationsData.find(i => i.id == target);
            }

            if (inst) {
                this.form.installation_id = inst.id;
                this.form.customer_name = inst.customer_name || '';
                this.form.customer_phone = inst.customer_phone || '';
                this.form.customer_address = inst.customer_address || '';
                this.form.machine_model = inst.machine_model || '';
                this.form.machine_serial = inst.machine_serial || '';
                this.form.water_source_type = inst.water_source_type || 'Nước máy thành phố';
                this.form.tds_in = inst.tds_in || (inst.installed_machine?.tds_in || null);
                this.form.tds_out = inst.tds_out || (inst.installed_machine?.tds_out || null);
                this.form.pressure = inst.pressure || 'Bơm chạy êm, đủ áp';
                this.form.activation_notes = inst.completion_notes || 'Lắp đặt hoàn tất, nước sạch đạt tiêu chuẩn.';
                
                if (inst.items && inst.items.length > 0) {
                    const defaultDate = inst.installed_at ? String(inst.installed_at).slice(0, 10) : new Date().toISOString().slice(0, 10);
                    this.selectedAccessories = inst.items.map(it => ({
                        id: it.part_id,
                        code: it.part_code || '',
                        name: it.part_name || '',
                        quantity: parseInt(it.quantity) || 1,
                        unit: it.unit || 'Cái',
                        installed_date: defaultDate
                    }));
                    this.syncAccessoriesJson();
                } else if (inst.installed_machine?.accessories) {
                    try {
                        const parsed = JSON.parse(inst.installed_machine.accessories);
                        if (Array.isArray(parsed)) {
                            this.selectedAccessories = parsed.map(p => ({
                                name: p.name || '',
                                quantity: parseInt(p.quantity || p.qty) || 1,
                                unit: p.unit || 'Cái',
                                installed_date: p.installed_date || (inst.installed_at ? String(inst.installed_at).slice(0, 10) : new Date().toISOString().slice(0, 10))
                            }));
                        } else {
                            this.selectedAccessories = [{
                                name: inst.installed_machine.accessories,
                                quantity: 1,
                                unit: 'Cái',
                                installed_date: new Date().toISOString().slice(0, 10)
                            }];
                        }
                    } catch(e) {
                        this.selectedAccessories = [{
                            name: inst.installed_machine.accessories,
                            quantity: 1,
                            unit: 'Cái',
                            installed_date: new Date().toISOString().slice(0, 10)
                        }];
                    }
                    this.syncAccessoriesJson();
                }

                if (inst.installed_machine?.qr_code) {
                    this.form.qr_code = this.extractQrCode(inst.installed_machine.qr_code);
                }
            } else {
                this.resetForm();
            }

            this.showModal = true;
            this.$nextTick(() => {
                if (this.qrMode === 'camera') {
                    this.startScanner();
                }
            });
        },

        closeModal() {
            this.stopScanner();
            this.showModal = false;
        },

        resetForm() {
            this.selectedAccessories = [];
            this.selectedPartId = '';
            this.partSearchQuery = '';
            this.partDropdownOpen = false;
            this.customPartName = '';
            this.customPartQty = 1;
            this.customPartUnit = 'Cái';
            this.customPartDate = new Date().toISOString().slice(0, 10);
            this.showCustomPartForm = false;
            this.form = {
                installation_id: '',
                qr_code: '',
                customer_name: '',
                customer_phone: '',
                customer_address: '',
                machine_model: '',
                machine_serial: '',
                water_source_type: 'Nước máy thành phố',
                warranty_months: 24,
                filter_replacement_months: 6,
                tds_in: null,
                tds_out: null,
                pressure: 'Bơm chạy êm, đủ áp',
                accessories: '[]',
                activation_notes: ''
            };
        },

        onInstallationChange() {
            const found = this.installationsData.find(i => i.id == this.form.installation_id);
            if (found) {
                this.form.customer_name = found.customer_name || '';
                this.form.customer_phone = found.customer_phone || '';
                this.form.customer_address = found.customer_address || '';
                this.form.machine_model = found.machine_model || '';
                this.form.machine_serial = found.machine_serial || '';
                this.form.water_source_type = found.water_source_type || 'Nước máy thành phố';

                if (found.items && found.items.length > 0) {
                    const today = new Date().toISOString().slice(0, 10);
                    this.selectedAccessories = found.items.map(it => ({
                        id: it.part_id,
                        code: it.part_code || '',
                        name: it.part_name || '',
                        quantity: parseInt(it.quantity) || 1,
                        unit: it.unit || 'Cái',
                        installed_date: today
                    }));
                    this.syncAccessoriesJson();
                } else if (found.installed_machine?.accessories) {
                    try {
                        const parsed = JSON.parse(found.installed_machine.accessories);
                        if (Array.isArray(parsed)) {
                            this.selectedAccessories = parsed.map(p => ({
                                name: p.name || '',
                                quantity: parseInt(p.quantity || p.qty) || 1,
                                unit: p.unit || 'Cái',
                                installed_date: p.installed_date || new Date().toISOString().slice(0, 10)
                            }));
                        } else {
                            this.selectedAccessories = [{
                                name: found.installed_machine.accessories,
                                quantity: 1,
                                unit: 'Cái',
                                installed_date: new Date().toISOString().slice(0, 10)
                            }];
                        }
                    } catch(e) {
                        this.selectedAccessories = [{
                            name: found.installed_machine.accessories,
                            quantity: 1,
                            unit: 'Cái',
                            installed_date: new Date().toISOString().slice(0, 10)
                        }];
                    }
                    this.syncAccessoriesJson();
                }

                if (found.installed_machine?.qr_code && !this.form.qr_code) {
                    this.form.qr_code = this.extractQrCode(found.installed_machine.qr_code);
                }
            }
        },

        startScanner() {
            if (typeof Html5Qrcode === 'undefined') {
                console.warn('Html5Qrcode not loaded yet.');
                return;
            }

            this.stopScanner();

            setTimeout(() => {
                const readerElement = document.getElementById('qr-reader');
                if (!readerElement) return;

                this.scannerInstance = new Html5Qrcode("qr-reader");
                const config = { fps: 10, qrbox: { width: 220, height: 220 } };

                this.scannerInstance.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText, decodedResult) => {
                        this.onQrCodeScanned(decodedText);
                    },
                    (errorMessage) => {
                        // ignore frame scanning errors
                    }
                ).catch(err => {
                    console.log("Camera error, attempting default camera:", err);
                    Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length) {
                            const cameraId = devices[0].id;
                            this.scannerInstance.start(cameraId, config, (decodedText) => {
                                this.onQrCodeScanned(decodedText);
                            });
                        }
                    }).catch(e => console.error("No cameras found:", e));
                });
            }, 300);
        },

        stopScanner() {
            if (this.scannerInstance) {
                try {
                    this.scannerInstance.stop().then(() => {
                        this.scannerInstance.clear();
                        this.scannerInstance = null;
                    }).catch(() => {
                        this.scannerInstance = null;
                    });
                } catch(e) {
                    this.scannerInstance = null;
                }
            }
        },

        onQrCodeScanned(text) {
            console.log("Scanned QR Text:", text);
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                osc.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.15);
            } catch(e) {}

            this.form.qr_code = this.extractQrCode(text);
            this.stopScanner();

            if (window.showToast) {
                window.showToast('success', 'Đã quét thành công mã QR: ' + this.form.qr_code);
            }
        },

        async submitBindQr() {
            if (!this.form.installation_id) {
                alert('Vui lòng chọn đơn lắp đặt!');
                return;
            }
            if (!this.form.qr_code) {
                alert('Vui lòng quét hoặc nhập mã tem QR!');
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(`/api/water-filter/installations/${this.form.installation_id}/bind-qr`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const res = await response.json();

                if (res.success) {
                    this.successState = true;
                    this.successMessage = res.message;
                    this.successPassportUrl = res.passport_url;
                    this.activatedQrCode = res.qr_code || this.form.qr_code;
                } else {
                    alert(res.message || 'Có lỗi xảy ra khi liên kết mã QR.');
                }
            } catch (err) {
                console.error("Error submitting bind QR:", err);
                alert('Lỗi kết nối máy chủ. Vui lòng thử lại!');
            } finally {
                this.submitting = false;
            }
        }
    };
}

document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        Alpine.data('technicianTasksApp', technicianTasksApp);
    }
});
</script>

<div x-data="technicianTasksApp()" x-init="initApp()" class="space-y-6 pb-12">

    <!-- Top Action & View Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 text-lg">
                    <i class="fa-solid fa-list-check"></i>
                </span>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>{{ $view === 'my_tasks' ? 'Công Việc Của Tôi' : 'Quản Lý Công Việc & Phân Công' }}</span>
                        @if($view === 'my_tasks')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                Kỹ Thuật Viên: {{ Auth::user()->name }}
                            </span>
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Theo dõi các đơn lắp đặt máy lọc nước, cài đặt kích hoạt tem QR và tiến độ công việc.</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- View Switch Tabs -->
            <div class="grid grid-cols-2 sm:flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-bold">
                <a href="{{ url('/tasks?view=my_tasks') }}" 
                   class="px-3.5 py-2 rounded-lg transition-all flex items-center justify-center gap-2 {{ $view === 'my_tasks' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-user-check text-emerald-600"></i>
                    <span>Công việc của tôi</span>
                    @if($myInstallPending > 0)
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-amber-500 text-white">
                            {{ $myInstallPending }}
                        </span>
                    @endif
                </a>
                <a href="{{ url('/tasks?view=all') }}" 
                   class="px-3.5 py-2 rounded-lg transition-all flex items-center justify-center gap-2 {{ $view !== 'my_tasks' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-layer-group text-slate-500"></i>
                    <span>Tất cả công việc</span>
                </a>
            </div>

            <!-- Fast QR Scan Button -->
            <button type="button" 
                    id="btn-scan-qr-top"
                    @click="openQrModal(null)" 
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02] active:scale-[0.98] cursor-pointer">
                <i class="fa-solid fa-qrcode text-base"></i>
                <span>Quét Mã QR Cài Đặt Máy</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Card 1: Pending installations -->
        <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent p-4 rounded-2xl border border-amber-200/80 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-md shadow-amber-500/30 shrink-0">
                <i class="fa-solid fa-wrench"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Đơn Lắp Đặt Cần Làm</div>
                <div class="text-2xl font-black text-amber-950 mt-0.5">
                    {{ $view === 'my_tasks' ? $myInstallPending : $allInstallPending }}
                </div>
            </div>
        </div>

        <!-- Card 2: Completed installations -->
        <div class="bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent p-4 rounded-2xl border border-emerald-200/80 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-500/30 shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Đã Kích Hoạt QR</div>
                <div class="text-2xl font-black text-emerald-950 mt-0.5">
                    {{ $view === 'my_tasks' ? $myInstallCompleted : $allInstallCompleted }}
                </div>
            </div>
        </div>

        <!-- Card 3: Blank QR Available -->
        <div class="bg-gradient-to-br from-indigo-500/10 via-indigo-500/5 to-transparent p-4 rounded-2xl border border-indigo-200/80 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-indigo-500 text-white flex items-center justify-center text-xl shadow-md shadow-indigo-500/30 shrink-0">
                <i class="fa-solid fa-barcode"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-indigo-800 uppercase tracking-wider">Tem QR Trống Sẵn Sàng</div>
                <div class="text-2xl font-black text-indigo-950 mt-0.5">
                    {{ $blankQrCodes->count() }}
                </div>
            </div>
        </div>

        <!-- Card 4: Other Tasks -->
        <div class="bg-gradient-to-br from-sky-500/10 via-sky-500/5 to-transparent p-4 rounded-2xl border border-sky-200/80 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-sky-500 text-white flex items-center justify-center text-xl shadow-md shadow-sky-500/30 shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold text-sky-800 uppercase tracking-wider">Nhiệm Vụ Khác</div>
                <div class="text-2xl font-black text-sky-950 mt-0.5">
                    {{ $tasks->total() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Water Filter Installation Orders -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-blue-600"></i>
                    <span>Đơn Hàng Lắp Đặt Máy Lọc Nước</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        {{ $filterInstallations->count() }} đơn
                    </span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Danh sách đơn hàng cần đi lắp đặt thực tế tại nhà khách hàng & kích hoạt tem QR điện tử.</p>
            </div>

            <!-- Search Filter for installations -->
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           x-model="searchFilter" 
                           placeholder="Tìm tên KH, SĐT, địa chỉ, mã..." 
                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        @if($filterInstallations->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-3">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Chưa có đơn lắp đặt nào</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                    {{ $view === 'my_tasks' ? 'Bạn hiện không có đơn lắp đặt máy lọc nước nào được phân công.' : 'Hệ thống chưa có đơn lắp đặt máy lọc nước nào.' }}
                </p>
            </div>
        @else
            <!-- Installation Cards Grid -->
            <div class="divide-y divide-slate-100">
                @foreach($filterInstallations as $inst)
                    @php
                        $isCompleted = ($inst->status === 'completed');
                        $hasMachine = $inst->installedMachine;
                        $timeText = $inst->appointment_date ? date('H:i - d/m/Y', strtotime($inst->appointment_date)) : 'Chưa hẹn giờ';
                    @endphp
                    <div x-show="matchesSearch('{{ addslashes($inst->code) }} {{ addslashes($inst->customer_name) }} {{ addslashes($inst->customer_phone) }} {{ addslashes($inst->customer_address) }} {{ addslashes($inst->machine_model) }}')" 
                         class="p-5 hover:bg-blue-50/30 transition-all flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                        
                        <!-- Left Info -->
                        <div class="space-y-2 flex-1">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $inst->code }}
                                </span>

                                @if($isCompleted)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                        <i class="fa-solid fa-check-circle"></i>
                                        <span>Đã Hoàn Thành Lắp Đặt</span>
                                    </span>
                                @elseif($inst->status === 'in_progress')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                        <span>Đang Tiến Hành Lắp</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1">
                                        <i class="fa-solid fa-clock"></i>
                                        <span>Cần Lắp Đặt</span>
                                    </span>
                                @endif

                                @if($hasMachine && $hasMachine->status === 'activated')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 flex items-center gap-1">
                                        <i class="fa-solid fa-qrcode"></i>
                                        <span>Đã Kích Hoạt QR: {{ $hasMachine->qr_code }}</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <span>Chưa Gán Tem QR</span>
                                    </span>
                                @endif
                            </div>

                            <!-- Customer & Machine details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fa-solid fa-user text-slate-400 text-xs"></i>
                                        <span>{{ $inst->customer_name }}</span>
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1 flex items-center gap-2">
                                        <i class="fa-solid fa-phone text-emerald-600 text-xs"></i>
                                        <a href="tel:{{ $inst->customer_phone }}" class="font-bold text-emerald-700 hover:underline">
                                            {{ $inst->customer_phone }}
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <span class="text-slate-500">Gọi điện xác nhận hẹn</span>
                                    </div>
                                    <div class="text-xs text-slate-600 mt-1 flex items-start gap-2">
                                        <i class="fa-solid fa-location-dot text-rose-500 text-xs mt-0.5 shrink-0"></i>
                                        <span>{{ $inst->customer_address }}</span>
                                    </div>
                                </div>

                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 space-y-1 text-xs">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-microchip text-blue-600"></i>
                                        <span>Model: {{ $inst->machine_model }}</span>
                                    </div>
                                    <div class="text-slate-500 flex items-center gap-1.5">
                                        <i class="fa-solid fa-calendar-day text-slate-400"></i>
                                        <span>Hẹn lắp: <strong class="text-slate-700">{{ $timeText }}</strong></span>
                                    </div>
                                    @if($inst->technician_name)
                                        <div class="text-slate-500 flex items-center gap-1.5">
                                            <i class="fa-solid fa-user-gear text-slate-400"></i>
                                            <span>Kỹ thuật viên: <strong class="text-sky-700">{{ $inst->technician_name }}</strong></span>
                                        </div>
                                    @endif
                                    @if($hasMachine && ($hasMachine->tds_in || $hasMachine->tds_out))
                                        <div class="text-[11px] font-mono text-emerald-700 pt-0.5">
                                            TDS vào: <strong>{{ $hasMachine->tds_in ?? '--' }}</strong> ppm → TDS ra: <strong>{{ $hasMachine->tds_out ?? '--' }}</strong> ppm
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-end justify-end gap-2 shrink-0 pt-2 lg:pt-0 w-full lg:w-auto">
                            <!-- Button: Quét QR & Cài Đặt Máy (Chỉ hiển thị với đơn chưa gán tem QR) -->
                            @if(!$isCompleted && (!$hasMachine || $hasMachine->status !== 'activated'))
                                <button type="button" 
                                        id="btn-scan-qr-order-{{ $inst->id }}"
                                        @click="openQrModal({{ $inst->id }})" 
                                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-emerald-600/20 hover:scale-[1.02]">
                                    <i class="fa-solid fa-qrcode text-sm"></i>
                                    <span>Quét Mã QR Cài Đặt Máy</span>
                                </button>
                            @endif

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                @if($hasMachine && $hasMachine->qr_token)
                                    <a href="{{ url('/m/' . $hasMachine->qr_token) }}" 
                                       target="_blank" 
                                       class="flex-1 sm:flex-none px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-blue-700 hover:bg-blue-50 border border-slate-200 flex items-center justify-center gap-1.5 transition-all text-center">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                        <span>Hồ Sơ Bảo Hành</span>
                                    </a>
                                @endif

                                <a href="{{ url('/water-filter/installations/' . $inst->id . '/edit') }}" 
                                   class="flex-1 sm:flex-none px-3 py-2 rounded-xl text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all flex items-center justify-center gap-1.5 text-center">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    <span>Chi tiết đơn</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Section 2: General Tasks / Phân Công Công Việc Chung -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-tasks text-indigo-600"></i>
                    <span>Nhiệm Vụ & Phân Công Công Việc</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $tasks->total() }} công việc
                    </span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Tiến độ giao việc, hạn chót hoàn thành và báo cáo công việc nội bộ.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase text-[11px]">
                        <th class="py-3 px-4">Tên Công Việc</th>
                        <th class="py-3 px-4">Người Giao</th>
                        <th class="py-3 px-4">Người Thực Hiện</th>
                        <th class="py-3 px-4">Thời Hạn</th>
                        <th class="py-3 px-4 text-center">Tiến Độ</th>
                        <th class="py-3 px-4 text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tasks as $tk)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    @if(str_contains($tk->category ?? '', 'water'))
                                        <i class="fa-solid fa-faucet-drip text-blue-500 text-xs"></i>
                                    @else
                                        <i class="fa-solid fa-clipboard-check text-slate-400 text-xs"></i>
                                    @endif
                                    <span>{{ $tk->title ?? 'Nhiệm vụ công việc' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-normal truncate max-w-sm mt-0.5">
                                    {{ $tk->description }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">{{ $tk->creator?->name ?? 'Ban Giám Đốc' }}</td>
                            <td class="py-3 px-4 font-semibold text-sky-700">{{ $tk->assignedTo?->name ?? ($tk->assigned_to_name ?? 'Phòng Kỹ Thuật') }}</td>
                            <td class="py-3 px-4 text-slate-600 font-mono">{{ $tk->deadline ? date('d/m/Y', strtotime($tk->deadline)) : '---' }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 bg-slate-100 h-2 rounded-full overflow-hidden">
                                        <div class="bg-blue-600 h-full rounded-full" style="width: {{ $tk->progress_percent ?? ($tk->progress ?? 100) }}%"></div>
                                    </div>
                                    <span class="font-bold text-slate-700 text-[11px]">{{ $tk->progress_percent ?? ($tk->progress ?? 100) }}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ in_array($tk->status, ['Hoàn thành', 'completed']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $tk->status ?? 'Đang thực hiện' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">Chưa có nhiệm vụ nào được tạo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tasks->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- MODAL: QUÉT MÃ QR CÀI ĐẶT THÔNG TIN MÁY LỌC NƯỚC           -->
    <!-- ======================================================== -->
    <div x-show="showModal" 
         id="qr-setup-modal"
         x-cloak 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-2.5 sm:p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
        
        <div @click.away="closeModal()" 
             class="bg-white w-full max-w-3xl rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[92vh] sm:max-h-[95vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 pr-2">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center text-emerald-400 text-base sm:text-lg border border-white/20 shrink-0">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-black tracking-tight leading-snug">Cài Đặt Máy & Kích Hoạt Mã QR Bảo Hành</h3>
                        <p class="text-[11px] sm:text-xs text-blue-200 truncate sm:whitespace-normal">Quét tem QR dán trên máy hoặc chọn tem từ kho để liên kết đơn lắp đặt</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white transition-colors cursor-pointer shrink-0">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-4 sm:p-6 overflow-y-auto space-y-5 flex-1">

                <!-- Success Screen State -->
                <template x-if="successState">
                    <div class="text-center py-5 sm:py-6 space-y-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl sm:text-4xl mx-auto shadow-lg shadow-emerald-500/20 animate-bounce">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <h4 class="text-base sm:text-lg font-black text-slate-900" x-text="successMessage || 'Kích Hoạt Tem QR Thành Công!'"></h4>
                            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">Đơn lắp đặt đã được đánh dấu hoàn thành và khách hàng có thể quét mã QR để tra cứu bảo hành điện tử.</p>
                        </div>

                        <!-- Passport Badge -->
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 border border-slate-200 max-w-md mx-auto text-left space-y-2 text-xs">
                            <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                                <span class="text-slate-500">Mã Tem QR:</span>
                                <strong class="text-blue-700 font-mono text-sm" x-text="activatedQrCode"></strong>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                                <span class="text-slate-500">Khách Hàng:</span>
                                <strong class="text-slate-900" x-text="form.customer_name"></strong>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Model Máy:</span>
                                <strong class="text-slate-900" x-text="form.machine_model"></strong>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2.5 sm:gap-3 pt-2">
                            <a :href="successPassportUrl" 
                               target="_blank" 
                               class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-xs shadow-md shadow-blue-500/20 flex items-center justify-center gap-2 hover:scale-[1.02] transition-transform">
                                <i class="fa-solid fa-passport"></i>
                                <span>Xem Thẻ Bảo Hành Cho Khách</span>
                            </a>
                            <button type="button" 
                                    @click="closeModal(); window.location.reload();" 
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                                <span>Hoàn Tất & Đóng</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Input Form State -->
                <template x-if="!successState">
                    <form @submit.prevent="submitBindQr()" class="space-y-5">

                        <!-- SECTION 1: QUÉT / CHỌN TEM QR -->
                        <div class="bg-slate-50/80 p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-qrcode text-blue-600 text-sm"></i>
                                    <span>Bước 1: Quét Hoặc Chọn Tem QR Dán Trên Máy</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                
                                <!-- Mode Selector: Responsive full-width trên mobile thành dạng tab 3 cột dễ bấm -->
                                <div class="grid grid-cols-3 sm:flex items-center bg-white p-1 rounded-xl border border-slate-200 text-xs font-bold w-full sm:w-auto shadow-2xs">
                                    <button type="button" 
                                            @click="qrMode = 'select'; stopScanner();" 
                                            :class="qrMode === 'select' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                                            class="py-2 sm:py-1 px-2.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer text-center">
                                        <i class="fa-solid fa-list text-xs"></i>
                                        <span>Kho Tem</span>
                                    </button>
                                    <button type="button" 
                                            @click="qrMode = 'camera'; startScanner();" 
                                            :class="qrMode === 'camera' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                                            class="py-2 sm:py-1 px-2.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer text-center">
                                        <i class="fa-solid fa-camera text-xs"></i>
                                        <span>Camera</span>
                                    </button>
                                    <button type="button" 
                                            @click="qrMode = 'manual'; stopScanner();" 
                                            :class="qrMode === 'manual' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                                            class="py-2 sm:py-1 px-2.5 rounded-lg transition-all flex items-center justify-center gap-1.5 cursor-pointer text-center">
                                        <i class="fa-solid fa-keyboard text-xs"></i>
                                        <span>Nhập Mã</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Blank QR Dropdown Select -->
                            <div x-show="qrMode === 'select'" class="space-y-1.5">
                                <select x-model="form.qr_code" 
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-mono font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[44px]">
                                    <option value="">-- Chọn một tem QR trống từ kho Đại Sơn --</option>
                                    @foreach($blankQrCodes as $bqr)
                                        <option value="{{ $bqr->qr_code }}">
                                            Tem: {{ $bqr->qr_code }} ({{ $bqr->machine_code }}) - Chưa kích hoạt
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info text-blue-500"></i>
                                    <span>Các tem QR dán trống đã được in sẵn trong kho để cấp phát cho kỹ thuật viên.</span>
                                </p>
                            </div>

                            <!-- Camera Scanner Viewport -->
                            <div x-show="qrMode === 'camera'" class="space-y-2.5">
                                <div id="qr-reader" class="w-full max-w-sm mx-auto overflow-hidden rounded-2xl border-2 border-dashed border-blue-400 bg-black min-h-[220px]"></div>
                                <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 max-w-sm mx-auto gap-2">
                                    <span class="flex items-center gap-1 text-[11px]">
                                        <i class="fa-solid fa-bolt text-amber-500"></i>
                                        <span>Hướng camera vào tem QR để quét tự động</span>
                                    </span>
                                    <button type="button" @click="startScanner()" class="text-blue-600 font-bold hover:underline flex items-center gap-1 cursor-pointer py-1 px-2 rounded-lg hover:bg-blue-50">
                                        <i class="fa-solid fa-rotate-right"></i>
                                        <span>Bật lại Camera</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Manual Input -->
                            <div x-show="qrMode === 'manual'">
                                <div class="relative">
                                    <input type="text" 
                                           x-model="form.qr_code" 
                                           @input="form.qr_code = extractQrCode($event.target.value)"
                                           @paste="$nextTick(() => form.qr_code = extractQrCode(form.qr_code))"
                                           placeholder="Nhập mã tem (vd: QR-10001) hoặc quét/dán tem..." 
                                           class="w-full pl-3.5 pr-10 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-mono font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 min-h-[44px]">
                                    <i class="fa-solid fa-qrcode absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                </div>
                            </div>

                            <!-- Display Scanned / Selected QR -->
                            <div x-show="form.qr_code" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-emerald-600 text-base shrink-0"></i>
                                    <div>
                                        <span class="text-slate-600 text-xs">Đã chọn mã QR:</span>
                                        <strong class="font-mono font-bold text-emerald-800 text-sm ml-1" x-text="form.qr_code"></strong>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-200 text-emerald-900 uppercase tracking-wide">Sẵn sàng liên kết</span>
                            </div>
                        </div>

                        <!-- SECTION 2: CHỌN ĐƠN LẮP ĐẶT & THÔNG TIN KHÁCH HÀNG -->
                        <div class="space-y-3">
                            <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-file-invoice text-indigo-600"></i>
                                <span>Bước 2: Chọn Đơn Lắp Đặt & Thông Tin Khách Hàng</span>
                                <span class="text-rose-500">*</span>
                            </label>

                            <div>
                                @php
                                    $unassignedInstallations = $filterInstallations->filter(function($f) {
                                        $hasQr = ($f->status === 'completed') 
                                            || ($f->installedMachine && ($f->installedMachine->status === 'activated' || !empty($f->installedMachine->qr_code))) 
                                            || !empty($f->warranty_card_code);
                                        return !$hasQr;
                                    });
                                @endphp
                                <select x-model="form.installation_id" 
                                        @change="onInstallationChange()" 
                                        required 
                                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500 min-h-[44px]">
                                    <option value="">-- Chọn đơn lắp đặt --</option>
                                    @foreach($unassignedInstallations as $f)
                                        <option value="{{ $f->id }}">
                                            [{{ $f->code }}] {{ $f->customer_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Editable Customer Info -->
                            <div class="p-3.5 bg-slate-50/90 rounded-2xl border border-slate-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-pen text-indigo-600"></i>
                                        <span>Thông Tin Khách Hàng</span>
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs font-bold text-slate-700 block mb-1">
                                            Tên Khách Hàng <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   x-model="form.customer_name" 
                                                   required 
                                                   placeholder="Nhập họ tên khách hàng..." 
                                                   class="w-full pl-8 pr-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 min-h-[40px]">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-700 block mb-1">
                                            Số Điện Thoại <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   x-model="form.customer_phone" 
                                                   required 
                                                   placeholder="vd: 0988123456..." 
                                                   class="w-full pl-8 pr-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-mono font-bold text-slate-800 focus:ring-2 focus:ring-indigo-500 min-h-[40px]">
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="text-xs font-bold text-slate-700 block mb-1">
                                            Địa Chỉ Lắp Đặt <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <i class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                            <input type="text" 
                                                   x-model="form.customer_address" 
                                                   required 
                                                   placeholder="Nhập địa chỉ nhà khách hàng..." 
                                                   class="w-full pl-8 pr-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white text-slate-800 focus:ring-2 focus:ring-indigo-500 min-h-[40px]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: THÔNG SỐ KỸ THUẬT & MÁY LỌC NƯỚC -->
                        <div class="space-y-4 pt-2 border-t border-slate-100">
                            <label class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-sliders text-teal-600"></i>
                                <span>Bước 3: Cài Đặt Thông Số Kỹ Thuật Thực Tế</span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <!-- Machine Model -->
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Model Máy Lọc Nước <span class="text-rose-500">*</span></label>
                                    <input type="text" 
                                           x-model="form.machine_model" 
                                           required 
                                           placeholder="vd: Karofi Optimus O-s129..." 
                                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 min-h-[42px]">
                                </div>

                                <!-- Serial Number -->
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Mã Serial / Số Máy (Nếu có)</label>
                                    <input type="text" 
                                           x-model="form.machine_serial" 
                                           placeholder="vd: SN-RO-2026-981..." 
                                           class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 font-mono focus:ring-2 focus:ring-blue-500 min-h-[42px]">
                                </div>

                                <!-- Water Source Type -->
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Nguồn Nước Cấp</label>
                                    <select x-model="form.water_source_type" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white min-h-[42px]">
                                        <option value="Nước máy thành phố">Nước máy thành phố (Thủy cục)</option>
                                        <option value="Nước giếng khoan đã qua lọc thô">Nước giếng khoan đã qua lọc thô</option>
                                        <option value="Nước giếng khoan trực tiếp">Nước giếng khoan trực tiếp</option>
                                        <option value="Nước mưa">Nước mưa</option>
                                        <option value="Nước ngầm / Nước suối">Nước ngầm / Nước suối</option>
                                    </select>
                                </div>

                                <!-- Warranty Months -->
                                <div>
                                    <label class="text-xs font-bold text-slate-700 block mb-1">Thời Hạn Bảo Hành Chính Hãng</label>
                                    <select x-model="form.warranty_months" class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-bold text-slate-800 min-h-[42px]">
                                        <option value="12">12 Tháng (1 Năm)</option>
                                        <option value="24" selected>24 Tháng (2 Năm - Mặc định)</option>
                                        <option value="36">36 Tháng (3 Năm - Khuyến mãi)</option>
                                    </select>
                                </div>

                                <!-- Accessories Field: Lưu dạng JSON (tên phụ kiện, số lượng, ngày lắp) -->
                                <div class="sm:col-span-2 space-y-3 pt-1">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                            <i class="fa-solid fa-boxes-stacked text-teal-600"></i>
                                            <span>Phụ Kiện Kèm Theo Máy</span>
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <template x-if="selectedAccessories.length > 0">
                                                <button type="button" @click="selectedAccessories = []; syncAccessoriesJson();" class="text-rose-500 hover:underline cursor-pointer text-[10px]">
                                                    Xóa tất cả
                                                </button>
                                            </template>
                                            <span class="text-[11px] text-teal-700 font-bold bg-teal-50 px-2 py-0.5 rounded-lg border border-teal-200">
                                                Đã chọn: <span x-text="selectedAccessories.length"></span> phụ kiện
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Searchable Accessory Combobox & Custom Part Toggle -->
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                        <!-- Search Box with Autocomplete Dropdown -->
                                        <div class="relative flex-1" @click.away="partDropdownOpen = false">
                                            <div class="relative">
                                                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                                <input type="text" 
                                                       x-model="partSearchQuery" 
                                                       @focus="partDropdownOpen = true" 
                                                       @input="partDropdownOpen = true" 
                                                       @keydown.enter.prevent="if(filteredPartsCatalog.length > 0) { addPartToAccessories(filteredPartsCatalog[0].id); }"
                                                       placeholder="🔍 Tìm phụ kiện theo tên, mã (vd: Vòi, Bơm, Bình áp...)..." 
                                                       class="w-full pl-9 pr-9 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 min-h-[42px] shadow-2xs">
                                                
                                                <!-- Clear or Dropdown toggle button -->
                                                <template x-if="partSearchQuery">
                                                    <button type="button" 
                                                            @click="partSearchQuery = ''; partDropdownOpen = true" 
                                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 flex items-center justify-center cursor-pointer">
                                                        <i class="fa-solid fa-xmark text-xs"></i>
                                                    </button>
                                                </template>
                                                <template x-if="!partSearchQuery">
                                                    <button type="button" 
                                                            @click="partDropdownOpen = !partDropdownOpen" 
                                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 hover:text-teal-600 flex items-center justify-center cursor-pointer">
                                                        <i class="fa-solid fa-chevron-down text-[11px] transition-transform duration-200" :class="{ 'rotate-180': partDropdownOpen }"></i>
                                                    </button>
                                                </template>
                                            </div>

                                            <!-- Autocomplete Suggestions List Dropdown -->
                                            <div x-show="partDropdownOpen" 
                                                 x-cloak 
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                                
                                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                                                    <span>Kho phụ tùng (<span x-text="filteredPartsCatalog.length"></span>)</span>
                                                    <span class="text-teal-700 font-normal">Chạm để chọn & thêm</span>
                                                </div>

                                                <template x-if="filteredPartsCatalog.length === 0">
                                                    <div class="p-4 text-center text-xs text-slate-500 space-y-2">
                                                        <p>Không tìm thấy phụ kiện nào với từ khóa "<strong class="text-slate-800" x-text="partSearchQuery"></strong>"</p>
                                                        <button type="button" 
                                                                @click="customPartName = partSearchQuery; showCustomPartForm = true; partDropdownOpen = false;" 
                                                                class="px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-xs rounded-lg border border-teal-200 inline-flex items-center gap-1.5 cursor-pointer">
                                                            <i class="fa-solid fa-plus"></i>
                                                            <span>Thêm "<span x-text="partSearchQuery"></span>" làm phụ kiện ngoài kho</span>
                                                        </button>
                                                    </div>
                                                </template>

                                                <template x-for="p in filteredPartsCatalog" :key="p.id">
                                                    <div @click="addPartToAccessories(p.id)" 
                                                         class="p-2.5 hover:bg-teal-50/70 cursor-pointer flex items-center justify-between gap-2.5 transition active:bg-teal-100">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 text-slate-600 border border-slate-200/60" x-text="p.code"></span>
                                                                <span class="font-bold text-slate-800 text-xs sm:text-sm truncate" x-text="p.name"></span>
                                                            </div>
                                                            <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                                                <span x-text="p.category || 'Phụ kiện'"></span>
                                                                <span>&bull;</span>
                                                                <span :class="(p.stock_qty || 0) <= 0 ? 'text-rose-600 font-bold' : 'text-teal-700 font-semibold'" 
                                                                      x-text="'Tồn kho: ' + (p.stock_qty || 0) + ' ' + (p.unit || 'Cái')"></span>
                                                            </div>
                                                        </div>
                                                        <div class="shrink-0">
                                                            <span class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold shadow-2xs hover:scale-105 transition">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Toggle button for non-catalog items -->
                                        <button type="button" 
                                                @click="showCustomPartForm = !showCustomPartForm" 
                                                class="px-4 py-2.5 rounded-xl border border-dashed border-teal-400 bg-teal-50/50 hover:bg-teal-100 text-teal-700 font-bold text-xs flex items-center justify-center gap-1.5 shrink-0 min-h-[42px] cursor-pointer transition-all shadow-2xs">
                                            <i class="fa-solid fa-circle-plus text-xs"></i>
                                            <span>Thêm Ngoài Kho</span>
                                        </button>
                                    </div>

                                    <!-- Form nhập phụ kiện ngoài kho nếu có -->
                                    <div x-show="showCustomPartForm" x-cloak class="p-3 bg-teal-50/70 rounded-xl border border-teal-200 space-y-2.5">
                                        <div class="flex items-center justify-between text-xs font-bold text-teal-900">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fa-solid fa-wrench text-teal-600"></i>
                                                <span>Thêm Phụ Kiện Ngoài Kho</span>
                                            </span>
                                            <button type="button" @click="showCustomPartForm = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                            <div class="sm:col-span-5">
                                                <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Tên Phụ Kiện <span class="text-rose-500">*</span></label>
                                                <input type="text" 
                                                       x-model="customPartName" 
                                                       placeholder="vd: Vòi gạt Inox 304..." 
                                                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                            </div>
                                            <div class="grid grid-cols-2 sm:col-span-4 gap-2">
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Số Lượng</label>
                                                    <input type="number" 
                                                           x-model.number="customPartQty" 
                                                           min="1" 
                                                           placeholder="1" 
                                                           class="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300 bg-white font-bold text-center focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                                </div>
                                                <div>
                                                    <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Đơn Vị</label>
                                                    <input type="text" 
                                                           x-model="customPartUnit" 
                                                           placeholder="Cái" 
                                                           class="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300 bg-white text-center focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                                </div>
                                            </div>
                                            <div class="sm:col-span-3">
                                                <label class="text-[10px] font-bold text-slate-600 block mb-0.5">Ngày Lắp Đặt</label>
                                                <input type="date" 
                                                       x-model="customPartDate" 
                                                       class="w-full px-2 py-1.5 text-xs rounded-lg border border-slate-300 bg-white font-mono focus:ring-2 focus:ring-teal-500 min-h-[36px]">
                                            </div>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-1 border-t border-teal-200/60">
                                            <button type="button" 
                                                    @click="showCustomPartForm = false" 
                                                    class="px-2.5 py-1 text-xs text-slate-500 hover:text-slate-700 cursor-pointer">
                                                Hủy
                                            </button>
                                            <button type="button" 
                                                    @click="addCustomAccessory()" 
                                                    :disabled="!customPartName.trim()" 
                                                    class="px-3.5 py-1.5 bg-teal-600 hover:bg-teal-700 disabled:opacity-40 text-white font-bold text-xs rounded-lg cursor-pointer transition shadow-2xs">
                                                Lưu Phụ Kiện
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Danh sách phụ kiện: Thiết kế dạng thẻ Card tối ưu hoàn hảo trên mobile -->
                                    <div class="space-y-2">
                                        <template x-if="selectedAccessories.length === 0">
                                            <div class="p-4 text-center text-xs text-slate-400 italic rounded-xl border border-dashed border-slate-200 bg-slate-50/50">
                                                <i class="fa-solid fa-boxes-stacked text-lg mb-1 block text-slate-300"></i>
                                                Chưa có phụ kiện nào được thêm. Hãy chọn từ kho hoặc bấm "Thêm Ngoài Kho".
                                            </div>
                                        </template>

                                        <template x-for="(acc, idx) in selectedAccessories" :key="idx">
                                            <div class="p-3 bg-white rounded-xl border border-slate-200/90 shadow-2xs hover:border-teal-300 transition-all">
                                                <!-- Hàng 1: Tên phụ kiện, ĐVT & Nút xóa -->
                                                <div class="flex items-start justify-between gap-2.5">
                                                    <div class="flex items-start gap-2 min-w-0 flex-1">
                                                        <span class="w-5 h-5 rounded-md bg-teal-50 text-teal-600 flex items-center justify-center text-[11px] shrink-0 mt-0.5 border border-teal-200/60">
                                                            <i class="fa-solid fa-check"></i>
                                                        </span>
                                                        <div class="min-w-0 flex-1">
                                                            <h4 class="font-bold text-slate-800 text-xs sm:text-sm leading-snug break-words" x-text="acc.name"></h4>
                                                            <template x-if="acc.unit">
                                                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200/60" x-text="'ĐVT: ' + acc.unit"></span>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <!-- Nút xóa dễ bấm trên mobile -->
                                                    <button type="button" 
                                                            @click="removeAccessory(idx)" 
                                                            class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center shrink-0 transition-all cursor-pointer border border-slate-200/80 active:scale-95" 
                                                            title="Xóa phụ kiện này">
                                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                                    </button>
                                                </div>

                                                <!-- Hàng 2: Điều chỉnh Số lượng & Chọn Ngày lắp đặt rộng rãi, không bị che khuất -->
                                                <div class="flex flex-wrap items-center justify-between gap-2.5 pt-2.5 mt-2 border-t border-slate-100 text-xs">
                                                    <!-- Bộ chỉnh Số lượng -->
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Số lượng:</span>
                                                        <div class="inline-flex items-center bg-slate-50 rounded-lg p-0.5 border border-slate-200 shadow-2xs">
                                                            <button type="button" 
                                                                    @click="changeAccessoryQty(idx, -1)" 
                                                                    class="w-7 h-7 rounded-md bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center font-black cursor-pointer shadow-2xs text-xs active:scale-90 transition border border-slate-200/60">-</button>
                                                            <span class="font-mono font-bold text-slate-900 w-8 text-center text-xs" x-text="acc.quantity"></span>
                                                            <button type="button" 
                                                                    @click="changeAccessoryQty(idx, 1)" 
                                                                    class="w-7 h-7 rounded-md bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center font-black cursor-pointer shadow-2xs text-xs active:scale-90 transition border border-slate-200/60">+</button>
                                                        </div>
                                                    </div>

                                                    <!-- Ô chọn Ngày lắp rộng rãi, đầy đủ icon lịch -->
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ngày lắp:</span>
                                                        <div class="relative inline-flex items-center">
                                                            <input type="date" 
                                                                   x-model="acc.installed_date" 
                                                                   @change="syncAccessoriesJson()" 
                                                                   class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 font-mono font-medium text-slate-800 bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-1 focus:ring-teal-500 cursor-pointer shadow-2xs">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <!-- Input hidden để submit form -->
                                    <input type="hidden" name="accessories" :value="form.accessories">
                                </div>
                            </div>

                            <!-- Real-time measured metrics (TDS in, TDS out, pressure) -->
                            <div class="p-3.5 sm:p-4 bg-blue-50/50 rounded-2xl border border-blue-200/80 space-y-3">
                                <div class="text-xs font-bold text-blue-900 flex flex-wrap items-center justify-between gap-2">
                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-gauge-high text-blue-600"></i>
                                        <span>Chỉ Số Đo Thực Tế Tại Nhà Khách</span>
                                    </span>
                                    <template x-if="tdsEfficiency !== null">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            Hiệu suất lọc: <span x-text="tdsEfficiency"></span>% (Đạt Chuẩn)
                                        </span>
                                    </template>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-3">
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-600 block mb-1">TDS Nguồn Vào (ppm)</label>
                                        <input type="number" 
                                               x-model.number="form.tds_in" 
                                               placeholder="vd: 250" 
                                               class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-mono font-bold text-slate-800 min-h-[40px]">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-600 block mb-1">TDS Nước Lọc Tinh Khiết (ppm)</label>
                                        <input type="number" 
                                               x-model.number="form.tds_out" 
                                               placeholder="vd: 12" 
                                               class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white font-mono font-bold text-emerald-700 min-h-[40px]">
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-bold text-slate-600 block mb-1">Tình Trạng Áp Lực / Bơm</label>
                                        <input type="text" 
                                               x-model="form.pressure" 
                                               placeholder="vd: Bơm chạy êm, đủ áp 2.5kg" 
                                               class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 bg-white min-h-[40px]">
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="text-xs font-bold text-slate-700 block mb-1">Ghi Chú Nghiệm Thu Của Kỹ Thuật Viên</label>
                                <textarea x-model="form.activation_notes" 
                                          rows="2" 
                                          placeholder="vd: Đã hướng dẫn khách xả rửa 3 bình đầu, máy chạy ổn định, không rò rỉ nước..." 
                                          class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 min-h-[60px]"></textarea>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-4 border-t border-slate-100 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3">
                            <button type="button" @click="closeModal()" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer text-center">
                                Hủy Bỏ
                            </button>
                            <button type="submit" 
                                    :disabled="submitting || !form.qr_code || !form.installation_id" 
                                    class="w-full sm:w-auto px-6 py-3 sm:py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer">
                                <template x-if="submitting">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                </template>
                                <template x-if="!submitting">
                                    <i class="fa-solid fa-check-circle"></i>
                                </template>
                                <span>Kích Hoạt Tem QR & Hoàn Tất Đơn Lắp Đặt</span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection