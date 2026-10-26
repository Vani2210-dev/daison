    <!-- ============================================================== -->
    <!-- MODAL: XEM MÃ QR & QUÉT THỬ                                    -->
    <!-- ============================================================== -->
    <div x-show="modals.qrPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="closeModal('qrPreviewModal')" class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-sm overflow-hidden text-center">
            <div class="p-4 border-b border-slate-100 bg-gradient-to-r from-blue-700 via-indigo-700 to-cyan-600 text-white flex items-center justify-between">
                <span class="font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-cyan-300"></i>
                    Tem QR Bảo Hành Điện Tử
                </span>
                <button type="button" @click="closeModal('qrPreviewModal')" class="text-white/80 hover:text-white p-1 cursor-pointer">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-center">
                    <div id="largeQrBox" class="p-3 bg-white border-2 border-slate-800 rounded-2xl shadow-md inline-block"></div>
                </div>

                <div>
                    <div class="text-xs font-mono font-bold text-slate-500 uppercase" x-text="previewMachine?.machine_code"></div>
                    <h3 class="text-base font-extrabold text-slate-800 mt-0.5" x-text="previewMachine?.machine_model || 'Máy lọc nước'"></h3>
                    <p class="text-xs text-blue-600 font-mono font-bold mt-1" x-text="previewMachine?.qr_code"></p>
                </div>

                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-left text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Trạng thái:</span>
                        <strong class="text-slate-800" x-text="previewMachine?.status === 'unactivated' ? 'Tem trống (chưa kích hoạt)' : (previewMachine?.status === 'pending_activation' ? 'Chờ duyệt kích hoạt' : 'Đã kích hoạt')"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Khách hàng:</span>
                        <strong class="text-slate-800" x-text="previewMachine?.customer_name || '(Chưa gán)'"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Số lần quét:</span>
                        <strong class="text-blue-700" x-text="(previewMachine?.view_count || 0) + ' lượt'"></strong>
                    </div>
                </div>

                <div class="space-y-2 pt-1 text-xs">
                    <a :href="'/m/' + previewMachine?.qr_token" target="_blank"
                       class="w-full py-2.5 px-4 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20 flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Mở Trang Quét QR (/m/...)</span>
                    </a>
                    <a :href="'/bao-hanh-may-loc/print-qr?ids=' + previewMachine?.id" target="_blank"
                       class="w-full py-2 px-4 rounded-xl font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-print"></i>
                        <span>In Tem Dán Này</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

