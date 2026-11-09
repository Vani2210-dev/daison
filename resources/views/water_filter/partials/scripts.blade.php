<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- JAVASCRIPT & ALPINE.JS LOGIC -->
<script>
function waterFilterModule() {
    return {
        activeTab: '{{ $tab ?? "installations" }}',

        // Data arrays
        installations: [],
        installedMachines: [],
        installedMachinesStats: { total: 0, active: 0, expiring_soon: 0, expired: 0 },
        maintenanceList: [],
        maintenanceStats: { total: 0, overdue: 0, due_7_days: 0, due_30_days: 0, unclaimed: 0, my_jobs: 0 },
        maintenanceAreaSuggestions: [],
        canAssignMaint: false,
        currentUserId: null,
        repairs: [],
        parts: @json($parts ?? []),
        machineModels: @json($machineModels ?? []),
        statuses: @json($statuses ?? []),
        technicians: @json($technicians ?? []),
        transactions: [],

        // Search & Filter models
        installSearch: '',
        installStatus: '',
        installedSearch: '',
        installedWarrantyStatus: '',
        installedTechId: '',
        maintSearch: '',
        maintStatusFilter: 'all',
        maintTechFilter: '',
        maintAreaFilter: '',
        rescheduleTarget: null,
        rescheduleForm: {
            id: null,
            next_maintenance_date: '',
            filter_replacement_months: 6,
            technician_id: '',
            notes: ''
        },
        reminderTarget: null,
        reminderTemplateType: 'friendly',
        reminderMessage: '',
        copiedMessage: false,
        modelSearch: '',
        modelBrand: '',
        repairSearch: '',
        repairStatus: '',
        repairWarrantyType: '',
        partSearch: '',
        partCategory: '',
        partFilterLowStock: false,
        transSearch: '',
        transType: '',

        // Modals state
        modals: {
            installModal: false,
            inspectModal: false,
            repairModal: false,
            repairUpdateModal: false,
            partModal: false,
            importModal: false,
            machineModelModal: false,
            warrantyEditModal: false,
            batchQrModal: false,
            qrPreviewModal: false,
            activateModal: false,
            machineEditModal: false,
            rescheduleModal: false,
            reminderModal: false,
        },

        // QR Selection & Batch creation
        selectedMachineIds: [],
        batchQrForm: {
            quantity: 10,
            machine_model_id: '',
            warranty_months: 24,
            admin_notes: 'Tạo tem QR dán máy mới'
        },
        previewMachine: null,
        activateData: null,
        activateForm: {
            installed_at: '',
            warranty_months: 24,
            warranty_until: '',
            warranty_card_code: '',
            machine_serial: '',
            filter_replacement_months: 6,
            next_maintenance_date: '',
            admin_notes: ''
        },
        machineEditForm: {
            id: null,
            machine_code: '',
            qr_code: '',
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            machine_model_id: '',
            machine_model: '',
            machine_serial: '',
            accessories: '',
            technician_id: '',
            installed_at: '',
            warranty_months: 24,
            warranty_until: '',
            warranty_card_code: '',
            filter_replacement_months: 6,
            next_maintenance_date: '',
            warranty_notes: '',
            status: 'activated',
            admin_notes: ''
        },

        // Warranty Edit Form
        warrantyEditForm: {
            id: null,
            code: '',
            customer_name: '',
            machine_model: '',
            machine_serial: '',
            installed_at: '',
            warranty_months: 24,
            warranty_until: '',
            warranty_card_code: '',
            warranty_notes: '',
            filter_replacement_months: 6,
            next_maintenance_date: ''
        },

        // Machine Model Form
        activeModelDropdownIdx: null,
        modelForm: {
            id: null,
            code: '',
            brand: '',
            name: '',
            filter_stages: '',
            type: '',
            standard_price: '',
            description: '',
            default_parts: []
        },

        // Forms
        installForm: {
            id: null,
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            machine_model: '',
            machine_serial: '',
            technician_id: '',
            appointment_date: '',
            customer_notes: '',
            items: []
        },

        inspectData: {},
        inspectForm: {
            status: 'completed',
            completion_notes: ''
        },

        repairForm: {
            id: null,
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            repair_type: 'at_shop',
            warranty_type: 'out_warranty',
            machine_model: '',
            machine_serial: '',
            technician_id: '',
            symptom: '',
            labor_fee: 100000,
            discount: 0,
            items: []
        },

        repairUpdateData: {},
        repairUpdateForm: {
            status: 'completed',
            diagnosis: '',
            solution: '',
            payment_status: 'paid',
            warranty_until: '',
        },

        partForm: {
            id: null,
            code: '',
            name: '',
            category: 'Lõi lọc thô',
            unit: 'Cái',
            in_price: 0,
            out_price: 0,
            stock_qty: 0,
            min_stock_alert: 5,
            location: '',
            description: ''
        },

        importForm: {
            part_id: '',
            quantity: 10,
            in_price: 0,
            supplier: '',
            note: ''
        },

        init() {
            this.fetchInstallations();
            this.fetchInstalledMachines();
            this.fetchMaintenance();
            this.fetchRepairs();
            this.fetchParts();
            this.fetchMachineModels();
            this.fetchTransactions();
        },

        changeTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
        },

        openModal(name) {
            this.modals[name] = true;
        },

        closeModal(name) {
            this.modals[name] = false;
        },

        formatDateTime(str) {
            if (!str) return '-';
            const d = new Date(str);
            if (isNaN(d)) return str;
            return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        },

        /* ---------------------------------------------------------
         * INSTALLATIONS LOGIC
         * --------------------------------------------------------- */
        async fetchInstallations() {
            try {
                let url = `/api/water-filter/installations?search=${encodeURIComponent(this.installSearch)}&status=${this.installStatus}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.installations = data.installations.data || data.installations;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openInstallModal() {
            this.installForm = {
                id: null,
                customer_name: '',
                customer_phone: '',
                customer_address: '',
                machine_model: '',
                machine_serial: '',
                technician_id: '',
                appointment_date: '',
                customer_notes: '',
                items: []
            };
            this.openModal('installModal');
        },

        editInstallation(item) {
            this.installForm = {
                id: item.id,
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                customer_address: item.customer_address,
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                technician_id: item.technician_id || '',
                appointment_date: item.appointment_date ? item.appointment_date.replace(' ', 'T').substring(0, 16) : '',
                customer_notes: item.customer_notes || '',
                items: (item.items || []).map(i => ({
                    part_id: i.part_id,
                    part_name: i.part_name,
                    unit: i.unit,
                    quantity: i.quantity,
                    unit_price: i.unit_price,
                    note: i.note
                }))
            };
            this.openModal('installModal');
        },

        addInstallItemRow() {
            this.installForm.items.push({
                part_id: '',
                part_name: '',
                unit: 'Cái',
                quantity: 1,
                unit_price: 0,
                note: ''
            });
        },

        removeInstallItemRow(idx) {
            this.installForm.items.splice(idx, 1);
        },

        onSelectInstallPart(idx, partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.installForm.items[idx].part_id = found.id;
                this.installForm.items[idx].part_name = found.name;
                this.installForm.items[idx].unit = found.unit;
                this.installForm.items[idx].unit_price = found.out_price;
            }
        },

        async saveInstallationSubmit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/installations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.installForm)
                });
                let data = {};
                try { data = await res.json(); } catch (err) {}

                if (!res.ok || !data.success) {
                    let errMsg = data.message || 'Lỗi lưu đơn hàng';
                    if (data.errors) {
                        errMsg += '\n• ' + Object.values(data.errors).flat().join('\n• ');
                    }
                    alert(errMsg);
                    return;
                }

                alert(data.message);
                this.closeModal('installModal');
                this.fetchInstallations();
            } catch (e) {
                alert('Có lỗi kết nối máy chủ: ' + (e.message || ''));
            }
        },

        openInspectModal(item) {
            this.inspectData = item;
            this.inspectForm = {
                status: 'completed',
                completion_notes: item.completion_notes || ''
            };
            this.openModal('inspectModal');
        },

        async submitInspect() {
            try {
                const res = await fetch(`/api/water-filter/installations/${this.inspectData.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.inspectForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('inspectModal');
                    this.fetchInstallations();
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi cập nhật');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deleteInstallation(id) {
            if (!confirm('Bạn có chắc muốn xóa đơn lắp đặt này?')) return;
            try {
                const res = await fetch(`/api/water-filter/installations/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchInstallations();
                }
            } catch (e) {
                alert('Lỗi xóa đơn');
            }
        },

        getInstallStatusBadge(st) {
            if (this.statuses && this.statuses[st]) {
                return this.statuses[st].badge;
            }
            switch (st) {
                case 'pending': return 'bg-amber-100 text-amber-800';
                case 'confirmed': return 'bg-sky-100 text-sky-800';
                case 'assigned': return 'bg-blue-100 text-blue-800';
                case 'preparing': return 'bg-indigo-100 text-indigo-800';
                case 'dispatching': return 'bg-purple-100 text-purple-800';
                case 'in_progress': return 'bg-cyan-100 text-cyan-800';
                case 'inspecting': return 'bg-teal-100 text-teal-800';
                case 'completed': return 'bg-emerald-100 text-emerald-800';
                case 'rescheduled': return 'bg-orange-100 text-orange-800';
                case 'cancelled': return 'bg-rose-100 text-rose-800';
                default: return 'bg-slate-100 text-slate-700';
            }
        },

        getInstallStatusText(st) {
            if (this.statuses && this.statuses[st]) {
                return this.statuses[st].label;
            }
            switch (st) {
                case 'pending': return 'Mới tiếp nhận';
                case 'confirmed': return 'Đã hẹn lịch';
                case 'assigned': return 'Đã giao việc';
                case 'preparing': return 'Đang chuẩn bị';
                case 'dispatching': return 'Đang di chuyển';
                case 'in_progress': return 'Đang lắp đặt';
                case 'inspecting': return 'Nghiệm thu TDS';
                case 'completed': return 'Đã hoàn thành';
                case 'rescheduled': return 'Khách hẹn lại';
                case 'cancelled': return 'Đã hủy';
                default: return st;
            }
        },

        /* ---------------------------------------------------------
         * REPAIRS LOGIC
         * --------------------------------------------------------- */
        async fetchRepairs() {
            try {
                let url = `/api/water-filter/repairs?search=${encodeURIComponent(this.repairSearch)}&status=${this.repairStatus}&warranty_type=${this.repairWarrantyType}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.repairs = data.repairs.data || data.repairs;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openRepairModal() {
            this.repairForm = {
                id: null,
                customer_name: '',
                customer_phone: '',
                customer_address: '',
                repair_type: 'at_shop',
                warranty_type: 'out_warranty',
                machine_model: '',
                machine_serial: '',
                technician_id: '',
                symptom: '',
                labor_fee: 100000,
                discount: 0,
                items: []
            };
            this.openModal('repairModal');
        },

        editRepair(item) {
            this.repairForm = {
                id: item.id,
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                customer_address: item.customer_address || '',
                repair_type: item.repair_type,
                warranty_type: item.warranty_type,
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                technician_id: item.technician_id || '',
                symptom: item.symptom,
                labor_fee: item.labor_fee || 0,
                discount: item.discount || 0,
                items: (item.items || []).map(i => ({
                    part_id: i.part_id,
                    part_name: i.part_name,
                    unit: i.unit,
                    quantity: i.quantity,
                    unit_price: i.unit_price,
                    is_warranty: !!i.is_warranty
                }))
            };
            this.openModal('repairModal');
        },

        addRepairItemRow() {
            this.repairForm.items.push({
                part_id: '',
                part_name: '',
                unit: 'Cái',
                quantity: 1,
                unit_price: 0,
                is_warranty: false
            });
        },

        removeRepairItemRow(idx) {
            this.repairForm.items.splice(idx, 1);
        },

        onSelectRepairPart(idx, partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.repairForm.items[idx].part_id = found.id;
                this.repairForm.items[idx].part_name = found.name;
                this.repairForm.items[idx].unit = found.unit;
                this.repairForm.items[idx].unit_price = found.out_price;
            }
        },

        async saveRepairSubmit() {
            try {
                const res = await fetch('/api/water-filter/repairs', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.repairForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('repairModal');
                    this.fetchRepairs();
                } else {
                    alert(data.message || 'Lỗi lưu phiếu');
                }
            } catch (e) {
                alert('Có lỗi kết nối máy chủ');
            }
        },

        openRepairUpdateModal(item) {
            this.repairUpdateData = item;
            this.repairUpdateForm = {
                status: item.status === 'completed' ? 'delivered' : 'completed',
                diagnosis: item.diagnosis || '',
                solution: item.solution || '',
                payment_status: item.payment_status || 'unpaid',
                warranty_until: item.warranty_until || '',
            };
            this.openModal('repairUpdateModal');
        },

        async submitRepairUpdate() {
            try {
                const res = await fetch(`/api/water-filter/repairs/${this.repairUpdateData.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.repairUpdateForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('repairUpdateModal');
                    this.fetchRepairs();
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi cập nhật');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deleteRepair(id) {
            if (!confirm('Bạn có chắc muốn xóa phiếu sửa chữa này?')) return;
            try {
                const res = await fetch(`/api/water-filter/repairs/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchRepairs();
                }
            } catch (e) {
                alert('Lỗi xóa phiếu');
            }
        },

        getRepairStatusBadge(st) {
            switch (st) {
                case 'received': return 'bg-amber-100 text-amber-800';
                case 'inspecting': return 'bg-blue-100 text-blue-800';
                case 'waiting_parts': return 'bg-purple-100 text-purple-800';
                case 'repairing': return 'bg-indigo-100 text-indigo-800';
                case 'completed': return 'bg-emerald-100 text-emerald-800';
                case 'delivered': return 'bg-teal-100 text-teal-800';
                case 'cancelled': return 'bg-rose-100 text-rose-800';
                default: return 'bg-slate-100 text-slate-700';
            }
        },

        getRepairStatusText(st) {
            switch (st) {
                case 'received': return 'Tiếp nhận';
                case 'inspecting': return 'Đang K.tra';
                case 'waiting_parts': return 'Chờ l.kiện';
                case 'repairing': return 'Đang sửa';
                case 'completed': return 'Sửa xong';
                case 'delivered': return 'Đã trả khách';
                case 'cancelled': return 'Hủy sửa';
                default: return st;
            }
        },

        /* ---------------------------------------------------------
         * PARTS WAREHOUSE LOGIC
         * --------------------------------------------------------- */
        async fetchParts() {
            try {
                let url = `/api/water-filter/parts?search=${encodeURIComponent(this.partSearch)}&category=${encodeURIComponent(this.partCategory)}&low_stock=${this.partFilterLowStock ? '1' : '0'}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.parts = data.parts;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openPartModal() {
            this.partForm = {
                id: null,
                code: '',
                name: '',
                category: 'Lõi lọc thô',
                unit: 'Cái',
                in_price: 0,
                out_price: 0,
                stock_qty: 0,
                min_stock_alert: 5,
                location: '',
                description: ''
            };
            this.openModal('partModal');
        },

        editPart(item) {
            this.partForm = {
                id: item.id,
                code: item.code,
                name: item.name,
                category: item.category,
                unit: item.unit,
                in_price: item.in_price,
                out_price: item.out_price,
                stock_qty: item.stock_qty,
                min_stock_alert: item.min_stock_alert,
                location: item.location || '',
                description: item.description || ''
            };
            this.openModal('partModal');
        },

        async savePartSubmit() {
            try {
                const res = await fetch('/api/water-filter/parts', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.partForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('partModal');
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi lưu phụ tùng');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deletePart(id) {
            if (!confirm('Bạn có chắc muốn xóa phụ tùng này?')) return;
            try {
                const res = await fetch(`/api/water-filter/parts/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchParts();
                }
            } catch (e) {
                alert('Lỗi xóa');
            }
        },

        quickImport(item) {
            this.importForm = {
                part_id: item.id,
                quantity: 10,
                in_price: item.in_price,
                supplier: '',
                note: 'Nhập bổ sung kho'
            };
            this.openModal('importModal');
        },

        onSelectImportPart(partId) {
            const found = this.parts.find(p => p.id == partId);
            if (found) {
                this.importForm.in_price = found.in_price;
            }
        },

        async submitImportStock() {
            try {
                const res = await fetch('/api/water-filter/parts/import', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.importForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('importModal');
                    this.fetchParts();
                    this.fetchTransactions();
                } else {
                    alert(data.message || 'Lỗi nhập kho');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        /* ---------------------------------------------------------
         * TRANSACTIONS LOGIC
         * --------------------------------------------------------- */
        async fetchTransactions() {
            try {
                let url = `/api/water-filter/transactions?search=${encodeURIComponent(this.transSearch)}&type=${this.transType}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.transactions = data.transactions.data || data.transactions;
                }
            } catch (e) {
                console.error(e);
            }
        },

        getTransBadge(type) {
            switch (type) {
                case 'import': return 'bg-emerald-100 text-emerald-800';
                case 'export_install': return 'bg-sky-100 text-sky-800';
                case 'export_repair': return 'bg-amber-100 text-amber-800';
                case 'return': return 'bg-purple-100 text-purple-800';
                default: return 'bg-slate-100 text-slate-700';
            }
        },

        getTransText(type) {
            switch (type) {
                case 'import': return 'Nhập kho NCC';
                case 'export_install': return 'Xuất đi lắp đặt';
                case 'export_repair': return 'Xuất sửa chữa';
                case 'return': return 'Hoàn kho phụ tùng';
                default: return type;
            }
        },

        /* ---------------------------------------------------------
         * MACHINE MODELS LOGIC
         * --------------------------------------------------------- */
        async fetchMachineModels() {
            try {
                let url = `/api/water-filter/machine-models?search=${encodeURIComponent(this.modelSearch)}&brand=${encodeURIComponent(this.modelBrand)}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.machineModels = data.models;
                }
            } catch (e) {
                console.error(e);
            }
        },

        openMachineModelModal() {
            this.modelForm = {
                id: null,
                code: '',
                brand: '',
                name: '',
                filter_stages: '',
                type: '',
                standard_price: '',
                description: '',
                default_parts: []
            };
            this.openModal('machineModelModal');
        },

        editMachineModel(item) {
            let parts = [];
            if (item.default_parts) {
                if (Array.isArray(item.default_parts)) {
                    parts = JSON.parse(JSON.stringify(item.default_parts));
                } else if (typeof item.default_parts === 'string') {
                    try { parts = JSON.parse(item.default_parts); } catch(e) { parts = []; }
                }
            }
            this.modelForm = {
                id: item.id,
                code: item.code,
                brand: item.brand || 'Karofi',
                name: item.name,
                filter_stages: item.filter_stages || 10,
                type: item.type || 'Tủ đứng',
                standard_price: item.standard_price || 0,
                description: item.description || '',
                default_parts: parts
            };
            this.openModal('machineModelModal');
        },

        addModelPart() {
            if (!this.modelForm.default_parts) {
                this.modelForm.default_parts = [];
            }
            const nextIdx = this.modelForm.default_parts.length + 1;
            let defaultPos = '';
            if (this.modelForm.filter_stages && nextIdx <= this.modelForm.filter_stages) {
                defaultPos = `Cấp ${nextIdx}`;
            }
            this.modelForm.default_parts.push({
                part_id: '',
                part_code: '',
                part_name: '',
                quantity: 1,
                position: defaultPos,
                lifespan_months: '',
                note: ''
            });
        },

        removeModelPart(index) {
            this.modelForm.default_parts.splice(index, 1);
        },

        filterModelParts(keyword, currentIdx = null) {
            const list = this.parts || [];
            const selectedIds = (this.modelForm.default_parts || [])
                .map((p, idx) => (idx !== currentIdx ? p.part_id : null))
                .filter(id => Boolean(id));

            return list.filter(p => {
                if (selectedIds.includes(p.id)) return false;
                if (!keyword || !keyword.trim()) return true;
                const q = keyword.toLowerCase().trim();
                return (
                    (p.name && p.name.toLowerCase().includes(q)) ||
                    (p.code && p.code.toLowerCase().includes(q)) ||
                    (p.category && p.category.toLowerCase().includes(q))
                );
            });
        },

        onTypeModelPartName(index, text) {
            const item = this.modelForm.default_parts[index];
            if (!item) return;
            item.part_name = text;
            const found = (this.parts || []).find(p => p.name.toLowerCase() === text.toLowerCase().trim());
            if (found) {
                item.part_id = found.id;
                item.part_code = found.code;
            } else {
                item.part_id = '';
                item.part_code = '';
            }
        },

        selectModelPart(index, part) {
            const item = this.modelForm.default_parts[index];
            if (!item) return;
            item.part_id = part.id;
            item.part_code = part.code;
            item.part_name = part.name;
            if (!item.position && part.category) {
                item.position = part.category;
            }
            this.activeModelDropdownIdx = null;
        },

        clearModelPart(index) {
            const item = this.modelForm.default_parts[index];
            if (!item) return;
            item.part_id = '';
            item.part_code = '';
            item.part_name = '';
        },

        autoGenerateModelStages() {
            let stages = parseInt(this.modelForm.filter_stages);
            if (!stages || isNaN(stages)) {
                stages = 10;
                this.modelForm.filter_stages = 10;
            }
            if (this.modelForm.default_parts && this.modelForm.default_parts.length > 0) {
                if (!confirm(`Tạo lại ${stages} cấp lọc chuẩn sẽ thay thế danh sách phụ tùng hiện tại. Bạn có chắc không?`)) {
                    return;
                }
            }
            const standardStages = [
                { pos: 'Cấp 1', name: 'Lõi lọc số 1 PP 5 Micron', months: 3 },
                { pos: 'Cấp 2', name: 'Lõi lọc số 2 Than hoạt tính OCB-GAC', months: 6 },
                { pos: 'Cấp 3', name: 'Lõi lọc số 3 PP 1 Micron (hoặc CTO)', months: 9 },
                { pos: 'Cấp 4', name: 'Màng lọc RO Purifim / Filmtec', months: 24 },
                { pos: 'Cấp 5', name: 'Lõi số 5 T33 Than hoạt tính gáo dừa', months: 12 },
                { pos: 'Cấp 6', name: 'Lõi số 6 Khoáng đá Mineral', months: 12 },
                { pos: 'Cấp 7', name: 'Lõi số 7 Hồng ngoại Far Infrared', months: 12 },
                { pos: 'Cấp 8', name: 'Lõi số 8 Tourmaline / Hydrogen', months: 12 },
                { pos: 'Cấp 9', name: 'Lõi số 9 Nano Bạc diệt khuẩn', months: 12 },
                { pos: 'Cấp 10', name: 'Lõi số 10 Bổ sung kiềm Alkaline', months: 12 },
            ];

            const newParts = [];
            for (let i = 1; i <= stages; i++) {
                const std = standardStages[i - 1] || { pos: `Cấp ${i}`, name: `Lõi lọc số ${i}`, months: 12 };
                const matchInKho = (this.parts || []).find(p => 
                    p.name.toLowerCase().includes(`số ${i}`) || 
                    (i === 4 && (p.name.toLowerCase().includes('ro') || p.name.toLowerCase().includes('màng')))
                );

                newParts.push({
                    part_id: matchInKho ? matchInKho.id : '',
                    part_code: matchInKho ? matchInKho.code : '',
                    part_name: matchInKho ? matchInKho.name : std.name,
                    quantity: 1,
                    position: std.pos,
                    lifespan_months: std.months,
                    note: ''
                });
            }
            this.modelForm.default_parts = newParts;
        },

        async saveMachineModelSubmit() {
            try {
                const res = await fetch('/api/water-filter/machine-models', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.modelForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('machineModelModal');
                    this.fetchMachineModels();
                } else {
                    alert(data.message || 'Lỗi lưu model máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra');
            }
        },

        async deleteMachineModel(id) {
            if (!confirm('Bạn có chắc muốn xóa model máy lọc nước này?')) return;
            try {
                const res = await fetch(`/api/water-filter/machine-models/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchMachineModels();
                } else {
                    alert(data.message || 'Lỗi xóa model máy');
                }
            } catch (e) {
                alert('Lỗi xóa');
            }
        },

        /* ---------------------------------------------------------
         * INSTALLED MACHINES & QR CODE LIFECYCLE LOGIC
         * --------------------------------------------------------- */
        async fetchInstalledMachines() {
            try {
                let url = `/api/water-filter/installed-machines?search=${encodeURIComponent(this.installedSearch)}&warranty_status=${encodeURIComponent(this.installedWarrantyStatus)}&technician_id=${encodeURIComponent(this.installedTechId)}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.installedMachines = data.machines.data || data.machines;
                    if (data.stats) {
                        this.installedMachinesStats = data.stats;
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedMachineIds = this.installedMachines.map(m => m.id);
            } else {
                this.selectedMachineIds = [];
            }
        },

        printSelectedStickers() {
            if (!this.selectedMachineIds.length) {
                alert('Vui lòng chọn ít nhất 1 tem máy để in!');
                return;
            }
            window.open(`/bao-hanh-may-loc/print-qr?ids=${this.selectedMachineIds.join(',')}`, '_blank');
        },

        openBatchQrModal() {
            this.batchQrForm = {
                quantity: 10,
                machine_model_id: '',
                warranty_months: 24,
                admin_notes: 'Tạo tem QR dán máy mới'
            };
            this.openModal('batchQrModal');
        },

        async submitBatchQr() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/water-filter/installed-machines/generate-qr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.batchQrForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('batchQrModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi tạo mã QR');
                }
            } catch (e) {
                alert('Có lỗi xảy ra khi tạo mã QR: ' + (e.message || ''));
            }
        },

        openQrPreview(item) {
            this.previewMachine = item;
            this.openModal('qrPreviewModal');
            this.$nextTick(() => {
                const container = document.getElementById('largeQrBox');
                if (container) {
                    container.innerHTML = '';
                    new QRCode(container, {
                        text: window.location.origin + '/m/' + item.qr_token,
                        width: 160,
                        height: 160,
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            });
        },

        openActivateModal(item) {
            this.activateData = item;
            const now = new Date();
            const todayStr = now.toISOString().split('T')[0];
            const wMonths = Number(item.warranty_months || 24);
            const rMonths = Number(item.filter_replacement_months || 6);

            const untilDate = new Date();
            untilDate.setMonth(untilDate.getMonth() + wMonths);
            const untilStr = untilDate.toISOString().split('T')[0];

            this.activateForm = {
                installed_at: item.installed_at_raw || todayStr,
                warranty_months: wMonths,
                warranty_until: item.warranty_until_raw || untilStr,
                warranty_card_code: item.warranty_card_code || item.qr_code,
                machine_serial: item.machine_serial || '',
                filter_replacement_months: rMonths,
                next_maintenance_date: item.next_maintenance_raw || '',
                admin_notes: 'Đã duyệt kích hoạt máy chính hãng Đại Sơn'
            };
            this.openModal('activateModal');
        },

        recalcActivateUntil() {
            if (!this.activateForm.installed_at) return;
            try {
                const parts = this.activateForm.installed_at.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.activateForm.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.activateForm.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async submitActivateMachine() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${this.activateData.id}/activate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.activateForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('activateModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi duyệt kích hoạt');
                }
            } catch (e) {
                alert('Có lỗi xảy ra: ' + (e.message || ''));
            }
        },

        openMachineEditModal(item) {
            this.machineEditForm = {
                id: item.id,
                machine_code: item.machine_code,
                qr_code: item.qr_code,
                customer_name: item.customer_name || '',
                customer_phone: item.customer_phone || '',
                customer_address: item.customer_address || '',
                machine_model_id: item.machine_model_id || '',
                machine_model: item.machine_model || '',
                machine_serial: item.machine_serial || '',
                accessories: item.accessories || '',
                technician_id: item.technician_id || '',
                installed_at: item.installed_at_raw || '',
                warranty_months: Number(item.warranty_months || 24),
                warranty_until: item.warranty_until_raw || '',
                warranty_card_code: item.warranty_card_code || '',
                filter_replacement_months: Number(item.filter_replacement_months || 6),
                next_maintenance_date: item.next_maintenance_raw || '',
                warranty_notes: item.warranty_notes || '',
                status: item.status || 'activated',
                admin_notes: item.admin_notes || ''
            };
            this.openModal('machineEditModal');
        },

        recalcMachineEditUntil() {
            if (!this.machineEditForm.installed_at) return;
            try {
                const parts = this.machineEditForm.installed_at.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.machineEditForm.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.machineEditForm.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async submitMachineEdit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${this.machineEditForm.id}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.machineEditForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('machineEditModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi cập nhật máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra: ' + (e.message || ''));
            }
        },

        async deleteInstalledMachine(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa bản ghi máy lọc / mã QR này?')) return;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi xóa máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra: ' + (e.message || ''));
            }
        },

        openWarrantyModal(item) {
            this.warrantyEditForm = {
                id: item.id,
                code: item.code,
                customer_name: item.customer_name,
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                installed_at: item.installed_at_raw || '',
                warranty_months: Number(item.warranty_months || 24),
                warranty_until: item.warranty_until_raw || '',
                warranty_card_code: item.warranty_card_code || '',
                warranty_notes: item.warranty_notes || '',
                filter_replacement_months: Number(item.filter_replacement_months || 6),
                next_maintenance_date: item.next_maintenance_date !== '-' ? item.next_maintenance_date : ''
            };
            this.openModal('warrantyEditModal');
        },

        recalcEditWarrantyUntil() {
            if (!this.warrantyEditForm.installed_at) return;
            try {
                const parts = this.warrantyEditForm.installed_at.split('-');
                if (parts.length === 3) {
                    const d = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                    d.setMonth(d.getMonth() + (Number(this.warrantyEditForm.warranty_months) || 24));
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    this.warrantyEditForm.warranty_until = `${y}-${m}-${day}`;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async saveWarrantySubmit() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch(`/api/water-filter/installed-machines/${this.warrantyEditForm.id}/warranty`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(this.warrantyEditForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal('warrantyEditModal');
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Lỗi lưu bảo hành máy');
                }
            } catch (e) {
                alert('Có lỗi xảy ra khi cập nhật bảo hành: ' + (e.message || ''));
            }
        },

        quickCreateRepairFromInstalled(item) {
            const isWarrantyActive = (item.warranty_status === 'active' || item.warranty_status === 'expiring_soon');
            
            // Tự động mang theo danh sách phụ tùng / lõi lọc cần bảo dưỡng
            const prefilledItems = (item.maintenance_parts && item.maintenance_parts.length > 0)
                ? item.maintenance_parts.map(p => ({
                    part_id: p.part_id || null,
                    part_name: p.part_name,
                    quantity: p.quantity || 1,
                    unit_price: p.price || 0,
                    is_free: isWarrantyActive ? true : false,
                    note: p.note || 'Bảo dưỡng thay thế'
                }))
                : [];

            this.repairForm = {
                id: null,
                customer_name: item.customer_name,
                customer_phone: item.customer_phone,
                customer_address: item.customer_address,
                repair_type: 'at_home',
                warranty_type: isWarrantyActive ? 'in_warranty' : 'out_warranty',
                machine_model: item.machine_model,
                machine_serial: item.machine_serial || '',
                technician_id: item.technician_id || '',
                symptom: isWarrantyActive ? `Bảo hành & bảo dưỡng định kỳ máy lọc [${item.machine_model}]` : `Bảo dưỡng & thay lõi định kỳ máy lọc [${item.machine_model}]`,
                labor_fee: isWarrantyActive ? 0 : 50000,
                discount: 0,
                items: prefilledItems
            };
            this.openModal('repairModal');
        },

        getWarrantyBadgeClass(st) {
            switch (st) {
                case 'active': return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
                case 'expiring_soon': return 'bg-amber-100 text-amber-800 border border-amber-300 animate-pulse';
                case 'expired': return 'bg-rose-100 text-rose-800 border border-rose-300';
                default: return 'bg-slate-100 text-slate-700 border border-slate-300';
            }
        },

        getWarrantyStatusLabel(st) {
            switch (st) {
                case 'active': return 'Còn bảo hành';
                case 'expiring_soon': return 'Sắp hết hạn';
                case 'expired': return 'Hết hạn bảo hành';
                default: return 'Chưa kích hoạt';
            }
        },

        /* ---------------------------------------------------------
         * MAINTENANCE SCHEDULE LOGIC
         * --------------------------------------------------------- */
        async fetchMaintenance() {
            try {
                let url = `/api/water-filter/maintenance?search=${encodeURIComponent(this.maintSearch)}&filter_status=${encodeURIComponent(this.maintStatusFilter)}&technician_id=${encodeURIComponent(this.maintTechFilter)}&area=${encodeURIComponent(this.maintAreaFilter)}`;
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    this.maintenanceList = data.machines.data || data.machines;
                    if (data.stats) {
                        this.maintenanceStats = data.stats;
                    }
                    if (data.area_suggestions) {
                        this.maintenanceAreaSuggestions = data.area_suggestions;
                    }
                    if (data.can_assign !== undefined) {
                        this.canAssignMaint = data.can_assign;
                    }
                    if (data.current_user_id !== undefined) {
                        this.currentUserId = data.current_user_id;
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        async claimMaintenanceJob(item) {
            if (!confirm(`Bạn có chắc chắn muốn nhận phụ trách đơn bảo dưỡng máy cho khách hàng [${item.customer_name}] không?`)) {
                return;
            }
            try {
                const res = await fetch(`/api/water-filter/maintenance/${item.id}/claim`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Bạn đã nhận việc thành công!');
                    this.fetchMaintenance();
                } else {
                    alert(data.message || 'Không thể nhận việc!');
                }
            } catch (e) {
                console.error(e);
                alert('Lỗi kết nối máy chủ!');
            }
        },

        async unclaimMaintenanceJob(item) {
            if (!confirm(`Xác nhận hủy nhận đơn bảo dưỡng của khách hàng [${item.customer_name}]?\nĐơn sẽ được chuyển về trạng thái 'Chưa phân công' để KTV khác có thể nhận.`)) {
                return;
            }
            try {
                const res = await fetch(`/api/water-filter/maintenance/${item.id}/unclaim`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã hủy nhận đơn thành công!');
                    this.fetchMaintenance();
                } else {
                    alert(data.message || 'Không thể hủy nhận đơn!');
                }
            } catch (e) {
                console.error(e);
                alert('Lỗi kết nối máy chủ!');
            }
        },

        async assignMaintenanceTech(item, techId) {
            try {
                const res = await fetch(`/api/water-filter/maintenance/${item.id}/assign`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ technician_id: techId || null })
                });
                const data = await res.json();
                if (data.success) {
                    this.fetchMaintenance();
                } else {
                    alert(data.message || 'Không thể phân công KTV!');
                }
            } catch (e) {
                console.error(e);
                alert('Lỗi kết nối máy chủ!');
            }
        },

        filterMaintenanceByArea(areaName) {
            if (this.maintAreaFilter === areaName) {
                this.maintAreaFilter = '';
            } else {
                this.maintAreaFilter = areaName;
            }
            this.fetchMaintenance();
        },

        openRescheduleModal(item) {
            this.rescheduleTarget = item;
            this.rescheduleForm = {
                id: item.id,
                next_maintenance_date: item.next_maintenance_raw || new Date().toISOString().slice(0, 10),
                filter_replacement_months: item.filter_replacement_months || 6,
                technician_id: item.technician_id || '',
                notes: ''
            };
            this.openModal('rescheduleModal');
        },

        setRescheduleQuickDays(days) {
            const d = new Date();
            d.setDate(d.getDate() + days);
            this.rescheduleForm.next_maintenance_date = d.toISOString().slice(0, 10);
        },

        setRescheduleQuickMonths(months) {
            const d = new Date();
            d.setMonth(d.getMonth() + months);
            this.rescheduleForm.next_maintenance_date = d.toISOString().slice(0, 10);
        },

        async submitRescheduleMaintenance() {
            if (!this.rescheduleForm.next_maintenance_date) {
                alert('Vui lòng chọn ngày hẹn bảo dưỡng!');
                return;
            }
            try {
                const res = await fetch(`/api/water-filter/maintenance/${this.rescheduleForm.id}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.rescheduleForm)
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã dời ngày hẹn bảo dưỡng thành công!');
                    this.closeModal('rescheduleModal');
                    this.fetchMaintenance();
                    this.fetchInstalledMachines();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi lưu lịch hẹn!');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ!');
                console.error(e);
            }
        },

        async confirmCompleteMaintenance(item) {
            const months = item.filter_replacement_months || 6;
            if (!confirm(`Xác nhận đã bảo dưỡng xong cho khách hàng: ${item.customer_name}?\n\nHệ thống sẽ tự động dời lịch bảo dưỡng tiếp theo (+${months} tháng) và lưu vào nhật ký dịch vụ.`)) {
                return;
            }
            try {
                const res = await fetch(`/api/water-filter/maintenance/${item.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        filter_replacement_months: months,
                        parts_replaced: item.default_parts ? item.default_parts.map(p => p.name || p.part_name).join(', ') : 'Bộ 3 lõi lọc thô 1, 2, 3'
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert(data.message || 'Đã cập nhật hoàn tất bảo dưỡng!');
                    this.fetchMaintenance();
                    this.fetchInstalledMachines();
                    this.fetchRepairs();
                } else {
                    alert(data.message || 'Không thể hoàn tất bảo dưỡng!');
                }
            } catch (e) {
                alert('Lỗi kết nối máy chủ!');
                console.error(e);
            }
        },

        openReminderModal(item) {
            this.reminderTarget = item;
            this.copiedMessage = false;
            this.generateReminderText();
            this.openModal('reminderModal');
        },

        openGlobalReminderModal() {
            if (this.maintenanceList.length > 0) {
                this.openReminderModal(this.maintenanceList[0]);
            } else {
                this.reminderTarget = {
                    customer_name: 'Quý Khách Hàng',
                    customer_phone: '09xxxxxxxx',
                    machine_model: 'Máy lọc nước RO',
                    next_maintenance_date: 'Định kỳ'
                };
                this.copiedMessage = false;
                this.generateReminderText();
                this.openModal('reminderModal');
            }
        },

        generateReminderText() {
            if (!this.reminderTarget) return;
            const name = this.reminderTarget.customer_name || 'Quý khách';
            const model = this.reminderTarget.machine_model || 'máy lọc nước';
            const date = this.reminderTarget.next_maintenance_date || 'định kỳ';
            const partsList = (this.reminderTarget.maintenance_parts && this.reminderTarget.maintenance_parts.length > 0)
                ? this.reminderTarget.maintenance_parts.map(p => p.part_name).join(', ')
                : '';
            const partsText = partsList ? `\n* Các lõi/vật tư cần thay: ${partsList}` : '';

            if (this.reminderTemplateType === 'friendly') {
                this.reminderMessage = `Dạ em chào anh/chị ${name}! Em là nhân viên chăm sóc khách hàng máy lọc nước Daison.\n\nTheo định kỳ theo dõi, thiết bị [${model}] của gia đình mình đã đến thời gian bảo dưỡng và thay lõi lọc nước định kỳ (Hẹn: ${date}) để đảm bảo nguồn nước luôn tinh khiết, sạch khuẩn và bảo vệ tuổi thọ màng RO.${partsText}\n\nAnh/chị cho bên em xin lịch thuận tiện trong tuần này để kỹ thuật viên qua kiểm tra và thay lõi tận nhà giúp gia đình mình nhé ạ! Em cảm ơn anh/chị nhiều ạ!`;
            } else if (this.reminderTemplateType === 'formal') {
                this.reminderMessage = `Kính gửi Quý khách ${name},\n\nBộ phận Dịch vụ & Bảo hành Daison xin trân trọng thông báo: Thiết bị máy lọc nước [${model}] của Quý khách đã đến hạn bảo dưỡng và thay thế lõi lọc định kỳ vào ngày ${date}.${partsText}\n\nViệc bảo dưỡng đúng định kỳ giúp đảm bảo chất lượng nước uống đạt chuẩn và vận hành bền bỉ. Kính mời Quý khách liên hệ hoặc phản hồi tin nhắn này để trung tâm bố trí Kỹ thuật viên phục vụ tận nơi.\n\nHotline hỗ trợ: 1900 xxxx. Trân trọng cảm ơn Quý khách!`;
            } else if (this.reminderTemplateType === 'urgent') {
                this.reminderMessage = `⚠️ THÔNG BÁO QUÁ HẠN THAY LÕI LỌC NƯỚC\n\nKính gửi Quý khách ${name}, hệ thống ghi nhận máy lọc nước [${model}] của Quý khách đã QUÁ HẠN thay lõi lọc định kỳ (${date}).${partsText}\n\nLõi lọc quá hạn tích tụ nhiều cặn bẩn, kim loại nặng và vi khuẩn, dễ làm tắc màng RO và ảnh hưởng chất lượng nước ăn uống của gia đình.\n\nQuý khách vui lòng đặt lịch kiểm tra ngay hôm nay để KTV xử lý kịp thời. Xin cảm ơn Quý khách!`;
            }
        },

        copyReminderMessage() {
            navigator.clipboard.writeText(this.reminderMessage).then(() => {
                this.copiedMessage = true;
                setTimeout(() => { this.copiedMessage = false; }, 3000);
            }).catch(() => {
                alert('Không thể tự động sao chép, vui lòng bôi đen và nhấn Ctrl+C');
            });
        }
    };
}
</script>
