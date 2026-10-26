<!-- Include html5-qrcode scanner library -->
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>

<script>
function technicianTasksApp() {
    return {
        searchFilter: '',
        showModal: false,
        qrMode: 'camera', // 'camera' | 'manual'
        scannerInstance: null,
        submitting: false,
        successState: false,
        successMessage: '',
        successPassportUrl: '',
        activatedQrCode: '',

        installationsData: @json($installationsData),
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
        customPartMaintenanceMonths: 6,
        showCustomPartForm: false,

        // Maintenance complete modal state
        maintenanceCompleteModalOpen: false,
        maintSubmitting: false,
        maintCurrentMachine: {},
        maintItems: [],
        filterPartsList: @json($filterParts ?? []),
        selectedWarehousePartId: '',
        maintForm: {
            filter_replacement_months: 6,
            next_maintenance_date: '',
            labor_fee: 0,
            discount: 0,
            payment_status: 'paid',
            notes: ''
        },

        // Tech Warehouse Export states
        techExportSubmitting: false,
        techExportForm: {
            purpose: 'install', // 'install' | 'maintenance' | 'repair' | 'general'
            target_name: '',
            notes: '',
            items: []
        },

        // Return Part Modal states
        returnPartModalOpen: false,
        returnSubmitting: false,
        returnForm: {
            part_id: null,
            part_name: '',
            original_reference_code: '',
            quantity: 1,
            max_qty: 1,
            unit: 'Cái',
            note: ''
        },

        form: {
            installation_id: '',
            qr_code: '',
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            machine_model: '',
            machine_serial: '',
            warranty_months: 24,
            filter_replacement_months: 6,
            accessories: '[]',
            activation_notes: ''
        },

        initApp() {
            const urlParams = new URLSearchParams(window.location.search);
            const openInstallId = urlParams.get('install_id');
            if (openInstallId) {
                this.openQrModal(openInstallId);
            }
            if (urlParams.get('tab') === 'warehouse_export' && this.techExportForm.items.length === 0) {
                this.addTechExportItemRow();
            }
        },

        matchesSearch(text) {
            if (!this.searchFilter) return true;
            const q = this.searchFilter.toLowerCase().trim();
            return (text || '').toLowerCase().includes(q);
        },

        parseMaintMonths(val, fallback = 6) {
            if (val !== undefined && val !== null && val !== '' && !isNaN(val)) {
                return parseInt(val);
            }
            return fallback;
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
                    unit_price: 0,
                    is_free: true,
                    maintenance_months: this.parseMaintMonths(part.warranty_months ?? part.replacement_period_months, 6),
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
                unit_price: 0,
                is_free: true,
                maintenance_months: this.parseMaintMonths(this.customPartMaintenanceMonths, 6),
                installed_date: this.customPartDate || new Date().toISOString().slice(0, 10)
            });
            this.customPartName = '';
            this.customPartQty = 1;
            this.customPartUnit = 'Cái';
            this.customPartMaintenanceMonths = 6;
            this.customPartDate = new Date().toISOString().slice(0, 10);
            this.showCustomPartForm = false;
            this.syncAccessoriesJson();
        },

        toggleAccessoryFree(index) {
            if (this.selectedAccessories[index]) {
                const acc = this.selectedAccessories[index];
                acc.is_free = !acc.is_free;
                if (acc.is_free) {
                    acc.unit_price = 0;
                } else {
                    if (acc.id) {
                        const part = this.partsCatalog.find(p => p.id == acc.id);
                        acc.unit_price = part ? Number(part.out_price || 0) : 0;
                    }
                }
                this.syncAccessoriesJson();
            }
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
            this.resetForm();
            this.qrMode = 'camera';

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
                this.form.warranty_months = inst.warranty_months || (inst.installed_machine?.warranty_months || 24);
                this.form.activation_notes = inst.completion_notes || '';
                
                if (inst.items && inst.items.length > 0) {
                    const defaultDate = inst.installed_at ? String(inst.installed_at).slice(0, 10) : new Date().toISOString().slice(0, 10);
                    this.selectedAccessories = inst.items.map(it => {
                        const isFree = (it.is_free !== undefined && it.is_free !== null) ? Boolean(it.is_free) : (Number(it.unit_price || 0) === 0);
                        return {
                            id: it.part_id,
                            code: it.part_code || '',
                            name: it.part_name || '',
                            quantity: parseInt(it.quantity) || 1,
                            unit: it.unit || 'Cái',
                            unit_price: isFree ? 0 : Number(it.unit_price || 0),
                            is_free: isFree,
                            maintenance_months: this.parseMaintMonths(it.maintenance_months ?? it.warranty_months, 6),
                            installed_date: defaultDate
                        };
                    });
                    this.syncAccessoriesJson();
                } else if (inst.installed_machine?.accessories) {
                    try {
                        const parsed = JSON.parse(inst.installed_machine.accessories);
                        if (Array.isArray(parsed)) {
                            this.selectedAccessories = parsed.map(p => ({
                                name: p.name || '',
                                quantity: parseInt(p.quantity || p.qty) || 1,
                                unit: p.unit || 'Cái',
                                maintenance_months: this.parseMaintMonths(p.maintenance_months ?? p.warranty_months, 6),
                                installed_date: p.installed_date || (inst.installed_at ? String(inst.installed_at).slice(0, 10) : new Date().toISOString().slice(0, 10))
                            }));
                        } else {
                            this.selectedAccessories = [{
                                name: inst.installed_machine.accessories,
                                quantity: 1,
                                unit: 'Cái',
                                maintenance_months: 6,
                                installed_date: new Date().toISOString().slice(0, 10)
                            }];
                        }
                    } catch(e) {
                        this.selectedAccessories = [{
                            name: inst.installed_machine.accessories,
                            quantity: 1,
                            unit: 'Cái',
                            maintenance_months: 6,
                            installed_date: new Date().toISOString().slice(0, 10)
                        }];
                    }
                    this.syncAccessoriesJson();
                }

                // Nếu đơn này đã từng dán tem QR đã kích hoạt thì load mã, ngược lại xóa trắng
                if (inst.installed_machine?.qr_code && inst.installed_machine?.status === 'activated') {
                    this.form.qr_code = this.extractQrCode(inst.installed_machine.qr_code);
                } else {
                    this.form.qr_code = '';
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
            this.resetForm();
            this.qrMode = 'camera';
        },

        resetForm() {
            this.selectedAccessories = [];
            this.selectedPartId = '';
            this.partSearchQuery = '';
            this.partDropdownOpen = false;
            this.customPartName = '';
            this.customPartQty = 1;
            this.customPartUnit = 'Cái';
            this.customPartMaintenanceMonths = 6;
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
                this.form.warranty_months = found.warranty_months || (found.installed_machine?.warranty_months || 24);
                this.form.activation_notes = found.completion_notes || '';

                if (found.items && found.items.length > 0) {
                    const today = new Date().toISOString().slice(0, 10);
                    this.selectedAccessories = found.items.map(it => {
                        const isFree = (it.is_free !== undefined && it.is_free !== null) ? Boolean(it.is_free) : (Number(it.unit_price || 0) === 0);
                        return {
                            id: it.part_id,
                            code: it.part_code || '',
                            name: it.part_name || '',
                            quantity: parseInt(it.quantity) || 1,
                            unit: it.unit || 'Cái',
                            unit_price: isFree ? 0 : Number(it.unit_price || 0),
                            is_free: isFree,
                            maintenance_months: this.parseMaintMonths(it.maintenance_months ?? it.warranty_months, 6),
                            installed_date: today
                        };
                    });
                    this.syncAccessoriesJson();
                } else if (found.installed_machine?.accessories) {
                    try {
                        const parsed = JSON.parse(found.installed_machine.accessories);
                        if (Array.isArray(parsed)) {
                            this.selectedAccessories = parsed.map(p => ({
                                name: p.name || '',
                                quantity: parseInt(p.quantity || p.qty) || 1,
                                unit: p.unit || 'Cái',
                                maintenance_months: this.parseMaintMonths(p.maintenance_months ?? p.warranty_months, 6),
                                installed_date: p.installed_date || new Date().toISOString().slice(0, 10)
                            }));
                        } else {
                            this.selectedAccessories = [{
                                name: found.installed_machine.accessories,
                                quantity: 1,
                                unit: 'Cái',
                                maintenance_months: 6,
                                installed_date: new Date().toISOString().slice(0, 10)
                            }];
                        }
                    } catch(e) {
                        this.selectedAccessories = [{
                            name: found.installed_machine.accessories,
                            quantity: 1,
                            unit: 'Cái',
                            maintenance_months: 6,
                            installed_date: new Date().toISOString().slice(0, 10)
                        }];
                    }
                    this.syncAccessoriesJson();
                }

                if (found.installed_machine?.qr_code && found.installed_machine?.status === 'activated') {
                    this.form.qr_code = this.extractQrCode(found.installed_machine.qr_code);
                } else {
                    this.form.qr_code = '';
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
                    this.form.qr_code = '';
                } else {
                    alert(res.message || 'Có lỗi xảy ra khi liên kết mã QR.');
                }
            } catch (err) {
                console.error("Error submitting bind QR:", err);
                alert('Lỗi kết nối máy chủ. Vui lòng thử lại!');
            } finally {
                this.submitting = false;
            }
        },

        async claimMaintenanceJob(id, customerName = '') {
            const confirmed = confirm(`Bạn có chắc chắn muốn nhận phụ trách bảo dưỡng cho khách hàng "${customerName || 'này'}" không?`);
            if (!confirmed) return;

            try {
                const response = await fetch(`/api/water-filter/maintenance/${id}/claim`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    alert(res.message || 'Nhận đơn bảo dưỡng thành công!');
                    window.location.reload();
                } else {
                    alert(res.message || 'Không thể nhận đơn. Vui lòng thử lại!');
                }
            } catch (err) {
                console.error("Error claiming maintenance:", err);
                alert('Lỗi kết nối máy chủ khi nhận đơn!');
            }
        },

        openCompleteMaintenanceModal(machine) {
            this.maintCurrentMachine = machine || {};
            const rMonths = parseInt(machine.filter_replacement_months) || 6;
            
            // Tính ngày tiếp theo mặc định
            const d = new Date();
            d.setMonth(d.getMonth() + rMonths);
            const defaultNextDate = d.toISOString().slice(0, 10);

            this.maintForm = {
                filter_replacement_months: rMonths,
                next_maintenance_date: defaultNextDate,
                labor_fee: 0,
                discount: 0,
                payment_status: 'paid',
                notes: ''
            };
            this.selectedWarehousePartId = '';
            this.maintItems = [];

            // Tự động nạp danh sách linh kiện của máy
            this.loadMachineAccessories();

            this.maintenanceCompleteModalOpen = true;
        },

        closeCompleteMaintenanceModal() {
            if (this.maintSubmitting) return;
            this.maintenanceCompleteModalOpen = false;
        },

        setMaintMonths(months) {
            this.maintForm.filter_replacement_months = months;
            const d = new Date();
            d.setMonth(d.getMonth() + months);
            this.maintForm.next_maintenance_date = d.toISOString().slice(0, 10);
        },

        loadMachineAccessories() {
            const rawAcc = this.maintCurrentMachine.accessories || [];
            const defaultParts = this.maintCurrentMachine.default_parts || [];
            const list = rawAcc.length > 0 ? rawAcc : defaultParts;

            if (!list || list.length === 0) {
                return;
            }

            this.maintItems = list.map(item => {
                const name = item.name || item.part_name || '';
                // Tìm trong kho xem có trùng tên hoặc mã không để lấy giá xuất kho
                const matchedPart = this.filterPartsList.find(p => 
                    (item.part_id && p.id == item.part_id) || 
                    (p.name && name && p.name.toLowerCase() === name.toLowerCase())
                );

                const outPrice = matchedPart ? parseFloat(matchedPart.out_price || 0) : (parseFloat(item.unit_price) || 0);
                const isFree = item.is_free !== undefined ? Boolean(item.is_free) : true;

                return {
                    part_id: matchedPart ? matchedPart.id : (item.part_id || null),
                    part_code: matchedPart ? matchedPart.code : (item.part_code || ''),
                    part_name: name,
                    unit: item.unit || (matchedPart ? matchedPart.unit : 'Lõi'),
                    quantity: parseInt(item.quantity) || 1,
                    is_free: isFree,
                    unit_price: outPrice,
                    note: isFree ? 'Thay định kỳ miễn phí' : 'Thay thế tính phí',
                    showDropdown: false
                };
            });
        },

        addSelectedWarehousePart() {
            if (!this.selectedWarehousePartId) return;
            const part = this.filterPartsList.find(p => p.id == this.selectedWarehousePartId);
            if (!part) return;

            this.maintItems.push({
                part_id: part.id,
                part_code: part.code || '',
                part_name: part.name,
                unit: part.unit || 'Cái',
                quantity: 1,
                is_free: false, // Thêm chủ động từ kho mặc định là tính tiền
                unit_price: parseFloat(part.out_price || 0),
                note: '',
                showDropdown: false
            });

            this.selectedWarehousePartId = '';
        },

        addCustomMaintPartRow() {
            this.maintItems.push({
                part_id: null,
                part_code: '',
                part_name: '',
                unit: 'Cái',
                quantity: 1,
                is_free: false,
                unit_price: 0,
                note: '',
                showDropdown: false
            });
        },

        getFilteredPartsForMaint(keyword = '') {
            if (!keyword || !keyword.trim()) {
                return (this.filterPartsList || []).slice(0, 25);
            }
            const q = keyword.toLowerCase().trim();
            return (this.filterPartsList || []).filter(p => {
                const name = (p.name || '').toLowerCase();
                const code = (p.code || '').toLowerCase();
                const cat = (p.category || '').toLowerCase();
                return name.includes(q) || code.includes(q) || cat.includes(q);
            }).slice(0, 25);
        },

        selectPartForItem(item, part) {
            item.part_id = part.id;
            item.part_code = part.code || '';
            item.part_name = part.name;
            item.unit = part.unit || item.unit || 'Cái';
            if (!item.is_free) {
                item.unit_price = parseFloat(part.out_price || 0);
            }
            item.showDropdown = false;
        },

        removeMaintItemRow(index) {
            this.maintItems.splice(index, 1);
        },

        setPartPricing(item, isFree) {
            item.is_free = isFree;
            if (!isFree) {
                // nếu tính tiền mà đơn giá đang là 0 thì tìm lại giá kho nếu có
                if (!item.unit_price || item.unit_price === 0) {
                    const matchedPart = this.filterPartsList.find(p => 
                        (item.part_id && p.id == item.part_id) || 
                        (p.name && item.part_name && p.name.toLowerCase() === item.part_name.toLowerCase())
                    );
                    if (matchedPart && matchedPart.out_price) {
                        item.unit_price = parseFloat(matchedPart.out_price);
                    }
                }
            }
        },

        calcMaintPartsFee() {
            let total = 0;
            for (const item of this.maintItems) {
                if (!item.is_free) {
                    const qty = parseInt(item.quantity) || 0;
                    const price = parseFloat(item.unit_price) || 0;
                    total += qty * price;
                }
            }
            return total;
        },

        calcMaintTotalAmount() {
            const partsFee = this.calcMaintPartsFee();
            const labor = parseFloat(this.maintForm.labor_fee) || 0;
            const discount = parseFloat(this.maintForm.discount) || 0;
            const total = Math.max(0, partsFee + labor - discount);

            // Tự động chỉnh trạng thái thanh toán nếu tổng tiền = 0
            if (total === 0 && this.maintForm.payment_status !== 'free') {
                this.maintForm.payment_status = 'free';
            } else if (total > 0 && this.maintForm.payment_status === 'free') {
                this.maintForm.payment_status = 'paid';
            }

            return total;
        },

        async submitCompleteMaintenance() {
            if (!this.maintCurrentMachine || !this.maintCurrentMachine.id) {
                alert('Không tìm thấy thông tin máy bảo dưỡng!');
                return;
            }

            // Kiểm tra tên các phụ tùng nhập
            for (let i = 0; i < this.maintItems.length; i++) {
                if (!this.maintItems[i].part_name || !this.maintItems[i].part_name.trim()) {
                    alert(`Vui lòng nhập tên cho linh kiện ở dòng số ${i + 1} hoặc xóa dòng đó.`);
                    return;
                }
            }

            this.maintSubmitting = true;
            try {
                const totalAmount = this.calcMaintTotalAmount();
                const partsFee = this.calcMaintPartsFee();

                const payload = {
                    filter_replacement_months: this.maintForm.filter_replacement_months,
                    next_maintenance_date: this.maintForm.next_maintenance_date,
                    notes: this.maintForm.notes,
                    items: this.maintItems,
                    parts_fee: partsFee,
                    labor_fee: parseFloat(this.maintForm.labor_fee) || 0,
                    discount: parseFloat(this.maintForm.discount) || 0,
                    total_amount: totalAmount,
                    payment_status: this.maintForm.payment_status
                };

                const response = await fetch(`/api/water-filter/maintenance/${this.maintCurrentMachine.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const res = await response.json();
                if (res.success) {
                    alert(res.message || 'Ghi nhận hoàn tất bảo dưỡng thành công!');
                    this.maintenanceCompleteModalOpen = false;
                    window.location.reload();
                } else {
                    alert(res.message || 'Không thể lưu bảo dưỡng. Vui lòng thử lại!');
                }
            } catch (err) {
                console.error("Error submitting complete maintenance:", err);
                alert('Lỗi kết nối máy chủ khi lưu bảo dưỡng!');
            } finally {
                this.maintSubmitting = false;
            }
        },

        async completeMaintenanceJob(id, customerName = '', months = 6) {
            // Fallback mở modal
            this.openCompleteMaintenanceModal({
                id: id,
                customer_name: customerName,
                filter_replacement_months: months
            });
        },

        async rescheduleMaintenanceJob(id, customerName = '', currentDate = '') {
            const defaultDate = currentDate ? currentDate.substring(0, 10) : new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 10);
            const newDate = prompt(`Nhập ngày hẹn bảo dưỡng mới cho khách hàng "${customerName}" (Định dạng YYYY-MM-DD):`, defaultDate);
            if (!newDate || !newDate.trim()) return;

            try {
                const response = await fetch(`/api/water-filter/maintenance/${id}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        next_maintenance_date: newDate.trim(),
                        notes: 'KTV hẹn lại lịch bảo dưỡng với khách hàng'
                    })
                });

                const res = await response.json();
                if (res.success) {
                    alert(res.message || 'Đã dời ngày hẹn bảo dưỡng thành công!');
                    window.location.reload();
                } else {
                    alert(res.message || 'Không thể dời ngày hẹn!');
                }
            } catch (err) {
                console.error("Error rescheduling maintenance:", err);
                alert('Lỗi kết nối máy chủ!');
            }
        },

        // ==========================
        // TECH WAREHOUSE EXPORT & RETURN METHODS
        // ==========================
        scrollToCreateExportForm() {
            const el = document.getElementById('form-create-tech-export');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
            }
            if (this.techExportForm.items.length === 0) {
                this.addTechExportItemRow();
            }
        },

        resetTechExportForm() {
            this.techExportForm = {
                purpose: 'install',
                target_name: '',
                notes: '',
                items: [
                    {
                        part_id: null,
                        part_name: '',
                        part_code: '',
                        unit: 'Cái',
                        quantity: 1,
                        current_stock: 0,
                        note: '',
                        showDropdown: false
                    }
                ]
            };
        },

        addTechExportItemRow() {
            this.techExportForm.items.push({
                part_id: null,
                part_name: '',
                part_code: '',
                unit: 'Cái',
                quantity: 1,
                current_stock: 0,
                note: '',
                showDropdown: false
            });
        },

        removeTechExportItemRow(index) {
            this.techExportForm.items.splice(index, 1);
        },

        selectTechExportPart(item, part) {
            item.part_id = part.id;
            item.part_name = part.name;
            item.part_code = part.code || '';
            item.unit = part.unit || 'Cái';
            item.current_stock = part.stock_qty || 0;
            item.quantity = Math.min(Math.max(1, item.quantity || 1), Math.max(1, part.stock_qty || 1));
            item.showDropdown = false;
        },

        async submitTechExport() {
            if (this.techExportForm.items.length === 0) {
                alert('Vui lòng thêm ít nhất 1 linh kiện cần lấy!');
                return;
            }

            for (let i = 0; i < this.techExportForm.items.length; i++) {
                const it = this.techExportForm.items[i];
                if (!it.part_id) {
                    alert(`Vui lòng chọn linh kiện từ kho cho dòng số ${i + 1}!`);
                    return;
                }
                if (it.quantity <= 0) {
                    alert(`Số lượng lấy ở dòng số ${i + 1} phải lớn hơn 0!`);
                    return;
                }
                if (it.quantity > it.current_stock) {
                    alert(`Linh kiện "${it.part_name}" chỉ còn tồn ${it.current_stock} trong kho, không đủ xuất ${it.quantity}!`);
                    return;
                }
            }

            this.techExportSubmitting = true;
            try {
                const response = await fetch('/api/water-filter/parts/tech-export', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.techExportForm)
                });

                const res = await response.json();
                if (res.success) {
                    alert(res.message || 'Xuất kho lấy hàng thành công!');
                    window.location.reload();
                } else {
                    alert(res.message || 'Lỗi khi xuất kho lấy hàng. Vui lòng thử lại!');
                }
            } catch (err) {
                console.error("Error tech export:", err);
                alert('Lỗi kết nối máy chủ khi xuất kho!');
            } finally {
                this.techExportSubmitting = false;
            }
        },

        openReturnModal(partId, partName, refCode, maxQty, unit) {
            this.returnForm = {
                part_id: partId,
                part_name: partName,
                original_reference_code: refCode,
                quantity: Math.max(1, maxQty),
                max_qty: maxQty,
                unit: unit || 'Cái',
                note: ''
            };
            this.returnPartModalOpen = true;
        },

        async submitReturnPart() {
            if (!this.returnForm.part_id || this.returnForm.quantity <= 0) {
                alert('Số lượng hoàn trả không hợp lệ!');
                return;
            }

            this.returnSubmitting = true;
            try {
                const response = await fetch('/api/water-filter/parts/tech-return', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.returnForm)
                });

                const res = await response.json();
                if (res.success) {
                    alert(res.message || 'Hoàn trả linh kiện về kho thành công!');
                    this.returnPartModalOpen = false;
                    window.location.reload();
                } else {
                    alert(res.message || 'Lỗi hoàn trả kho. Vui lòng thử lại!');
                }
            } catch (err) {
                console.error("Error returning part:", err);
                alert('Lỗi kết nối máy chủ!');
            } finally {
                this.returnSubmitting = false;
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