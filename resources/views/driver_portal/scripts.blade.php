<!-- Embed initial dataset from Laravel controller into JSON script -->
<script id="driver-execution-data" type="application/json">
{
    "selectedVehicle": @json($selectedVehicle ?? null),
    "orders": @json($orders ?? []),
    "deliveryReturns": @json($deliveryReturns ?? []),
    "cashHandovers": @json($cashHandovers ?? []),
    "accountants": @json($accountants ?? []),
    "warehouseStaff": @json($warehouseStaff ?? []),
    "warehouses": @json($warehouses ?? []),
    "holdingWarehouse": @json($holdingWarehouse ?? null),
    "holdingStocks": @json($holdingStocks ?? []),
    "totalHoldingQty": {{ (float)($totalHoldingQty ?? 0) }},
    "totalHoldingVal": {{ (float)($totalHoldingVal ?? 0) }},
    "totalHoldingSkus": {{ (int)($totalHoldingSkus ?? 0) }},
    "empDebt": {{ (float)($empDebt ?? 0) }},
    "vehicleManagerName": @json($vehicleManagerName ?? ''),
    "vehicleManagerId": @json($vehicleManagerId ?? null),
    "dealers": @json($dealers ?? []),
    "driverVanDispatches": @json($driverVanDispatches ?? []),
    "totalVanSaleDebt": {{ (float)($totalVanSaleDebt ?? 0) }},
    "salesStaff": @json($salesStaff ?? [])
}
</script>

<script>
function deliveryExecutionApp() {
    const rawData = JSON.parse(document.getElementById('driver-execution-data')?.textContent || '{}');

    return {
        selectedVehicle: rawData.selectedVehicle || null,
        showOrderReturnWarehouseModal: false,

        async safeFetchJson(url, options = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const defaultHeaders = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token || ''
            };
            if (options.body && typeof options.body === 'string') {
                defaultHeaders['Content-Type'] = 'application/json';
            }
            options.headers = { ...defaultHeaders, ...(options.headers || {}) };

            try {
                const res = await fetch(url, options);
                const contentType = res.headers.get('content-type') || '';
                let data = null;
                if (contentType.includes('application/json')) {
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = null;
                    }
                }
                if (!res.ok || !data) {
                    let msg = data?.message || data?.error;
                    if (!msg) {
                        if (res.status === 419) msg = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang!';
                        else if (res.status === 403) msg = 'Tài khoản chưa được cấp quyền thực hiện chức năng này!';
                        else if (res.status === 404) msg = 'Không tìm thấy liên kết xử lý (' + url + ')!';
                        else if (res.status >= 500) msg = 'Máy chủ gặp sự cố (' + res.status + '). Vui lòng thử lại sau!';
                        else msg = 'Phản hồi không hợp lệ từ máy chủ (' + res.status + ')';
                    }
                    return { ok: false, status: res.status, data: data || { success: false, message: msg } };
                }
                return { ok: true, status: res.status, data };
            } catch (networkErr) {
                return { ok: false, status: 0, data: { success: false, message: networkErr.message || 'Lỗi kết nối máy chủ' } };
            }
        },

        orderReturnForm: {
            order: null,
            intakeStaffId: '',
            intakeStaffSearch: '',
            warehouseId: '',
            notes: '',
            isSubmitting: false,
            isStaffDropdownOpen: false
        },

        get filteredWarehouseStaff() {
            const q = (this.orderReturnForm.intakeStaffSearch || '').trim().toLowerCase();
            if (!q) return this.warehouseStaff || [];
            return (this.warehouseStaff || []).filter(s => {
                const name = (s.name || '').toLowerCase();
                const dept = (s.department || '').toLowerCase();
                const phone = (s.phone || '');
                return name.includes(q) || dept.includes(q) || phone.includes(q);
            });
        },

        selectIntakeStaff(staff) {
            if (!staff) {
                this.orderReturnForm.intakeStaffId = '';
                this.orderReturnForm.intakeStaffSearch = '';
            } else {
                this.orderReturnForm.intakeStaffId = staff.id;
                this.orderReturnForm.intakeStaffSearch = staff.name + (staff.department ? (' (' + staff.department + ')') : '');
            }
            this.orderReturnForm.isStaffDropdownOpen = false;
        },

        clearIntakeStaff() {
            this.orderReturnForm.intakeStaffId = '';
            this.orderReturnForm.intakeStaffSearch = '';
            this.orderReturnForm.isStaffDropdownOpen = true;
        },
        orders: (rawData.orders || []).map(o => ({ ...o, _showItems: false })),
        showAcceptHoldingModal: false,
        holdingReturnSlipsToAccept: [],
        agreeHoldingTerms: true,
        isAcceptingHolding: false,

        returnsList: (rawData.deliveryReturns || []).map(r => ({
            ...r,
            slipCode: r.slipCode || r.slip_code || ('TH-' + (r.orderCode || r.order_code)),
            orderCode: r.orderCode || r.order_code,
            dealerName: r.dealerName || r.dealer_name || 'Khách hàng',
            totalReturnedAmount: Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0),
            items: (r.items || []).map(it => ({
                ...it,
                returnedQty: Number(it.returnedQty ?? it.returned_qty ?? 0),
                unitPrice: Number(it.unitPrice ?? it.unit_price ?? 0),
                amount: Number(it.amount || 0)
            }))
        })),
        cashHandovers: rawData.cashHandovers || [],
        salesStaff: rawData.salesStaff || [],
        showDebtHandoverModal: false,
        isSubmittingDebtHandover: false,
        showDocPreviewModal: false,
        previewDocUrl: null,
        debtHandoverForm: {
            accountantId: '',
            toUserId: '',
            selectedOrderIds: [],
            amount: 0,
            notes: '',
            documents: []
        },
        accountants: rawData.accountants || [],
        warehouseStaff: rawData.warehouseStaff || [],
        warehouses: rawData.warehouses || [],

        // Hàng kho nhân sự quản lý xe
        showHoldingStockModal: false,
        showVanSaleItemsModal: false,
        holdingWarehouse: rawData.holdingWarehouse || null,
        holdingStocks: rawData.holdingStocks || [],
        totalHoldingQty: Number(rawData.totalHoldingQty || 0),
        totalHoldingVal: Number(rawData.totalHoldingVal || 0),
        totalHoldingSkus: Number(rawData.totalHoldingSkus || 0),
        empDebt: Number(rawData.empDebt || 0),
        vehicleManagerName: rawData.vehicleManagerName || '',
        vehicleManagerId: rawData.vehicleManagerId || null,
        dealers: rawData.dealers || [],
        holdingSearchQuery: '',

        // Modal Bán hàng từ kho nhân sự (Tương tự holding_sale_create.blade.php)
        showHoldingSaleModal: false,
        isSubmittingHoldingSale: false,
        saleForm: {
            customer_type: 'trip_dealer', // 'trip_dealer' | 'dealer' | 'walk_in'
            dealer_id: '',
            customer_name: '',
            customer_phone: '',
            customer_address: '',
            payment_method: 'Tiền mặt',
            notes: '',
            items: []
        },
        saleProductSearch: '',
        showSaleProductDropdown: false,

        // Danh sách các đại lý có đơn trên xe hôm nay (duy nhất)
        get tripDealers() {
            const map = new Map();
            (this.orders || []).forEach(o => {
                const id = o.dealer_id || o.customer_name;
                if (!map.has(id)) {
                    map.set(id, {
                        dealer_id: o.dealer_id || '',
                        name: o.customer_name || 'Đại lý',
                        phone: o.customer_phone || '',
                        address: o.customer_address || ''
                    });
                }
            });
            return Array.from(map.values());
        },

        get filteredSaleProducts() {
            if (!this.saleProductSearch || !this.saleProductSearch.trim()) {
                return this.holdingStocks;
            }
            const q = this.saleProductSearch.toLowerCase().trim();
            return (this.holdingStocks || []).filter(s => 
                (s.product_name && s.product_name.toLowerCase().includes(q)) ||
                (s.sku && s.sku.toLowerCase().includes(q))
            );
        },

        openHoldingSaleModal(preselectedItem = null, preselectedDealer = null) {
            // Reset form
            this.saleForm = {
                customer_type: 'trip_dealer',
                dealer_id: '',
                customer_name: '',
                customer_phone: '',
                customer_address: '',
                payment_method: 'Tiền mặt',
                notes: 'Bán trực tiếp từ kho ' + (this.holdingWarehouse?.name || 'nhân sự'),
                items: []
            };
            this.saleProductSearch = '';
            this.showSaleProductDropdown = false;

            // Nếu có preselectedDealer
            if (preselectedDealer) {
                this.selectTripDealer(preselectedDealer);
            } else if (this.tripDealers.length > 0) {
                this.selectTripDealer(this.tripDealers[0]);
            }

            // Nếu có preselectedItem
            if (preselectedItem) {
                this.addSaleItem(preselectedItem);
            }

            this.showHoldingSaleModal = true;
        },

        selectTripDealer(d) {
            this.saleForm.customer_type = 'trip_dealer';
            this.saleForm.dealer_id = d.dealer_id || '';
            this.saleForm.customer_name = d.name;
            this.saleForm.customer_phone = d.phone || '';
            this.saleForm.customer_address = d.address || '';
        },

        onSystemDealerChange() {
            const dl = (this.dealers || []).find(d => String(d.id) === String(this.saleForm.dealer_id));
            if (dl) {
                this.saleForm.customer_name = dl.name;
                this.saleForm.customer_phone = dl.phone || '';
                this.saleForm.customer_address = dl.address || '';
            }
        },

        addSaleItem(stockItem) {
            if (!stockItem) return;
            const existing = this.saleForm.items.find(i => String(i.product_id) === String(stockItem.product_id));
            const maxStock = Number(stockItem.quantity ?? stockItem.stock_quantity ?? 0);
            if (existing) {
                if (existing.quantity < maxStock) {
                    existing.quantity++;
                } else {
                    alert(`Sản phẩm [${stockItem.sku}] ${stockItem.product_name} chỉ còn tồn ${maxStock} trong kho!`);
                }
            } else {
                this.saleForm.items.push({
                    product_id: stockItem.product_id,
                    sku: stockItem.sku,
                    name: stockItem.product_name || stockItem.name,
                    unit: stockItem.unit || 'cái',
                    available_stock: maxStock,
                    quantity: 1,
                    unit_price: Number(stockItem.price || 0)
                });
            }
            this.saleProductSearch = '';
            this.showSaleProductDropdown = false;
        },

        removeSaleItem(idx) {
            this.saleForm.items.splice(idx, 1);
        },

        get saleTotalQty() {
            return (this.saleForm.items || []).reduce((sum, it) => sum + Number(it.quantity || 0), 0);
        },

        get saleTotalAmount() {
            return (this.saleForm.items || []).reduce((sum, it) => sum + (Number(it.quantity || 0) * Number(it.unit_price || 0)), 0);
        },

        // Sinh link VietQR khi khách chọn quét mã thanh toán đơn bán này
        get holdingSaleVietQrUrl() {
            const amt = this.saleTotalAmount;
            if (amt <= 0) return '';
            const memo = encodeURIComponent('BAN ' + (this.selectedVehicle?.plate_number || '') + ' ' + (this.saleForm.customer_name ? this.saleForm.customer_name.substring(0, 15) : 'KHO'));
            return `https://img.vietqr.io/image/${this.bankInfo.bankId}-${this.bankInfo.accountNo}-compact2.png?amount=${amt}&addInfo=${memo}&accountName=${encodeURIComponent(this.bankInfo.accountName)}`;
        },

        async submitHoldingSale() {
            if (!this.saleForm.customer_name || !this.saleForm.customer_name.trim()) {
                alert('Vui lòng nhập tên khách hàng hoặc chọn đại lý nhận hàng!');
                return;
            }
            if (this.saleForm.items.length === 0) {
                alert('Vui lòng chọn ít nhất 1 sản phẩm từ kho để bán!');
                return;
            }
            for (let it of this.saleForm.items) {
                if (Number(it.quantity) <= 0) {
                    alert(`Số lượng bán cho sản phẩm [${it.sku}] phải lớn hơn 0!`);
                    return;
                }
                if (Number(it.quantity) > Number(it.available_stock)) {
                    alert(`Sản phẩm [${it.sku}] ${it.name} chỉ còn tồn ${it.available_stock} trong kho. Không thể bán ${it.quantity}!`);
                    return;
                }
            }

            this.isSubmittingHoldingSale = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const pMethod = this.saleForm.payment_method;
                const isPaid = (pMethod === 'Tiền mặt' || pMethod === 'VietQR');

                const payload = {
                    warehouse: this.holdingWarehouse ? this.holdingWarehouse.name : 'Kho xe lưu động',
                    warehouse_id: this.holdingWarehouse ? this.holdingWarehouse.id : null,
                    employee_id: this.vehicleManagerId || (this.holdingWarehouse ? this.holdingWarehouse.employee_id : null),
                    employee_name: this.vehicleManagerName || '',
                    sales_rep_id: this.vehicleManagerId || null,
                    salesRepId: this.vehicleManagerId || null,
                    assigned_vehicle_plate: this.selectedVehicle?.plate_number || '',
                    assignedVehiclePlate: this.selectedVehicle?.plate_number || '',
                    assigned_driver_id: this.vehicleManagerId || null,
                    order_type: 'holding_sale',
                    orderType: 'holding_sale',
                    is_holding_stock: true,
                    is_holding_stock_sale: true,
                    status: 'Đã giao',
                    dealerId: this.saleForm.dealer_id || null,
                    dealerName: this.saleForm.customer_name,
                    customerName: this.saleForm.customer_name,
                    customerPhone: this.saleForm.customer_phone,
                    customerAddress: this.saleForm.customer_address,
                    deliveryAddress: this.saleForm.customer_address,
                    paymentMethod: pMethod,
                    paymentStatus: isPaid ? 'Đã thanh toán' : 'Ghi nợ đại lý',
                    notes: this.saleForm.notes || ('Bán trực tiếp từ kho ' + (this.holdingWarehouse?.name || 'nhân sự')),
                    items: this.saleForm.items.map(it => ({
                        product_id: parseInt(it.product_id, 10),
                        name: it.name,
                        sku: it.sku,
                        unit: it.unit,
                        quantity: Number(it.quantity),
                        unitPrice: Number(it.unit_price),
                        unit_price: Number(it.unit_price),
                        discount: 0,
                        amount: Number(it.quantity) * Number(it.unit_price),
                        isChecked: true,
                        isPicked: true
                    }))
                };

                const { ok, data } = await this.safeFetchJson('/orders/save', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                if (ok && (data?.success || data?.order_id || data?.id || data?.order?.id)) {
                    // Trừ tồn kho local trực tiếp trên màn hình
                    this.saleForm.items.forEach(saleItem => {
                        const target = (this.holdingStocks || []).find(s => String(s.product_id) === String(saleItem.product_id));
                        if (target) {
                            target.quantity = Math.max(0, Number(target.quantity) - Number(saleItem.quantity));
                            target.stock_quantity = target.quantity;
                            target.amount = target.quantity * Number(target.price || 0);
                        }
                    });

                    // Tính lại tổng tồn
                    this.totalHoldingQty = this.holdingStocks.reduce((sum, s) => sum + Number(s.quantity || 0), 0);
                    this.totalHoldingVal = this.holdingStocks.reduce((sum, s) => sum + (Number(s.quantity || 0) * Number(s.price || 0)), 0);

                    // Đóng modal & hiển thị Toast
                    this.showHoldingSaleModal = false;
                    this.showToast(`✅ Bán hàng thành công! Đã tạo đơn bán và trừ kho ${this.saleTotalQty} sản phẩm (${this.formatNumber(this.saleTotalAmount)} đ)`, 'success');
                } else {
                    alert('Lỗi tạo đơn bán hàng: ' + (data?.message || data?.error || 'Vui lòng kiểm tra lại'));
                }
            } catch (err) {
                alert('Lỗi: ' + err.message);
            } finally {
                this.isSubmittingHoldingSale = false;
            }
        },

        get filteredHoldingStocks() {
            if (!this.holdingSearchQuery) return this.holdingStocks;
            const q = this.holdingSearchQuery.toLowerCase().trim();
            return (this.holdingStocks || []).filter(it => 
                (it.product_name && it.product_name.toLowerCase().includes(q)) ||
                (it.sku && it.sku.toLowerCase().includes(q))
            );
        },

        // Legacy van dispatches fallback if any
        vanDispatches: rawData.driverVanDispatches || [],
        totalVanSaleDebt: Number(rawData.totalVanSaleDebt || 0),

        get allVanItems() {
            return this.holdingStocks;
        },

        searchQuery: '',
        statusFilter: 'all',

        // Action Modals State & View Navigation
        showDeliveryModal: false,
        currentView: 'list', // 'list' (danh sách đơn trên xe) | 'deliver' (trang giao đơn chuyên biệt)
        copiedField: '',
        showCodApprovalModal: false,
        showIntakeWarehouseModal: false,
        showReturnsModal: false,
        showFailModal: false,
        showOrderViewModal: false,

        // Inspecting order
        activeOrder: null,
        inspectOrder: null,

        @php 
            $portalBank = \App\Models\CompanyBankAccount::default()->first() ?? \App\Models\CompanyBankAccount::first(); 
        @endphp
        // Thông tin tài khoản ngân hàng để sinh mã VietQR
        bankInfo: {
            bankId: '{{ $portalBank?->bank_code ?? "MB" }}',
            bankName: '{{ $portalBank?->bank_name ?? "" }}',
            accountNo: '{{ $portalBank?->account_number ?? "" }}',
            accountName: '{{ $portalBank?->account_holder ?? "" }}'
        },

        // Form state for delivery confirmation
        confirmForm: {
            orderId: null,
            orderCode: '',
            hasReturns: false,
            items: [],
            recipientName: '',
            deliveryNotes: '',
            paymentMethod: 'Tiền mặt',
            collectedAmount: 0,
            finalAmount: 0,
            isDebt: false,
            appliedToThisOrder: 0,
            appliedToOldDebt: 0,
            projectedDebtAfter: 0,
        },

        // Form state for COD approval (bắt buộc chọn kế toán)
        codApprovalForm: {
            amount: 0,
            accountantId: '',
            accountantName: '',
            notes: ''
        },

        // Form state for warehouse intake (bắt buộc chọn người xác nhận)
        intakeForm: {
            intakeUserId: '',
            intakeUserName: '',
            warehouseId: {{ $warehouses->first()->id ?? 4 }},
            notes: ''
        },

        // Form state for fail delivery
        failForm: {
            reason: 'Khách hẹn giao lại vào buổi sau',
            rescheduledDate: '',
            notes: ''
        },

        // Process states
        isDeparting: false,
        isReturningVehicle: false,
        isSubmittingDelivery: false,
        isSubmittingFail: false,
        isSettlingCash: false,
        isIntakingReturns: false,
        hasPendingCodSubmission: false,
        pendingCodInfo: { code: '', amount: 0, accountantName: '' },
        isCheckingCodStatus: false,
        hasPendingReturnsSubmission: false,
        pendingReturnsInfo: { staffName: '', count: 0, amount: 0 },
        isCheckingReturnsStatus: false,

        toast: { show: false, message: '', type: 'success' },

        initApp() {
            this.recalcVehicleStats();
            if (typeof BroadcastChannel !== 'undefined') {
                const bc = new BroadcastChannel('erp_order_sync');
                bc.onmessage = (ev) => {
                    if (ev.data && (ev.data.type === 'QC_ALL_CHECKED' || ev.data.type === 'ORDER_STATUS_CHANGED' || ev.data.orderId)) {
                        window.location.reload();
                    }
                };
            }

            // Kiểm tra xem xe này có biên bản nộp tiền đang chờ duyệt không
            const pendingHandover = (this.cashHandovers || []).find(h => 
                (h.vehicle_plate === this.selectedVehicle?.plate_number || h.vehiclePlate === this.selectedVehicle?.plate_number)
                && h.status === 'Chờ kế toán duyệt'
            );
            if (pendingHandover) {
                this.hasPendingCodSubmission = true;
                this.pendingCodInfo = {
                    code: pendingHandover.code,
                    amount: pendingHandover.handover_amount || pendingHandover.handoverAmount || 0,
                    accountantName: pendingHandover.accountant_name || pendingHandover.accountantName || 'Kế toán'
                };
            }

            // Kiểm tra xem xe này có phiếu hoàn trả nào đang chờ thủ kho duyệt không
            const pendingReturns = (this.returnsList || []).filter(r => 
                r.status === 'Chờ thủ kho duyệt nhập' || r.status === 'Chờ nhập kho lại'
            );
            if (pendingReturns.length > 0) {
                this.hasPendingReturnsSubmission = true;
                const staff = (this.warehouseStaff || []).find(s => String(s.id) === String(pendingReturns[0].intakeUserId));
                this.pendingReturnsInfo = {
                    staffName: staff ? staff.name : (this.intakeForm.intakeUserName || 'Thủ kho'),
                    count: pendingReturns.length,
                    amount: pendingReturns.reduce((s, r) => s + Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0), 0)
                };
            }

            // Kiểm tra query parameter mở trực tiếp trang giao đơn khi tải trang
            const params = new URLSearchParams(window.location.search);
            if (params.get('view') === 'deliver' && params.get('order_id')) {
                window.location.href = `/delivery/orders/${params.get('order_id')}/confirm`;
            }
        },

        get isDeparted() {
            return this.selectedVehicle?.status === 'Đang di chuyển' 
                || this.selectedVehicle?.dispatch_status === 'Đang giao hàng'
                || this.selectedVehicle?.dispatch_status === 'Trên đường về';
        },

        get isReturning() {
            return this.selectedVehicle?.dispatch_status === 'Trên đường về';
        },

        get isAllCompleted() {
            return this.orders.length > 0 && this.pendingOrdersCount === 0;
        },

        get departureTimeStr() {
            if (!this.selectedVehicle?.departure_time) return '';
            try {
                const d = new Date(this.selectedVehicle.departure_time);
                return d.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
            } catch (e) {
                return '';
            }
        },

        // Kiểm tra đơn hàng đã kiểm xong chưa (yêu cầu "Đã kiểm hàng")
        isOrderChecked(o) {
            return ['Đã kiểm hàng', 'Đã kiểm', 'Đang vận chuyển', 'Đã giao', 'Giao thành công'].includes(o.status);
        },

        // Danh sách đơn chưa kiểm hàng trên xe
        get uncheckedOrders() {
            return this.orders.filter(o => !this.isOrderChecked(o));
        },

        // Số lượng đơn đã kiểm hàng
        get checkedOrdersCount() {
            return this.orders.filter(o => this.isOrderChecked(o)).length;
        },

        // Điều kiện xuất bến: Phải có đơn hàng VÀ 100% đơn hàng trên xe đã có trạng thái "Đã kiểm hàng"
        get isReadyToDepart() {
            return this.orders.length > 0 && this.uncheckedOrders.length === 0;
        },

        get completedOrdersCount() {
            return this.orders.filter(o => ['Đã giao', 'Giao thành công'].includes(o.status)).length;
        },

        get pendingOrdersCount() {
            return this.orders.filter(o => !['Đã giao', 'Giao thành công', 'Không thành công'].includes(o.status)).length;
        },

        get failedOrdersCount() {
            return this.orders.filter(o => o.status === 'Không thành công').length;
        },

        get progressPercent() {
            if (this.orders.length === 0) return 0;
            return Math.round((this.completedOrdersCount / this.orders.length) * 100);
        },

        get cashInHand() {
            return this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status) && o.payment_method === 'Tiền mặt')
                .reduce((sum, o) => sum + Number(o.driver_debt_amount || 0), 0);
        },

        get vietQrAmount() {
            return this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status) && (o.payment_method === 'VietQR' || o.payment_method === 'Chuyển khoản VietQR'))
                .reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
        },

        get debtAmount() {
            return this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status) && o.payment_status === 'Ghi nợ đại lý')
                .reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
        },

        get pendingReturnsList() {
            return (this.returnsList || []).filter(r => r.status !== 'Tài xế đã nhận ôm hàng' && r.status !== 'Đã nhập kho lại');
        },

        get pendingReturnsTotalAmount() {
            return this.pendingReturnsList.reduce((sum, r) => sum + Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0), 0);
        },

        get holdingReturnSlipsTotalAmount() {
            return (this.holdingReturnSlipsToAccept || []).reduce((sum, r) => sum + Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0), 0);
        },

        get returnsTotalAmount() {
            return this.returnsList.reduce((sum, r) => sum + Number(r.totalReturnedAmount ?? r.total_returned_amount ?? 0), 0);
        },

        get filteredOrders() {
            return this.orders.filter(o => {
                if (this.statusFilter === 'pending') {
                    if (['Đã giao', 'Giao thành công', 'Không thành công'].includes(o.status)) return false;
                } else if (this.statusFilter === 'completed') {
                    if (!['Đã giao', 'Giao thành công'].includes(o.status)) return false;
                } else if (this.statusFilter === 'failed') {
                    if (o.status !== 'Không thành công') return false;
                }

                if (this.searchQuery && this.searchQuery.trim()) {
                    const q = this.searchQuery.toLowerCase().trim();
                    const code = (o.order_code || '').toLowerCase();
                    const name = (o.customer_name || '').toLowerCase();
                    const phone = (o.customer_phone || '').toLowerCase();
                    const addr = (o.customer_address || '').toLowerCase();
                    return code.includes(q) || name.includes(q) || phone.includes(q) || addr.includes(q);
                }

                return true;
            });
        },

        get isExactAmount() {
            return Math.abs(Number(this.confirmForm.collectedAmount || 0) - Number(this.confirmForm.finalAmount || 0)) < 100 && !this.confirmForm.isDebt;
        },

        // ─── Actions & Helpers ─────────────────────────────────────

        recalcVehicleStats() {
            this.codApprovalForm.amount = this.cashInHand;
        },

        formatNumber(val) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(Number(val) || 0));
        },

        isOrderCompleted(order) {
            return ['Đã giao', 'Giao thành công'].includes(order.status);
        },

        getPaymentBadgeClass(status) {
            if (status === 'Đã thanh toán') return 'bg-emerald-50 text-emerald-800 border-emerald-300';
            if (status === 'Ghi nợ đại lý') return 'bg-purple-50 text-purple-800 border-purple-300';
            if (status === 'Thanh toán một phần') return 'bg-amber-50 text-amber-800 border-amber-300';
            return 'bg-amber-50 text-amber-900 border-amber-300';
        },

        hasReturns(order) {
            if (!order) return false;
            if (order.has_returns) return true;
            if (order.total_returned_amount && Number(order.total_returned_amount) > 0) return true;
            if (Array.isArray(order.items) && order.items.some(i => Number(i.returned_qty || 0) > 0)) return true;
            return false;
        },

        getItemDeliveredQty(order, it) {
            if (!it) return 0;
            if (!this.hasReturns(order)) {
                return Number(it.quantity || 0);
            }
            if (it.delivered_qty !== undefined && it.delivered_qty !== null && it.delivered_qty !== '') {
                return Number(it.delivered_qty);
            }
            const q = Number(it.quantity || 0);
            const r = Number(it.returned_qty || 0);
            return Math.max(0, q - r);
        },

        getItemAmount(order, it) {
            if (!it) return 0;
            if (!this.hasReturns(order)) {
                return Number(it.amount || (Number(it.quantity || 0) * Number(it.unit_price || 0)));
            }
            const delivQty = this.getItemDeliveredQty(order, it);
            const price = Number(it.unit_price || 0);
            return delivQty * price;
        },

        getOrderNetDeliveryAmount(order) {
            if (!order) return 0;
            if (!this.hasReturns(order)) return Number(order.total_amount || 0);
            if (Array.isArray(order.items) && order.items.length > 0) {
                return order.items.reduce((s, it) => s + this.getItemAmount(order, it), 0);
            }
            const retAmt = Number(order.total_returned_amount || 0);
            return Math.max(0, Number(order.total_amount || 0) - retAmt);
        },

        getCollectionAmountDisplay(order) {
            if (order.payment_status === 'Đã thanh toán') {
                return '0 đ (Đã thanh toán)';
            }
            if (order.payment_status === 'Ghi nợ đại lý') {
                return '0 đ (Ký nợ đại lý)';
            }
            if (this.isOrderCompleted(order) && order.driver_debt_amount !== undefined && order.driver_debt_amount !== null) {
                return this.formatNumber(order.driver_debt_amount) + ' đ';
            }
            return this.formatNumber(this.getOrderNetDeliveryAmount(order)) + ' đ';
        },

        changeVehicle(plate) {
            const url = new URL(window.location.href);
            url.searchParams.set('vehicle', plate);
            window.location.href = url.toString();
        },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4000);
        },

        // ─── Xác nhận nhanh đơn hàng đã kiểm xong ───────────────────
        async quickConfirmChecked(order) {
            if (!confirm(`Xác nhận đơn hàng #${order.order_code} đã kiểm hàng xong (chuyển sang trạng thái "Đã kiểm hàng")?`)) return;
            try {
                const { ok, data } = await this.safeFetchJson('/orders/update-status', {
                    method: 'POST',
                    body: JSON.stringify({ orderId: order.id, status: 'Đã kiểm hàng' })
                });
                if (ok && data?.success) {
                    order.status = 'Đã kiểm hàng';
                    this.showToast(`✅ Đơn #${order.order_code} đã cập nhật sang trạng thái "Đã kiểm hàng"!`);
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể cập nhật trạng thái'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            }
        },

        // ─── Bấm Xuất Bến ──────────────────────────────────────────
        async departTrip() {
            if (!this.selectedVehicle) return;
            if (!this.isReadyToDepart) {
                alert('Chưa đủ điều kiện xuất bến! Còn đơn hàng chưa kiểm xong. Toàn bộ đơn hàng trên xe phải ở trạng thái "Đã kiểm hàng".');
                return;
            }
            if (!confirm('Xác nhận XE ' + this.selectedVehicle.plate_number + ' BẮT ĐẦU XUẤT BẾN ĐI GIAO HÀNG?\n\nTrạng thái các đơn hàng trên xe sẽ chuyển sang "Đang vận chuyển".')) {
                return;
            }

            this.isDeparting = true;
            try {
                const { ok, data } = await this.safeFetchJson('/delivery/depart', {
                    method: 'POST',
                    body: JSON.stringify({ vehiclePlate: this.selectedVehicle.plate_number })
                });
                if (ok && data?.success) {
                    this.selectedVehicle.status = 'Đang di chuyển';
                    this.selectedVehicle.dispatch_status = 'Đang giao hàng';
                    this.selectedVehicle.departure_time = new Date().toISOString();
                    this.orders.forEach(o => {
                        if (!this.isOrderCompleted(o)) o.status = 'Đang vận chuyển';
                    });
                    this.showToast('🚀 Xe đã xuất bến thành công! Bắt đầu lộ trình.');
                } else {
                    alert('Lỗi xuất bến: ' + (data?.message || 'Không thể xuất bến'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isDeparting = false;
            }
        },

        // ─── Getters cho Khách Hàng, Mã VietQR & Chuyển Khoản ───────
        get customerCodeDisplay() {
            return this.activeOrder?.dealer?.code || ('KH-' + (this.activeOrder?.dealer_id || this.activeOrder?.customer_name || 'LE'));
        },

        get qrTransferMemo() {
            const customerCode = this.customerCodeDisplay;
            const orderCode = this.activeOrder?.order_code || '';
            return `${customerCode} ${orderCode}`.trim();
        },

        get vietQrUrl() {
            const amount = Number(this.confirmForm.collectedAmount || this.confirmForm.finalAmount || 0);
            const memo = encodeURIComponent(this.qrTransferMemo);
            const bankId = this.bankInfo?.bankId || 'MB';
            const acc = (this.bankInfo?.accountNo || '').replace(/[^0-9]/g, '');
            const accName = encodeURIComponent(this.bankInfo?.accountName || '');
            return `https://img.vietqr.io/image/${bankId}-${acc}-compact2.png?amount=${amount}&addInfo=${memo}&accountName=${accName}`;
        },

        get isExactAmount() {
            return !this.confirmForm.isDebt && Number(this.confirmForm.collectedAmount) === Number(this.confirmForm.finalAmount);
        },

        selectPaymentMethod(method) {
            this.confirmForm.paymentMethod = method;
            if (method === 'Ghi nợ') {
                this.confirmForm.isDebt = true;
                this.confirmForm.collectedAmount = 0;
            } else if (method === 'VietQR') {
                this.confirmForm.isDebt = false;
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            } else { // Tiền mặt
                this.confirmForm.isDebt = false;
                if (this.confirmForm.collectedAmount === 0) {
                    this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
                }
            }
            this.recalcPaymentAllocation();
        },

        async copyToClipboard(text, fieldName = '') {
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(String(text));
                } else {
                    const textArea = document.createElement("textarea");
                    textArea.value = String(text);
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
                this.copiedField = fieldName;
                this.showToast('📋 Đã sao chép: ' + text);
                setTimeout(() => { this.copiedField = ''; }, 2000);
            } catch (err) {
                this.showToast('Không thể sao chép: ' + err.message, 'error');
            }
        },

        goBackToList() {
            this.currentView = 'list';
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete('view');
                url.searchParams.delete('order_id');
                window.history.pushState({ view: 'list' }, '', url.toString());
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) {}
        },

        // ─── Mở Màn hình Xác Nhận Giao Đơn (Chuyển sang trang deliver) ────────
        openDeliveryConfirmModal(order) {
            this.activeOrder = order;
            const itemsCopy = (order.items || []).map(it => ({
                id: it.id,
                order_item_id: it.id,
                product_id: it.product_id,
                sku: it.sku,
                name: it.name,
                unit: it.unit,
                quantity: Number(it.quantity || 1),
                unit_price: Number(it.unit_price || 0),
                amount: Number(it.amount || 0),
                returnedQty: 0,
                returnReason: ''
            }));

            const origTotal = Number(order.total_amount || 0);
            const isOrderDebt = order.payment_status === 'Ghi nợ đại lý' || order.payment_method === 'Ghi nợ' || order.payment_method === 'Ký nợ';
            const isOrderQr = order.payment_method === 'Chuyển khoản VietQR' || order.payment_method === 'VietQR';

            let initialMethod = 'Tiền mặt';
            if (isOrderDebt) initialMethod = 'Ghi nợ';
            else if (isOrderQr) initialMethod = 'VietQR';

            this.confirmForm = {
                orderId: order.id,
                orderCode: order.order_code,
                hasReturns: Boolean(order.has_returns),
                items: itemsCopy,
                recipientName: order.customer_name || '',
                deliveryNotes: '',
                paymentMethod: initialMethod,
                collectedAmount: (order.payment_status === 'Đã thanh toán' || isOrderDebt) ? 0 : origTotal,
                finalAmount: origTotal,
                isDebt: isOrderDebt,
                appliedToThisOrder: isOrderDebt ? 0 : origTotal,
                appliedToOldDebt: 0,
                projectedDebtAfter: Number(order.dealer?.current_debt || 0) + (isOrderDebt ? origTotal : 0)
            };

            this.recalcReturnTotals();
            this.currentView = 'deliver';

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('view', 'deliver');
                url.searchParams.set('order_id', order.id);
                window.history.pushState({ view: 'deliver', orderId: order.id }, '', url.toString());
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) {}
        },

        onHasReturnsToggle() {
            if (!this.confirmForm.hasReturns) {
                this.confirmForm.items.forEach(it => {
                    it.returnedQty = 0;
                    it.returnReason = '';
                });
            }
            this.recalcReturnTotals();
        },

        recalcReturnTotals() {
            let totalReturnAmt = 0;
            if (this.confirmForm.hasReturns) {
                this.confirmForm.items.forEach(it => {
                    const rQty = Math.max(0, Math.min(Number(it.quantity || 0), Number(it.returnedQty || 0)));
                    it.returnedQty = rQty;
                    totalReturnAmt += (rQty * Number(it.unit_price || 0));
                });
            }
            const origTotal = Number(this.activeOrder?.total_amount || 0);
            this.confirmForm.finalAmount = Math.max(0, origTotal - totalReturnAmt);

            if (!this.confirmForm.isDebt && this.confirmForm.collectedAmount > this.confirmForm.finalAmount) {
                this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            }
            this.recalcPaymentAllocation();
        },

        setExactPayment() {
            this.confirmForm.isDebt = false;
            this.confirmForm.collectedAmount = this.confirmForm.finalAmount;
            this.recalcPaymentAllocation();
        },

        setFullDebt() {
            this.selectPaymentMethod('Ghi nợ');
        },

        recalcPaymentAllocation() {
            const collected = Math.max(0, Number(this.confirmForm.collectedAmount || 0));
            const finalAmt = Number(this.confirmForm.finalAmount || 0);
            const curDebt = Number(this.activeOrder?.dealer?.current_debt || 0);

            if (this.confirmForm.isDebt || this.confirmForm.paymentMethod === 'Ghi nợ') {
                this.confirmForm.appliedToThisOrder = 0;
                this.confirmForm.appliedToOldDebt = 0;
                this.confirmForm.projectedDebtAfter = curDebt + finalAmt;
                return;
            }

            if (collected >= finalAmt) {
                this.confirmForm.appliedToThisOrder = finalAmt;
                this.confirmForm.appliedToOldDebt = collected - finalAmt;
                this.confirmForm.projectedDebtAfter = Math.max(0, curDebt - this.confirmForm.appliedToOldDebt);
            } else {
                this.confirmForm.appliedToThisOrder = collected;
                this.confirmForm.appliedToOldDebt = 0;
                this.confirmForm.projectedDebtAfter = curDebt + (finalAmt - collected);
            }
        },

        // ─── Xác Nhận Giao Hàng & Thu Tiền ─────────────────────────
        async submitDeliveryCompletion() {
            if (!this.activeOrder) return;
            if (!this.confirmForm.recipientName.trim()) {
                alert('Vui lòng nhập họ tên người nhận hàng tại điểm giao!');
                return;
            }

            let methodLabel = this.confirmForm.paymentMethod;
            if (this.confirmForm.isDebt) methodLabel = 'Ghi nợ đại lý';

            const confirmMsg = `XÁC NHẬN GIAO ĐƠN #${this.activeOrder.order_code}?\n\n` +
                `- Người nhận: ${this.confirmForm.recipientName}\n` +
                `- Hình thức: ${methodLabel}\n` +
                (this.confirmForm.isDebt ? `- Ghi nợ vào sổ đại lý: ${this.formatNumber(this.confirmForm.finalAmount)} đ\n` : `- Tiền thu: ${this.formatNumber(this.confirmForm.collectedAmount)} đ\n`) +
                (this.confirmForm.hasReturns ? `- Có hàng trả lại: trừ ${this.formatNumber(this.activeOrder.total_amount - this.confirmForm.finalAmount)} đ\n` : '- Giao đủ 100% không trả hàng\n');

            if (!confirm(confirmMsg)) return;

            this.isSubmittingDelivery = true;
            try {
                const payload = {
                    orderId: this.activeOrder.id,
                    orderCode: this.activeOrder.order_code,
                    recipientName: this.confirmForm.recipientName,
                    deliveryNotes: this.confirmForm.deliveryNotes,
                    paymentMethod: this.confirmForm.paymentMethod,
                    collectedAmount: this.confirmForm.collectedAmount,
                    isDebt: this.confirmForm.isDebt,
                    hasReturns: this.confirmForm.hasReturns,
                    returnedItems: this.confirmForm.hasReturns ? this.confirmForm.items.filter(it => it.returnedQty > 0) : []
                };

                const { ok, data } = await this.safeFetchJson('/delivery/complete-order', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                if (ok && data?.success) {
                    this.activeOrder.status = 'Đã giao';
                    this.activeOrder.payment_status = data.order?.paymentStatus || (this.confirmForm.isDebt ? 'Ghi nợ đại lý' : 'Đã thanh toán');
                    this.activeOrder.payment_method = data.order?.paymentMethod || this.confirmForm.paymentMethod;
                    this.activeOrder.driver_debt_amount = data.order?.driverDebtAmount || (this.confirmForm.paymentMethod === 'Tiền mặt' ? this.confirmForm.collectedAmount : 0);
                    this.activeOrder.has_returns = data.order?.hasReturns || false;
                    this.activeOrder.total_returned_amount = data.order?.totalReturnedAmount || 0;

                    if (payload.hasReturns && payload.returnedItems.length > 0) {
                        this.activeOrder.has_returns = true;
                        payload.returnedItems.forEach(rit => {
                            const item = (this.activeOrder.items || []).find(i => (i.id && i.id === rit.id) || (i.sku && i.sku === rit.sku));
                            if (item) {
                                item.returned_qty = Number(rit.returnedQty || 0);
                                item.delivered_qty = Math.max(0, Number(item.quantity) - Number(rit.returnedQty || 0));
                            }
                        });
                        const newReturnSlip = {
                            id: Date.now(),
                            slipCode: 'TH-' + this.activeOrder.order_code,
                            orderCode: this.activeOrder.order_code,
                            dealerName: this.activeOrder.customer_name,
                            totalReturnedAmount: payload.returnedItems.reduce((s, it) => s + (it.returnedQty * it.unit_price), 0),
                            items: payload.returnedItems.map(it => ({
                                name: it.name,
                                returnedQty: it.returnedQty,
                                unit: it.unit,
                                unitPrice: it.unit_price,
                                reason: it.returnReason
                            }))
                        };
                        this.returnsList = [newReturnSlip, ...this.returnsList];
                    }

                    this.goBackToList();
                    this.recalcVehicleStats();
                    this.showToast('🎉 Đã xác nhận giao hàng và hoàn tất đơn thành công!');
                } else {
                    alert('Lỗi xác nhận: ' + (data?.message || 'Không thể xác nhận giao hàng'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isSubmittingDelivery = false;
            }
        },

        // ─── Báo Giao Không Thành Công ─────────────────────────────
        openFailModal() {
            this.failForm = {
                reason: 'Khách hẹn giao lại vào buổi sau',
                rescheduledDate: new Date(Date.now() + 86400000).toISOString().split('T')[0],
                notes: ''
            };
            this.showFailModal = true;
        },

        async submitFailDelivery() {
            if (!this.activeOrder) return;
            this.isSubmittingFail = true;
            try {
                const { ok, data } = await this.safeFetchJson('/delivery/fail-order', {
                    method: 'POST',
                    body: JSON.stringify({
                        orderId: this.activeOrder.id,
                        orderCode: this.activeOrder.order_code,
                        reason: this.failForm.reason,
                        rescheduledDate: this.failForm.rescheduledDate,
                        notes: this.failForm.notes
                    })
                });
                if (ok && data?.success) {
                    this.activeOrder.status = 'Không thành công';
                    this.showFailModal = false;
                    this.showDeliveryModal = false;
                    this.showToast('Đã ghi nhận báo giao hàng không thành công.');
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể báo giao không thành công'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isSubmittingFail = false;
            }
        },

        onAccountantSelectChange(e) {
            const acc = this.accountants.find(a => String(a.id) === String(e.target.value));
            this.codApprovalForm.accountantName = acc ? acc.name : '';
        },

        onIntakeUserSelectChange(e) {
            const staff = this.warehouseStaff.find(s => String(s.id) === String(e.target.value));
            this.intakeForm.intakeUserName = staff ? staff.name : '';
        },

                // ─── Bàn Giao Công Nợ Cho Sale (Quy trình 2 bước) ─────────
        get candidateDebtOrders() {
            return this.orders.filter(o => {
                // Đơn nào đã bàn giao rồi và chưa bị từ chối thì KHÔNG được bàn giao lại nữa
                if (o.is_debt_handed_over) return false;

                return (
                    o.payment_status === 'Ghi nợ đại lý' || 
                    (Number(o.driver_debt_amount || 0) > 0 && o.payment_method !== 'Tiền mặt' && o.payment_method !== 'Chuyển khoản VietQR') ||
                    ['Đã giao', 'Giao thành công'].includes(o.status)
                );
            });
        },

        getOrderDebtValue(ord) {
            if (Number(ord.driver_debt_amount || 0) > 0) return Number(ord.driver_debt_amount);
            if (Number(ord.final_amount || 0) > 0) return Number(ord.final_amount);
            return Number(ord.total_amount || 0);
        },

        selectAllDebtOrders(select) {
            if (select) {
                this.debtHandoverForm.selectedOrderIds = this.candidateDebtOrders.map(o => o.id);
            } else {
                this.debtHandoverForm.selectedOrderIds = [];
            }
            this.recalcDebtHandoverAmount();
        },

        recalcDebtHandoverAmount() {
            const selectedIds = this.debtHandoverForm.selectedOrderIds.map(String);
            const sum = this.candidateDebtOrders
                .filter(o => selectedIds.includes(String(o.id)))
                .reduce((s, o) => s + this.getOrderDebtValue(o), 0);
            this.debtHandoverForm.amount = sum;
        },

        handleDebtDocUpload(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            Array.from(files).forEach(file => {
                if (!file.type.startsWith('image/')) {
                    alert('Vui lòng chỉ chọn tệp hình ảnh (JPG, PNG, WebP)!');
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    alert(`Ảnh ${file.name} vượt quá dung lượng cho phép (10MB)!`);
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (!this.debtHandoverForm.documents) {
                        this.debtHandoverForm.documents = [];
                    }
                    this.debtHandoverForm.documents.push(e.target.result);
                };
                reader.readAsDataURL(file);
            });
            event.target.value = '';
        },

        removeDebtDoc(index) {
            if (this.debtHandoverForm.documents) {
                this.debtHandoverForm.documents.splice(index, 1);
            }
        },

        viewDocPreview(url) {
            this.previewDocUrl = url;
            this.showDocPreviewModal = true;
        },

        openDebtHandoverModal(specificOrder = null) {
            if (specificOrder && specificOrder.is_debt_handed_over) {
                alert(`Đơn hàng ${specificOrder.order_code} đã được lập biên bản bàn giao nợ trước đó (${specificOrder.debt_handover_info?.code || ''}) và đang được xử lý, không thể bàn giao lại trừ khi bị từ chối!`);
                return;
            }
            this.debtHandoverForm.documents = [];
            if (specificOrder) {
                this.debtHandoverForm.selectedOrderIds = [specificOrder.id];
                this.debtHandoverForm.amount = this.getOrderDebtValue(specificOrder);
                this.debtHandoverForm.notes = `Bàn giao công nợ đơn hàng ${specificOrder.order_code} kèm chứng từ đối soát`;
            } else {
                const debtOrders = this.candidateDebtOrders.filter(o => o.payment_status === 'Ghi nợ đại lý' || Number(o.driver_debt_amount || 0) > 0);
                if (debtOrders.length > 0) {
                    this.debtHandoverForm.selectedOrderIds = debtOrders.map(o => o.id);
                    this.debtHandoverForm.amount = debtOrders.reduce((s, o) => s + this.getOrderDebtValue(o), 0);
                } else {
                    this.debtHandoverForm.selectedOrderIds = [];
                    this.debtHandoverForm.amount = 0;
                }
                this.debtHandoverForm.notes = 'Bàn giao công nợ các đơn hàng trên xe kèm chứng từ đối soát cho Kế toán.';
            }

            if (this.accountants.length > 0 && !this.debtHandoverForm.accountantId) {
                this.debtHandoverForm.accountantId = this.accountants[0].id;
            }
            this.debtHandoverForm.toUserId = null;

            this.showDebtHandoverModal = true;
        },

        async submitDebtHandover() {
            if (!this.debtHandoverForm.accountantId) {
                alert('Vui lòng chọn Kế toán kiểm tra chứng từ (Bước 1)!');
                return;
            }

            if (this.debtHandoverForm.selectedOrderIds.length === 0 && Number(this.debtHandoverForm.amount || 0) <= 0) {
                alert('Vui lòng chọn ít nhất 1 đơn hàng hoặc nhập số tiền nợ cần bàn giao!');
                return;
            }

            if (!this.debtHandoverForm.documents || this.debtHandoverForm.documents.length === 0) {
                if (!confirm('Bạn chưa đính kèm ảnh chứng từ (phiếu giao có chữ ký nhận nợ). Bạn có chắc chắn muốn gửi biên bản này không?')) {
                    return;
                }
            }

            const acc = this.accountants.find(a => String(a.id) === String(this.debtHandoverForm.accountantId));
            const accName = acc ? acc.name : 'Kế toán';

            if (!confirm(`Xác nhận gửi biên bản bàn giao nợ ${this.formatNumber(this.debtHandoverForm.amount)} đ kèm ${this.debtHandoverForm.documents?.length || 0} chứng từ cho Kế toán [${accName}]?`)) {
                return;
            }

            this.isSubmittingDebtHandover = true;
            try {
                const payload = {
                    order_ids: this.debtHandoverForm.selectedOrderIds,
                    accountant_id: this.debtHandoverForm.accountantId,
                    to_user_id: this.debtHandoverForm.toUserId || null,
                    amount: this.debtHandoverForm.amount,
                    notes: this.debtHandoverForm.notes || '',
                    documents: this.debtHandoverForm.documents || []
                };

                const { ok, data } = await this.safeFetchJson('/debt-handovers', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                if (ok && data?.success) {
                    this.showDebtHandoverModal = false;
                    this.showToast(`Đã gửi bàn giao nợ kèm chứng từ cho Kế toán [${accName}] thành công! Kế toán sẽ kiểm tra và chuyển tiếp cho Sale.`, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    alert('Lỗi bàn giao nợ: ' + (data?.message || 'Vui lòng kiểm tra lại thông tin'));
                }
            } catch (err) {
                alert('Lỗi khi gửi bàn giao nợ: ' + err.message);
            } finally {
                this.isSubmittingDebtHandover = false;
            }
        },

        // ─── Gửi Bàn Giao Tiền COD Cho Kế Toán Duyệt ─────────────
        openHandoverModal() {
            this.codApprovalForm.amount = this.cashInHand;
            this.codApprovalForm.notes = 'Bàn giao nộp tiền COD cuối chuyến xe cho Kế toán kiểm đếm.';
            if (this.accountants.length > 0 && !this.codApprovalForm.accountantId) {
                this.codApprovalForm.accountantId = this.accountants[0].id;
                this.codApprovalForm.accountantName = this.accountants[0].name;
            }
            this.showCodApprovalModal = true;
        },

        async confirmSettleCash() {
            if (!this.codApprovalForm.accountantId) {
                alert('Vui lòng chọn Kế toán / Thủ quỹ nhận tiền bàn giao!');
                return;
            }

            const completedCodes = this.orders
                .filter(o => ['Đã giao', 'Giao thành công'].includes(o.status))
                .map(o => o.order_code);

            const confirmMsg = `Xác nhận gửi biên bản nộp tiền ${this.formatNumber(this.codApprovalForm.amount)} đ cho Kế toán [${this.codApprovalForm.accountantName}] kiểm đếm và duyệt?`;
            if (!confirm(confirmMsg)) {
                return;
            }

            this.isSettlingCash = true;
            try {
                const { ok, data } = await this.safeFetchJson('/delivery-dispatches/settle-cash', {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'submit', // Lái xe gửi yêu cầu bàn giao nộp tiền chờ duyệt
                        vehiclePlate: this.selectedVehicle?.plate_number,
                        driverName: this.selectedVehicle?.driver?.name,
                        accountantId: this.codApprovalForm.accountantId,
                        accountantName: this.codApprovalForm.accountantName,
                        amount: this.codApprovalForm.amount,
                        handoverAmount: this.codApprovalForm.amount,
                        orderCodes: completedCodes,
                        notes: this.codApprovalForm.notes
                    })
                });
                if (ok && data?.success) {
                    this.showCodApprovalModal = false;
                    this.hasPendingCodSubmission = true;
                    this.pendingCodInfo = {
                        code: data.code,
                        amount: this.codApprovalForm.amount,
                        accountantName: this.codApprovalForm.accountantName
                    };

                    // Cập nhật trạng thái hiển thị của các đơn hàng thành "Chờ duyệt nộp quỹ"
                    this.orders.forEach(o => {
                        if (['Đã giao', 'Giao thành công'].includes(o.status)) {
                            o.payment_status = 'Chờ duyệt nộp quỹ';
                        }
                    });

                    this.showToast(`📩 Đã gửi biên bản nộp tiền COD cho Kế toán ${this.codApprovalForm.accountantName} chờ duyệt! Đơn hàng sẽ được giải phóng ngay sau khi Kế toán kiểm đếm xong.`);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể gửi yêu cầu'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isSettlingCash = false;
            }
        },

        async checkPendingCodStatus() {
            if (!this.selectedVehicle) return;
            this.isCheckingCodStatus = true;
            try {
                const { ok, data } = await this.safeFetchJson('/delivery-dispatches/driver-cash-handovers');
                if (ok && data?.handovers && Array.isArray(data.handovers)) {
                    const approved = data.handovers.find(h => 
                        (h.vehiclePlate === this.selectedVehicle?.plate_number || h.vehicle_plate === this.selectedVehicle?.plate_number)
                        && h.status === 'Đã hoàn thành'
                    );
                    if (approved) {
                        // Kế toán đã duyệt thành công -> Giải phóng đơn hàng trên xe
                        this.orders = this.orders.filter(o => !['Đã giao', 'Giao thành công', 'Đã quyết toán'].includes(o.status));
                        this.hasPendingCodSubmission = false;
                        this.recalcVehicleStats();
                        if (this.orders.length === 0) {
                            this.selectedVehicle.status = 'Sẵn sàng';
                            this.selectedVehicle.dispatch_status = 'Rảnh';
                            this.selectedVehicle.departure_time = null;
                        }
                        this.showToast(`🎉 Kế toán ${approved.approvedByName || approved.accountantName || ''} đã duyệt COD (${this.formatNumber(approved.handoverAmount || approved.handover_amount)} đ) và giải phóng các đơn hàng ra khỏi xe!`);
                        return;
                    }
                }
                this.showToast('⏳ Kế toán đang kiểm đếm tiền mặt, chưa hoàn tất duyệt. Vui lòng kiểm tra lại sau ít phút!');
            } catch (e) {
                console.error(e);
            } finally {
                this.isCheckingCodStatus = false;
            }
        },

        // ─── Gửi Bàn Giao Hàng Hoàn Trả Cho Thủ Kho Duyệt ─────────
        openAcceptHoldingModal(slip = null) {
            if (slip) {
                this.holdingReturnSlipsToAccept = [slip];
            } else {
                this.holdingReturnSlipsToAccept = this.pendingReturnsList;
            }

            if (this.holdingReturnSlipsToAccept.length === 0) {
                alert('Không có phiếu hoàn trả nào khả dụng để nhận ôm.');
                return;
            }

            this.agreeHoldingTerms = true;
            this.showAcceptHoldingModal = true;
        },

        async confirmAcceptHoldingReturn() {
            if (this.isAcceptingHolding || this.holdingReturnSlipsToAccept.length === 0) return;
            if (!this.agreeHoldingTerms) {
                alert('Vui lòng tích chọn cam kết chịu trách nhiệm hàng ôm trước khi lưu.');
                return;
            }

            this.isAcceptingHolding = true;
            try {
                const slipIds = this.holdingReturnSlipsToAccept.map(r => r.id);

                const { ok, data } = await this.safeFetchJson('/delivery-returns/accept-holding', {
                    method: 'POST',
                    body: JSON.stringify({
                        slipIds: slipIds,
                        vehiclePlate: this.selectedVehicle?.plate_number || '{{ $selectedPlate ?? '' }}'
                    })
                });

                if (ok && data?.success) {
                    const acceptedIds = this.holdingReturnSlipsToAccept.map(r => String(r.id));
                    (this.returnsList || []).forEach(r => {
                        if (acceptedIds.includes(String(r.id))) {
                            r.status = 'Tài xế đã nhận ôm hàng';
                        }
                    });

                    this.showToast(`🎉 ${data.message}`);
                    this.showAcceptHoldingModal = false;
                    if (this.showReturnsModal) {
                        this.showReturnsModal = false;
                    }

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('❌ Lỗi: ' + (data?.message || 'Không thể nhận ôm hàng'));
                }
            } catch (e) {
                console.error('Error accept holding return:', e);
                alert('Lỗi: ' + e.message);
            } finally {
                this.isAcceptingHolding = false;
            }
        },

        async confirmIntakeAllReturns() {
            if (this.returnsList.length === 0) {
                alert('Không có phiếu hàng hoàn trả nào trên xe.');
                return;
            }
            if (!this.intakeForm.intakeUserId) {
                alert('Vui lòng chọn Thủ kho nhận bàn giao!');
                return;
            }

            const confirmMsg = `Xác nhận gửi biên bản bàn giao ${this.returnsList.length} phiếu hàng hoàn trả (${this.formatNumber(this.returnsTotalAmount)} đ) cho Thủ kho [${this.intakeForm.intakeUserName}] kiểm đếm và duyệt nhập kho?`;
            if (!confirm(confirmMsg)) {
                return;
            }

            this.isIntakingReturns = true;
            try {
                const slipIds = this.returnsList.map(r => r.id);
                const { ok, data } = await this.safeFetchJson('/delivery-returns/intake-warehouse', {
                    method: 'POST',
                    body: JSON.stringify({
                        action: 'submit',
                        slipIds: slipIds,
                        vehiclePlate: this.selectedVehicle?.plate_number,
                        intakeUserId: this.intakeForm.intakeUserId,
                        intakeUserName: this.intakeForm.intakeUserName,
                        warehouseId: this.intakeForm.warehouseId,
                        notes: this.intakeForm.notes
                    })
                });
                if (ok && data?.success) {
                    this.showIntakeWarehouseModal = false;
                    this.hasPendingReturnsSubmission = true;
                    this.pendingReturnsInfo = {
                        staffName: this.intakeForm.intakeUserName,
                        count: this.returnsList.length,
                        amount: this.returnsTotalAmount
                    };

                    this.returnsList.forEach(r => {
                        r.status = 'Chờ thủ kho duyệt nhập';
                    });

                    this.showToast(`📩 Đã gửi biên bản bàn giao ${this.returnsList.length} phiếu hoàn trả cho Thủ kho ${this.intakeForm.intakeUserName} chờ duyệt! Hàng sẽ được giải phóng ngay sau khi Thủ kho duyệt tại phân hệ Nhập kho.`);
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể gửi yêu cầu bàn giao'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isIntakingReturns = false;
            }
        },

        async checkPendingReturnsStatus() {
            if (!this.selectedVehicle) return;
            this.isCheckingReturnsStatus = true;
            try {
                const { ok, data } = await this.safeFetchJson('/delivery-returns');
                if (ok && data?.returns && Array.isArray(data.returns)) {
                    // Lấy tất cả phiếu của xe này
                    const currentVehicleSlips = data.returns.filter(r => 
                        (r.vehiclePlate === this.selectedVehicle?.plate_number || r.vehicle_plate === this.selectedVehicle?.plate_number)
                    );

                    // Kiểm tra các phiếu còn đang chờ duyệt trên xe
                    const stillPending = currentVehicleSlips.filter(r => 
                        ['Chở trên xe', 'Chờ thủ kho duyệt nhập', 'Chờ nhập kho lại'].includes(r.status)
                    );

                    if (stillPending.length === 0 && this.returnsList.length > 0) {
                        // Toàn bộ đã được duyệt nhập kho -> Giải phóng khỏi xe
                        this.returnsList = [];
                        this.hasPendingReturnsSubmission = false;
                        this.showToast('🎉 Thủ kho đã kiểm đếm và duyệt nhập kho lại toàn bộ hàng hoàn trả! Đã giải phóng hàng trả về khỏi xe.');
                        return;
                    } else if (stillPending.length < this.returnsList.length) {
                        // Một số phiếu đã duyệt
                        this.returnsList = stillPending;
                        this.showToast(`Đã duyệt một phần! Còn lại ${this.returnsList.length} phiếu hoàn trả trên xe.`);
                        return;
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.isCheckingReturnsStatus = false;
            }
        },

        // ─── Bấm Quay Về (Về Bãi / Kho) ──────────────────────────
        async returnToDepot() {
            if (!this.selectedVehicle) return;
            if (!confirm(`Xác nhận xe ${this.selectedVehicle.plate_number} đã hoàn thành giao toàn bộ đơn và bắt đầu quay về bãi / kho?`)) {
                return;
            }

            this.isReturningVehicle = true;
            try {
                const { ok, data } = await this.safeFetchJson('/vehicles/update-status', {
                    method: 'POST',
                    body: JSON.stringify({
                        plateNumber: this.selectedVehicle.plate_number,
                        status: 'Đang di chuyển',
                        dispatchStatus: 'Trên đường về'
                    })
                });
                if (ok && data?.success) {
                    this.selectedVehicle.dispatch_status = 'Trên đường về';
                    this.selectedVehicle.status = 'Đang di chuyển';
                    this.showToast('🚚 Xe đang trên đường quay về bãi / kho!');
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể cập nhật trạng thái'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isReturningVehicle = false;
            }
        },

        // ─── Đã Về Tới Bãi / Kho (Kết thúc chuyến & Hoàn đơn vào kho) ────────────────
        returnFailedOrderToWarehouse(order) {
            this.openOrderReturnWarehouseModal(order);
        },

        openOrderReturnWarehouseModal(order) {
            this.orderReturnForm = {
                order: order,
                intakeStaffId: '',
                intakeStaffSearch: '',
                warehouseId: (this.warehouses && this.warehouses[0]) ? this.warehouses[0].id : '',
                notes: order.failed_delivery_reason ? ('Hoàn kho do: ' + order.failed_delivery_reason) : '',
                isSubmitting: false,
                isStaffDropdownOpen: false
            };
            this.showOrderReturnWarehouseModal = true;
        },

        async approveOrderReturnDirectly(order) {
            if (!order) return;
            if (!confirm(`Xác nhận Thủ kho đã kiểm đếm đủ hàng và duyệt nhập kho lại cho đơn ${order.order_code}? Tồn kho sẽ được cộng lại ngay vào hệ thống.`)) return;

            try {
                const { ok, data } = await this.safeFetchJson('/delivery/warehouse-approve-order-return', {
                    method: 'POST',
                    body: JSON.stringify({
                        orderId: order.id,
                        orderCode: order.order_code
                    })
                });
                if (ok && data?.success) {
                    order.status = 'Đã hoàn kho';
                    if (this.inspectOrder && this.inspectOrder.id === order.id) {
                        this.inspectOrder.status = 'Đã hoàn kho';
                    }
                    this.showToast('✅ Thủ kho đã duyệt nhập kho thành công! Tồn kho đã được hoàn lại.');
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể duyệt nhập'));
                }
            } catch(e) {
                alert('Lỗi: ' + e.message);
            }
        },

        async submitOrderReturnWarehouse() {
            if (!this.orderReturnForm.order) return;
            if (!this.orderReturnForm.intakeStaffId) {
                alert('Vui lòng chọn Thủ kho / Người xác nhận nhận hàng hoàn vào kho!');
                return;
            }

            const staffObj = (this.warehouseStaff || []).find(s => String(s.id) === String(this.orderReturnForm.intakeStaffId));
            const intakeStaffName = staffObj ? staffObj.name : '';

            const whObj = (this.warehouses || []).find(w => String(w.id) === String(this.orderReturnForm.warehouseId));
            const warehouseName = whObj ? whObj.name : 'Kho Tổng Mê Linh (MQ)';

            this.orderReturnForm.isSubmitting = true;
            try {
                const { ok, data } = await this.safeFetchJson('/delivery/return-order-to-warehouse', {
                    method: 'POST',
                    body: JSON.stringify({
                        orderId: this.orderReturnForm.order.id,
                        orderCode: this.orderReturnForm.order.order_code,
                        intakeStaffId: this.orderReturnForm.intakeStaffId,
                        intakeStaffName: intakeStaffName,
                        warehouseId: this.orderReturnForm.warehouseId,
                        warehouseName: warehouseName,
                        notes: this.orderReturnForm.notes,
                        reason: this.orderReturnForm.order.failed_delivery_reason || 'Xe trở về bãi: Hoàn trả hàng giao không thành công về kho'
                    })
                });
                if (ok && data?.success) {
                    this.orderReturnForm.order.status = data.order?.status || 'Chờ thủ kho duyệt hoàn';
                    this.showOrderReturnWarehouseModal = false;
                    this.showToast('📦 Đã gửi thông báo cho Thủ kho ' + (intakeStaffName ? ('[' + intakeStaffName + ']') : '') + '! Đang chờ thủ kho kiểm đếm và duyệt nhập kho.');
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể hoàn kho'));
                }
            } catch(e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.orderReturnForm.isSubmitting = false;
            }
        },

        async finishTripArrival() {
            if (!this.selectedVehicle) return;
            if (!confirm(`Xác nhận xe ${this.selectedVehicle.plate_number} đã về tới bãi / kho an toàn và kết thúc chuyến hàng?`)) {
                return;
            }

            this.isReturningVehicle = true;
            try {
                const { ok, data } = await this.safeFetchJson('/vehicles/update-status', {
                    method: 'POST',
                    body: JSON.stringify({
                        plateNumber: this.selectedVehicle.plate_number,
                        status: 'Sẵn sàng',
                        dispatchStatus: 'Rảnh'
                    })
                });
                if (ok && data?.success) {
                    this.selectedVehicle.dispatch_status = 'Rảnh';
                    this.selectedVehicle.status = 'Sẵn sàng';
                    this.selectedVehicle.departure_time = null;
                    this.showToast('🏁 Xe đã về bãi an toàn! Chuyến giao hàng đã hoàn thành.');
                } else {
                    alert('Lỗi: ' + (data?.message || 'Không thể cập nhật trạng thái'));
                }
            } catch (e) {
                alert('Lỗi: ' + e.message);
            } finally {
                this.isReturningVehicle = false;
            }
        },

        // ─── Quick Views & Prints ──────────────────────────────────
        openViewOrderModal(order) {
            this.inspectOrder = order;
            this.showOrderViewModal = true;
        },

        printDeliverySlip(order) {
            if (!order) return;
            const url = '/orders/' + order.id + '/edit?mode=print';
            window.open(url, '_blank', 'width=900,height=800');
        }
    };
}
</script>
