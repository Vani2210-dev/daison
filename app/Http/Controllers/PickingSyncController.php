<?php

namespace App\Http\Controllers;

use App\Models\PickingSyncEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PickingSyncController extends Controller
{
    /**
     * API: Nhận thông tin bốc hàng/phân công từ 1 tài khoản và lưu sự kiện đồng bộ
     */
    public function updateItem(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }

        $orderId = $request->input('orderId') 
            ?? $request->input('order_id') 
            ?? ($raw['orderId'] ?? null) 
            ?? ($raw['order_id'] ?? null);

        $itemId = $request->input('itemId') 
            ?? $request->input('item_id') 
            ?? ($raw['itemId'] ?? null) 
            ?? ($raw['item_id'] ?? null);

        $sku = $request->input('sku') ?? ($raw['sku'] ?? null);
        $pickedQty = (float) ($request->input('pickedQty') ?? $request->input('picked_qty') ?? ($raw['pickedQty'] ?? 0));
        $reqQty = isset($raw['reqQty']) ? (float) $raw['reqQty'] : (isset($raw['req_qty']) ? (float) $raw['req_qty'] : null);
        $itemStatus = $request->input('itemStatus') ?? $request->input('item_status') ?? ($raw['itemStatus'] ?? 'Chờ bốc');
        $shortageReason = $request->input('shortageReason') ?? $request->input('shortage_reason') ?? ($raw['shortageReason'] ?? null);
        $performerName = $request->input('performerName') 
            ?? $request->input('performer_name') 
            ?? ($raw['performerName'] ?? null) 
            ?? (auth()->user()?->name ?? 'Nhân sự kho');

        $eventType = $request->input('eventType') ?? ($raw['eventType'] ?? 'ITEM_UPDATE');
        $assignedStaff = $request->input('assignedStaff') 
            ?? $request->input('assigned_staff') 
            ?? ($raw['assignedStaff'] ?? null) 
            ?? ($raw['assigned_staff'] ?? null);
        $assignedStaffList = $request->input('assignedStaffList') 
            ?? $request->input('assigned_staff_list') 
            ?? ($raw['assignedStaffList'] ?? null) 
            ?? ($raw['assigned_staff_list'] ?? null);
        $orderStatus = $request->input('orderStatus') ?? ($raw['orderStatus'] ?? null);
        $shortageReported = false;
        $extraData = $request->input('extraData') ?? ($raw['extraData'] ?? null);

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'Missing orderId'], 422);
        }

        // Tạo sự kiện đồng bộ mới
        $event = PickingSyncEvent::create([
            'order_id'            => (string) $orderId,
            'item_id'             => $itemId ? (string) $itemId : null,
            'sku'                 => $sku ? (string) $sku : null,
            'picked_qty'          => $pickedQty,
            'req_qty'             => $reqQty,
            'item_status'         => (string) $itemStatus,
            'shortage_reason'     => $shortageReason ? (string) $shortageReason : null,
            'performer_name'      => (string) $performerName,
            'event_type'          => (string) $eventType,
            'assigned_staff'      => $assignedStaff ? (string) $assignedStaff : null,
            'assigned_staff_list' => is_array($assignedStaffList) ? $assignedStaffList : ($assignedStaffList ? [$assignedStaffList] : null),
            'order_status'        => $orderStatus ? (string) $orderStatus : null,
            'shortage_reported'   => $shortageReported,
            'extra_data'          => $extraData,
            'created_at'          => now(),
        ]);

        // Tự động đồng bộ và lưu trực tiếp vào CSDL MySQL (bảng orders và order_items)
        try {
            $order = null;
            if (is_numeric($orderId) && (int)$orderId >= 900000) {
                $order = \App\Models\ConsignmentOrder::with(['items.product.category', 'staff', 'warehouse'])->find((int)$orderId - 900000);
            }
            if (!$order) {
                $order = \App\Models\ConsignmentOrder::with(['items.product.category', 'staff', 'warehouse'])->where('code', $orderId)->first();
            }
            if (!$order) {
                $order = \App\Models\Order::where('id', $orderId)->orWhere('order_code', $orderId)->first();
            }
            if ($order) {
                if ($order->shortage_reported) {
                    $order->shortage_reported = false;
                    $order->shortage_reported_at = null;
                    $order->shortage_reported_note = null;
                }

                // Cập nhật từng mặt hàng cụ thể nếu có itemId hoặc sku
                if ($itemId || $sku) {
                    $orderItem = null;
                    if ($itemId && is_numeric($itemId)) {
                        $orderItem = $order->items()->where('id', $itemId)->first();
                    }
                    if (!$orderItem && $sku) {
                        $orderItem = $order->items()->where('sku', $sku)->first();
                    }

                    if ($orderItem) {
                        $reqQtyTotal = (float) ($orderItem->quantity + ($orderItem->gift_quantity ?? 0));
                        $isQcUpdate = !empty($extraData['isQc']) 
                            || isset($extraData['isChecked']) 
                            || isset($extraData['checkedQty']) 
                            || isset($extraData['checkNotes']) 
                            || !empty($extraData['qcAllChecked']) 
                            || !empty($extraData['qcCategoryChecked']);

                        // Kiểm tra quyền kiểm hàng (QC) khi thao tác là QC
                        if ($isQcUpdate) {
                            $authUser = auth()->user();
                            $canQc = $authUser && ($authUser->id === 1 
                                || $authUser->hasRole('admin') 
                                || $authUser->hasRole('Admin') 
                                || $authUser->hasRole('super-admin')
                                || $authUser->hasRole('warehouse')
                                || $authUser->hasPermission('inv_picking.qc')
                                || $authUser->hasPermission('inv_qc.qc')
                                || $authUser->hasPermission('inv_qc.approve')
                                || $authUser->hasPermission('inv_picking.assign')
                                || $authUser->hasPermission('inv_picking.view_all_categories')
                                || \App\Models\StaffGroup::where('status', 'active')->where('user_id', $authUser->id)->exists()
                            );

                            if (!$canQc) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Bạn không có quyền thực hiện kiểm hàng (QC) cho mặt hàng này!'
                                ], 403);
                            }
                        }

                        // Chỉ kiểm tra quyền soạn bốc khi KHÔNG PHẢI thao tác QC và số lượng bốc thực sự bị thay đổi
                        $hasPickedQtyParam = ($request->has('pickedQty') && $request->input('pickedQty') !== null) 
                            || (isset($raw['pickedQty']) && $raw['pickedQty'] !== null) 
                            || ($request->has('picked_qty') && $request->input('picked_qty') !== null) 
                            || (isset($raw['picked_qty']) && $raw['picked_qty'] !== null);

                        $isPickedQtyChanged = $hasPickedQtyParam && (abs((float)$orderItem->picked_qty - (float)$pickedQty) > 0.0001);
                        $isPickedQtyUpdate = !$isQcUpdate && $isPickedQtyChanged;

                        if ($isPickedQtyUpdate && $order instanceof \App\Models\Order && in_array($order->status, \App\Http\Controllers\OrderController::EXPORTED_STOCK_STATUSES, true)) {
                            return response()->json([
                                'success' => false,
                                'message' => "Đơn hàng {$order->order_code} đã kiểm hàng & trừ tồn kho ({$order->status}), không thể sửa số lượng bốc!"
                            ], 422);
                        }

                        if ($isPickedQtyUpdate && empty($extraData['quickPickAll'])) {
                            $authUser = auth()->user();
                            $performer = $authUser?->name ?? $performerName;
                            $curName = mb_strtolower(trim($performer));
                            $assignedName = mb_strtolower(trim($orderItem->assigned_staff ?? ''));
                            $assignedList = array_map(fn($s) => mb_strtolower(trim($s)), (array)($orderItem->assigned_staff_list ?? []));
                            $isSuper = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->id === 1);
                            $adminOverride = !empty($raw['adminOverride']) || !empty($extraData['adminOverride']);

                            // Nếu mặt hàng CHƯA ĐƯỢC PHÂN CÔNG (hoặc là null):
                            // Tự động cho phép và ăn theo người bấm Đủ / bốc hàng!
                            $isUnassigned = ($assignedName === '' || $assignedName === 'null');

                            $isAllowed = $isUnassigned
                                || ($assignedName === $curName) 
                                || in_array($curName, $assignedList) 
                                || ($isSuper && $adminOverride);

                            if (!$isAllowed) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Mặt hàng này đã được giao cho ' . $orderItem->assigned_staff . '! Bạn không được phép sửa số lượng bốc của người khác.'
                                ], 403);
                            }

                            // Nếu chưa phân công mà bốc hàng, tự động gán luôn cho người thao tác
                            if ($isUnassigned && !empty($performer)) {
                                $orderItem->assigned_staff = $performer;
                                $orderItem->assigned_staff_list = [$performer];
                                $assignedStaff = $performer;
                                $assignedStaffList = [$performer];
                                $event->assigned_staff = $performer;
                                $event->assigned_staff_list = [$performer];
                                $event->save();
                            }
                        }

                        if ($isPickedQtyUpdate) {
                            if ($reqQtyTotal > 0 && $pickedQty > $reqQtyTotal) {
                                $pickedQty = $reqQtyTotal;
                            }
                            $orderItem->picked_qty = $pickedQty;
                            $orderItem->shortage_reason = null;
                            if ($reqQtyTotal > 0 && $pickedQty >= $reqQtyTotal) {
                                $orderItem->is_picked = true;
                                $orderItem->item_status = 'Đủ hàng';
                            } else {
                                $orderItem->is_picked = false;
                                $orderItem->item_status = $pickedQty > 0 ? 'Đang bốc' : 'Chờ bốc';
                            }
                        }
                        if ($itemStatus) {
                            $resolvedStatus = ($itemStatus === 'Thiếu hàng')
                                ? ($orderItem->picked_qty > 0 ? 'Đang bốc' : 'Chờ bốc')
                                : $itemStatus;
                            $orderItem->item_status = $resolvedStatus;
                            $orderItem->shortage_reason = null;
                            if ($resolvedStatus === 'Đủ hàng' || ($reqQtyTotal > 0 && $orderItem->picked_qty >= $reqQtyTotal)) {
                                $orderItem->is_picked = true;
                                $orderItem->item_status = 'Đủ hàng';
                            }
                        }
                        if ($shortageReason !== null) {
                            $orderItem->shortage_reason = null;
                        }
                        if ($assignedStaff !== null) {
                            $authUser = auth()->user();
                            $targetStaff = trim((string)$assignedStaff);
                            $curUserName = trim((string)($authUser?->name ?? ''));
                            $isSelfAssign = $curUserName !== '' && mb_strtolower($targetStaff) === mb_strtolower($curUserName);
                            $isCanceling = $targetStaff === '';
                            $wasAssignedToCurUser = $curUserName !== '' && mb_strtolower(trim((string)$orderItem->assigned_staff)) === mb_strtolower($curUserName);

                            $canAssignOthers = $this->canUserAssignItem($authUser, $orderItem);

                            if (!$canAssignOthers) {
                                if ($isSelfAssign) {
                                    if (!empty($orderItem->assigned_staff) && !$wasAssignedToCurUser) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Mặt hàng này đã được giao cho ' . $orderItem->assigned_staff . ', bạn không thể tự nhận!'
                                        ], 403);
                                    }
                                } elseif ($isCanceling) {
                                    if (!$wasAssignedToCurUser && !empty($orderItem->assigned_staff)) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => 'Bạn chỉ có thể hủy nhận mặt hàng do chính mình nhận!'
                                        ], 403);
                                    }
                                } else {
                                    return response()->json([
                                        'success' => false,
                                        'message' => 'Chỉ trưởng nhóm mới có quyền phân công người khác soạn hàng! Thành viên có thể tự nhận nếu sản phẩm chưa được giao cho ai.'
                                    ], 403);
                                }
                            }

                            if ($targetStaff !== '') {
                                $assignedUser = \App\Models\User::where('name', $targetStaff)->first();
                                if ($assignedUser) {
                                    $hasPickRight = $assignedUser->id === 1 
                                        || $assignedUser->hasRole('admin') 
                                        || $assignedUser->hasRole('Admin') 
                                        || $assignedUser->hasRole('super-admin')
                                        || $assignedUser->hasRole('warehouse')
                                        || $assignedUser->hasPermission('inv_picking.pick') 
                                        || $assignedUser->hasPermission('inv_picking.assign');

                                    if (!$hasPickRight) {
                                        return response()->json([
                                            'success' => false,
                                            'message' => "Nhân viên {$targetStaff} không có quyền soạn bốc hàng!"
                                        ], 422);
                                    }

                                    $itemCatId = (int)($orderItem->category_id ?? $orderItem->product?->category_id);
                                    if ($itemCatId > 0) {
                                        $catGroups = \App\Models\StaffGroup::where('status', 'active')->get()->filter(fn($g) => $g->hasCategory($itemCatId));
                                        if ($catGroups->isNotEmpty()) {
                                            $isInGroup = $catGroups->contains(fn($g) => (int)$g->user_id === (int)$assignedUser->id || $g->hasMember($assignedUser->id));
                                            if (!$isInGroup && $assignedUser->id !== 1 && !$assignedUser->hasRole('admin') && !$assignedUser->hasRole('super-admin')) {
                                                $grpNames = $catGroups->pluck('name')->implode(', ');
                                                return response()->json([
                                                    'success' => false,
                                                    'message' => "Nhân viên {$targetStaff} không thuộc nhóm phụ trách loại sản phẩm này ({$grpNames})!"
                                                ], 422);
                                            }
                                        }
                                    }
                                }
                            }

                            $orderItem->assigned_staff = $targetStaff ?: null;
                        }
                        if ($assignedStaffList !== null) {
                            $orderItem->assigned_staff_list = is_array($assignedStaffList) ? $assignedStaffList : [$assignedStaffList];
                        } elseif (!empty($extraData['isAddCoPicker']) && $assignedStaff) {
                            $existing = $orderItem->assigned_staff_list ?: ($orderItem->assigned_staff ? [$orderItem->assigned_staff] : []);
                            if (!is_array($existing)) $existing = [$existing];
                            if (!in_array($assignedStaff, $existing)) {
                                $existing[] = $assignedStaff;
                            }
                            $orderItem->assigned_staff_list = array_values(array_filter($existing));
                            $orderItem->assigned_staff = $orderItem->assigned_staff_list[0] ?? $assignedStaff;
                        }

                        if (is_array($extraData)) {
                            if (isset($extraData['isChecked'])) {
                                $orderItem->is_checked = (bool)$extraData['isChecked'];
                            }
                            if (isset($extraData['checkedQty'])) $orderItem->checked_qty = (float)$extraData['checkedQty'];
                            if (isset($extraData['checkedBy'])) $orderItem->checked_by = $extraData['checkedBy'];
                            if (isset($extraData['checkedAt'])) $orderItem->checked_at = $extraData['checkedAt'];
                            if (isset($extraData['checkNotes'])) $orderItem->check_notes = $extraData['checkNotes'];
                        }
                        $orderItem->save();
                    }
                }

                // Xác định quyền xem ngành hàng của user để giới hạn thao tác hàng loạt
                $authUser = auth()->user();
                $isSuperUser = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('Admin') || $authUser->hasRole('super-admin') || $authUser->id === 1 || in_array($authUser->department ?? '', ['Ban Giám Đốc', 'IT', 'Quản Lý', 'Executive']));
                $canViewAllCategories = $isSuperUser || ($authUser && $authUser->hasPermission('inv_picking.view_all_categories'));
                
                $userCategoryIds = [];
                if (!$canViewAllCategories && $authUser) {
                    $uGroups = \App\Models\StaffGroup::where('status', 'active')->get()->filter(fn($g) => (int)$g->user_id === (int)$authUser->id || $g->hasMember($authUser->id));
                    $userCategoryIds = $uGroups->pluck('category_ids')->flatten()->filter()->map(fn($id) => (int)$id)->unique()->toArray();
                }

                // Xử lý các thao tác hàng loạt (Tự nhận tất cả, Bốc đủ tất cả, QC toàn bộ)
                if (!empty($extraData['quickPickAll'])) {
                    foreach ($order->items as $it) {
                        if (!$canViewAllCategories && !empty($userCategoryIds)) {
                            $itCatId = (int)($it->category_id ?? $it->product?->category_id);
                            if (!in_array($itCatId, $userCategoryIds)) {
                                continue;
                            }
                        }
                        $itReqTotal = (float) ($it->quantity + ($it->gift_quantity ?? 0));
                        $it->picked_qty = $itReqTotal;
                        $it->is_picked = true;
                        $it->item_status = 'Đủ hàng';
                        $it->shortage_reason = null;
                        if ($assignedStaff) $it->assigned_staff = $assignedStaff;
                        if ($assignedStaffList) $it->assigned_staff_list = is_array($assignedStaffList) ? $assignedStaffList : [$assignedStaffList];
                        $it->save();
                    }
                } elseif (!empty($extraData['items']) && is_array($extraData['items'])) {
                    foreach ($extraData['items'] as $bItem) {
                        $bId = $bItem['id'] ?? null;
                        $bSku = $bItem['sku'] ?? null;
                        $targetIt = null;
                        if ($bId && is_numeric($bId)) {
                            $targetIt = $order->items()->where('id', $bId)->first();
                        }
                        if (!$targetIt && $bSku) {
                            $targetIt = $order->items()->where('sku', $bSku)->first();
                        }
                        if ($targetIt) {
                            if (isset($bItem['assignedStaff'])) {
                                $targetIt->assigned_staff = $bItem['assignedStaff'];
                            }
                            if (isset($bItem['assignedStaffList'])) {
                                $targetIt->assigned_staff_list = is_array($bItem['assignedStaffList']) ? $bItem['assignedStaffList'] : [$bItem['assignedStaffList']];
                            }
                            $targetIt->save();
                        }
                    }
                } elseif (!empty($extraData['isAddCoPicker']) && $assignedStaff) {
                    foreach ($order->items as $it) {
                        $existing = $it->assigned_staff_list ?: ($it->assigned_staff ? [$it->assigned_staff] : []);
                        if (!is_array($existing)) $existing = [$existing];
                        if (!in_array($assignedStaff, $existing)) {
                            $existing[] = $assignedStaff;
                        }
                        $it->assigned_staff_list = array_values(array_filter($existing));
                        $it->assigned_staff = $it->assigned_staff_list[0] ?? $assignedStaff;
                        $it->save();
                    }
                } elseif (!empty($extraData['clearAllAssignments'])) {
                    foreach ($order->items as $it) {
                        $itReq = (float) ($it->quantity + ($it->gift_quantity ?? 0));
                        $isLocked = $it->is_picked || ($it->picked_qty >= $itReq && $itReq > 0) || $it->item_status === 'Đủ hàng' || $it->is_checked;
                        if (!$isLocked) {
                            $it->assigned_staff = null;
                            $it->assigned_staff_list = null;
                            $it->save();
                        }
                    }
                } elseif (!empty($extraData['assignAllStaff']) || ($eventType === 'STAFF_ASSIGN' && !$itemId && !$sku)) {
                    if (!$this->canUserAssignItem($authUser, null)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Chỉ trưởng nhóm mới có quyền phân công hàng loạt!'
                        ], 403);
                    }
                    foreach ($order->items as $it) {
                        if ($assignedStaff) $it->assigned_staff = $assignedStaff;
                        if ($assignedStaffList) $it->assigned_staff_list = is_array($assignedStaffList) ? $assignedStaffList : [$assignedStaffList];
                        $it->save();
                    }
                } elseif (!empty($extraData['quickPickCategory']) && isset($extraData['categoryId'])) {
                    $catId = (int)$extraData['categoryId'];
                    foreach ($order->items as $it) {
                        $itCatId = (int)($it->category_id ?? $it->product?->category_id);
                        if ($itCatId !== $catId) {
                            continue;
                        }
                        $itReqTotal = (float) ($it->quantity + ($it->gift_quantity ?? 0));
                        $it->picked_qty = $itReqTotal;
                        $it->is_picked = true;
                        $it->item_status = 'Đủ hàng';
                        $it->shortage_reason = null;
                        $it->save();
                    }
                } elseif (!empty($extraData['qcCategoryChecked']) && isset($extraData['categoryId'])) {
                    $catId = (int)$extraData['categoryId'];
                    foreach ($order->items as $it) {
                        $itCatId = (int)($it->category_id ?? $it->product?->category_id);
                        if ($itCatId !== $catId) {
                            continue;
                        }
                        $it->is_checked = true;
                        $it->checked_qty = (float)($it->picked_qty ?? 0);
                        $it->checked_by = $performerName;
                        $it->checked_at = now()->format('d/m/Y H:i');
                        $it->save();
                    }
                } elseif (!empty($extraData['qcAllChecked'])) {
                    foreach ($order->items as $it) {
                        if (!$canViewAllCategories && !empty($userCategoryIds)) {
                            $itCatId = (int)($it->category_id ?? $it->product?->category_id);
                            if (!in_array($itCatId, $userCategoryIds)) {
                                continue;
                            }
                        }
                        $it->is_checked = true;
                        $it->checked_qty = (float)($it->picked_qty ?? 0);
                        $it->checked_by = $performerName;
                        $it->checked_at = now()->format('d/m/Y H:i');
                        $it->save();
                    }
                }

                // ĐÁNH GIÁ TIẾN ĐỘ TỪNG NHÓM VÀ TOÀN BỘ ĐƠN HÀNG (KHÔNG tự nhảy sang Đã bốc xong / Đã kiểm hàng nếu chỉ Lưu hoặc Phân công)
                $order->load(['items.product.category']);
                $groupProgress = self::evaluateOrderGroupProgress($order);
                $isSaveOnly = !empty($extraData['saveOnly']) || !empty($raw['saveOnly']);
                $isStaffAssignOnly = ($eventType === 'STAFF_ASSIGN');
                $alreadyTransferred = in_array($order->status, \App\Models\Order::PICKING_TRANSFERRED_STATUSES, true);
                $oldOrderStatus = $order->status;

                if (!$isStaffAssignOnly && !$isSaveOnly && !in_array($order->status, ['Đang vận chuyển', 'Đã giao', 'Giao thành công'])) {
                    if ($alreadyTransferred) {
                        // Nếu đơn đã chuyển kiểm thì dù bốc thiếu vẫn cho phép hoàn tất kiểm -> Đã kiểm hàng
                        if (!empty($extraData['qcAllChecked'])) {
                            $order->status = 'Đã kiểm hàng';
                        }
                    } elseif ($order->items->contains(fn($it) => (bool)$it->is_picked || (float)$it->picked_qty > 0)) {
                        $order->status = 'Đang bốc hàng';
                    }
                }
                $order->save();

                // Nếu là đơn hàng ôm đã được Kế toán duyệt và kho kiểm hàng/giao xe xong -> Chuyển vào kho hàng ôm NV & trừ kho công ty
                if ($order instanceof \App\Models\ConsignmentOrder && in_array($order->status, ['Đã kiểm hàng', 'Kho đã xuất đủ', 'in_progress']) && !$order->stock_deducted && $order->approved_at) {
                    $order->transferStockToEmployeeHoldingWarehouse(auth()->id(), $performerName);
                } elseif ($order instanceof \App\Models\Order && in_array($order->status, \App\Http\Controllers\OrderController::EXPORTED_STOCK_STATUSES, true) && !$order->stock_deducted) {
                    app(\App\Http\Controllers\OrderController::class)->syncStockForStatus($order, $oldOrderStatus, true);
                }

                // Cập nhật lại event để phát sóng realtime chính xác
                if ($event) {
                    $event->order_status = $order->status;
                    $evExtra = $event->extra_data ?: [];
                    $evExtra['groupProgress'] = $groupProgress;
                    $evExtra['orderStatus'] = $order->status;
                    $event->extra_data = $evExtra;
                    $event->save();
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('PickingSyncController DB persist failed: ' . $e->getMessage());
        }

        // Tự động dọn dẹp các sự kiện cũ hơn 48 giờ
        try {
            PickingSyncEvent::where('created_at', '<', now()->subHours(48))->delete();
        } catch (\Throwable $e) {
            // Không ngắt luồng nếu cleanup gặp sự cố
        }

        return response()->json([
            'success'       => true,
            'orderStatus'   => $order->status ?? null,
            'groupProgress' => $groupProgress ?? null,
            'allPicked'     => $groupProgress['all_groups_picking_completed'] ?? false,
            'allChecked'    => $groupProgress['all_groups_qc_completed'] ?? false,
            'event'         => [
                'id'                => $event->id,
                'orderId'           => $event->order_id,
                'itemId'            => $event->item_id,
                'sku'               => $event->sku,
                'pickedQty'         => $event->picked_qty,
                'reqQty'            => $event->req_qty,
                'itemStatus'        => $event->item_status,
                'shortageReason'    => $event->shortage_reason,
                'performerName'     => $event->performer_name,
                'eventType'         => $event->event_type,
                'assignedStaff'     => $event->assigned_staff,
                'assignedStaffList' => $event->assigned_staff_list,
                'orderStatus'       => $order->status ?? $event->order_status,
                'shortageReported'  => $event->shortage_reported,
                'extraData'         => $event->extra_data,
                'timestamp'         => $event->created_at->timestamp,
            ]
        ]);
    }

    /**
     * API: Cơ chế báo thiếu hàng đã được gỡ bỏ
     */
    public function reportShortage(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Cơ chế báo thiếu hàng đã được gỡ bỏ.',
        ], 410);
    }

    /**
     * API Non-blocking: Lấy danh sách các sự kiện mới nhất kể từ since_id
     * Chạy siêu nhanh (1-2ms), giải phóng ngay worker, không làm đơ PHP CLI server
     */
    public function getEvents(Request $request)
    {
        $sinceId = (int) $request->query('since_id', 0);

        $events = PickingSyncEvent::where('id', '>', $sinceId)
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        $latestId = $events->isNotEmpty() ? $events->last()->id : $sinceId;

        $formatted = $events->map(function ($ev) {
            return [
                'id'                => $ev->id,
                'orderId'           => $ev->order_id,
                'itemId'            => $ev->item_id,
                'sku'               => $ev->sku,
                'pickedQty'         => (float) $ev->picked_qty,
                'reqQty'            => $ev->req_qty !== null ? (float) $ev->req_qty : null,
                'itemStatus'        => $ev->item_status,
                'shortageReason'    => $ev->shortage_reason,
                'performerName'     => $ev->performer_name,
                'eventType'         => $ev->event_type ?? 'ITEM_UPDATE',
                'assignedStaff'     => $ev->assigned_staff,
                'assignedStaffList' => $ev->assigned_staff_list,
                'orderStatus'       => $ev->order_status,
                'shortageReported'  => (bool) $ev->shortage_reported,
                'extraData'         => $ev->extra_data,
                'timestamp'         => $ev->created_at ? $ev->created_at->timestamp : time(),
            ];
        });

        return response()->json([
            'success'   => true,
            'latest_id' => $latestId,
            'events'    => $formatted
        ]);
    }

    /**
     * API: Lấy trạng thái tổng hợp đồng bộ của các đơn bốc trong ngày
     * Giúp tài khoản mới vào hoặc F5 trang nạp được ngay số lượng đã bốc và người được phân công
     */
    public function getLatestState(Request $request)
    {
        $events = PickingSyncEvent::where('created_at', '>=', now()->subHours(24))
            ->whereIn('event_type', ['ITEM_UPDATE', 'STAFF_ASSIGN'])
            ->orderBy('id', 'asc')
            ->get();

        $maxId = $events->isNotEmpty() ? $events->last()->id : 0;

        $formatted = $events->map(function ($ev) {
            return [
                'id'                => $ev->id,
                'orderId'           => $ev->order_id,
                'itemId'            => $ev->item_id,
                'sku'               => $ev->sku,
                'pickedQty'         => (float) $ev->picked_qty,
                'reqQty'            => $ev->req_qty !== null ? (float) $ev->req_qty : null,
                'itemStatus'        => $ev->item_status,
                'shortageReason'    => $ev->shortage_reason,
                'performerName'     => $ev->performer_name,
                'eventType'         => $ev->event_type ?? 'ITEM_UPDATE',
                'assignedStaff'     => $ev->assigned_staff,
                'assignedStaffList' => $ev->assigned_staff_list,
                'orderStatus'       => $ev->order_status,
                'shortageReported'  => (bool) $ev->shortage_reported,
                'extraData'         => $ev->extra_data,
                'timestamp'         => $ev->created_at ? $ev->created_at->timestamp : time(),
            ];
        });

        return response()->json([
            'success'   => true,
            'latest_id' => $maxId,
            'events'    => $formatted
        ]);
    }

    /**
     * Stream dự phòng (trả về ngay sau 1 lần đọc để không block PHP CLI worker trên Windows)
     */
    public function stream(Request $request): StreamedResponse
    {
        $lastEventId = (int) ($request->header('Last-Event-ID') 
            ?? $request->query('last_event_id') 
            ?? $request->query('last_id') 
            ?? 0);

        if ($lastEventId === 0) {
            $maxId = (int) PickingSyncEvent::max('id');
            $lastEventId = max(0, $maxId);
        }

        return new StreamedResponse(function () use ($lastEventId) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            echo "retry: 1500\n\n";

            $newEvents = PickingSyncEvent::where('id', '>', $lastEventId)
                ->orderBy('id', 'asc')
                ->limit(50)
                ->get();

            if ($newEvents->isNotEmpty()) {
                foreach ($newEvents as $ev) {
                    echo "id: {$ev->id}\n";
                    echo "event: PICKING_ITEM_UPDATED\n";
                    echo "data: " . json_encode([
                        'id'                => $ev->id,
                        'orderId'           => $ev->order_id,
                        'itemId'            => $ev->item_id,
                        'sku'               => $ev->sku,
                        'pickedQty'         => (float) $ev->picked_qty,
                        'reqQty'            => $ev->req_qty !== null ? (float) $ev->req_qty : null,
                        'itemStatus'        => $ev->item_status,
                        'shortageReason'    => $ev->shortage_reason,
                        'performerName'     => $ev->performer_name,
                        'eventType'         => $ev->event_type ?? 'ITEM_UPDATE',
                        'assignedStaff'     => $ev->assigned_staff,
                        'assignedStaffList' => $ev->assigned_staff_list,
                        'orderStatus'       => $ev->order_status,
                        'shortageReported'  => (bool) $ev->shortage_reported,
                        'extraData'         => $ev->extra_data,
                        'timestamp'         => $ev->created_at ? $ev->created_at->timestamp : time(),
                    ], JSON_UNESCAPED_UNICODE) . "\n\n";
                }
            } else {
                echo ": ping\n\n";
            }
            flush();
            // Trả về ngay lập tức, không dùng vòng lặp vô tận để giải phóng PHP worker 100%
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store, must-revalidate',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    public function pickingView(Request $request)
    {
        // Cho phép các trạng thái liên quan đến soạn hàng và kiểm hàng
        $allowedStatuses = [
            'Chốt đơn', 'Đã duyệt', 'Chờ bốc', 'Soạn bốc', 'Soạn hàng', 'Kho đang soạn', 'Đang bốc hàng',
            'Kho báo thiếu hàng', 'Đã bốc xong', 'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng',
            'Sẵn sàng xuất xe', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Kho đã xuất đủ'
        ];

        if ($reqOrderId = $request->query('order_id')) {
            $orderToPick = \App\Models\Order::find($reqOrderId);
            if (!$orderToPick && is_numeric($reqOrderId) && (int)$reqOrderId >= 900000) {
                $orderToPick = \App\Models\ConsignmentOrder::find((int)$reqOrderId - 900000);
            }
            if (!$orderToPick) {
                $orderToPick = \App\Models\ConsignmentOrder::where('code', $reqOrderId)->first();
            }
            if ($orderToPick) {
                if (!empty($orderToPick->delivery_date)) {
                    $orderToPick->load('items.product');
                    $itemsData = $orderToPick->items->map(fn($it) => [
                        'product_id'    => $it->product_id,
                        'sku'           => $it->sku,
                        'quantity'      => (float)$it->quantity,
                        'gift_quantity' => (float)($it->gift_quantity ?? 0),
                        'unit'          => $it->unit,
                    ])->toArray();
                    $targetWh = $orderToPick->warehouse ?: (\App\Models\Warehouse::first()?->name ?? 'Kho Tổng');
                    $check = \App\Services\InventoryAvailabilityService::checkItemsAvailability($targetWh, $itemsData, $orderToPick->id);

                    if ($check['is_valid']) {
                        if (in_array($orderToPick->status, ['Chốt đơn', 'Đã duyệt', 'pending', 'approved'])) {
                            $orderToPick->status = 'Soạn hàng';
                        }
                        if ($request->filled('picking_date')) {
                            $orderToPick->picking_date = $request->query('picking_date');
                        } elseif (empty($orderToPick->picking_date)) {
                            $orderToPick->picking_date = now()->format('Y-m-d');
                        }
                        $orderToPick->has_shortage_warning = false;
                        $orderToPick->save();
                    } else {
                        $orderToPick->has_shortage_warning = true;
                        $orderToPick->save();
                    }
                }
            }
        }

        // Bốc hàng & Kiểm hàng: Lấy đơn hàng bán thông thường
        $orders = \App\Models\Order::with(['items.product.category', 'dealer', 'creator', 'salesRep'])
            ->whereIn('status', $allowedStatuses)
            ->where(function($q) {
                $q->whereNotNull('delivery_date')
                  ->orWhereNotNull('picking_date')
                  ->orWhereIn('status', ['Soạn hàng', 'Soạn bốc', 'Kho đang soạn', 'Đang bốc hàng', 'Đã bốc xong', 'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Kho báo thiếu hàng']);
            })
            ->latest('id')
            ->get();

        foreach ($orders as $ord) {
            $ord->picking_date = $ord->picking_date ?: ($ord->delivery_date ?: ($ord->created_at ? $ord->created_at->format('Y-m-d') : now()->format('Y-m-d')));
            $ord->delivery_date = $ord->delivery_date ?: ($ord->picking_date ?: ($ord->created_at ? $ord->created_at->format('Y-m-d') : now()->format('Y-m-d')));
            $ord->is_consignment = false;
        }

        // Bổ sung: Lấy cả đơn hàng ôm (ConsignmentOrder) phục vụ xuất kho cho NVKD (CHỈ LẤY CÁC ĐƠN ĐÃ ĐƯỢC DUYỆT)
        $consignmentOrders = \App\Models\ConsignmentOrder::with(['items.product.category', 'staff', 'warehouse'])
            ->whereIn('status', ['approved', 'in_progress', 'Chờ bốc', 'Đang bốc hàng', 'Kho báo thiếu hàng', 'Đã bốc xong', 'Kiểm hàng', 'Đang kiểm hàng', 'Đã kiểm hàng', 'Sẵn sàng xuất xe', 'Kho đã xuất đủ'])
            ->latest('id')
            ->get();

        foreach ($consignmentOrders as $co) {
            $co->raw_id = $co->id;
            $co->id = 900000 + $co->id;
            $co->order_code = $co->code;
            $co->customer_name = 'Hàng Ôm - ' . ($co->staff_name ?: ($co->staff?->name ?: 'NVKD'));
            $co->customer_phone = $co->staff?->phone ?? '---';
            $co->customer_address = $co->warehouse_name ?: 'Kho xuất công ty';
            
            $whName = $co->warehouse_name ?: ($co->warehouse?->name ?: (\App\Models\Warehouse::first()?->name ?? 'Kho Tổng'));
            $co->unsetRelation('warehouse');
            $co->warehouse = $whName;

            $co->is_consignment = true;
            $co->order_type = 'Hàng ôm';
            $co->order_creator = $co->staff_name ?: ($co->staff?->name ?: 'Hệ thống');
            $co->supervisor = $co->staff_name ?: ($co->staff?->name ?: 'Hệ thống');
            $co->picking_date = $co->picking_date ?: ($co->created_at ? $co->created_at->format('Y-m-d') : now()->format('Y-m-d'));
            $co->delivery_date = $co->delivery_date ?: ($co->created_at ? $co->created_at->format('Y-m-d') : now()->format('Y-m-d'));

            // Map trạng thái đơn ôm sang quy trình soạn bốc kho
            if ($co->status === 'approved') {
                $co->status = 'Chờ bốc';
            } elseif ($co->status === 'in_progress') {
                $allPicked = $co->items->isNotEmpty() && $co->items->every(fn($it) => (float)($it->picked_qty ?? 0) >= (float)$it->quantity);
                $anyPicked = $co->items->contains(fn($it) => (float)($it->picked_qty ?? 0) > 0);
                if ($allPicked) {
                    $co->status = 'Đã bốc xong';
                } elseif ($anyPicked) {
                    $co->status = 'Đang bốc hàng';
                } else {
                    $co->status = 'Chờ bốc';
                }
            }

            foreach ($co->items as $it) {
                $it->order_id = $co->id;
                $it->gift_quantity = 0;
                $it->product_name = $it->name;
                $it->picked_qty = (float)($it->picked_qty ?? 0);
                $it->checked_qty = (float)($it->checked_qty ?? 0);
                $it->item_status = $it->item_status ?: ($it->picked_qty >= $it->quantity && $it->quantity > 0 ? 'Đủ hàng' : 'Chờ bốc');
                $it->is_picked = (bool)($it->is_picked || ($it->picked_qty >= $it->quantity && $it->quantity > 0));
                $it->is_checked = (bool)($it->is_checked || ($it->checked_qty >= $it->quantity && $it->quantity > 0));
                $it->category_id = (int)($it->category_id ?: ($it->product?->category_id ?? 0));
                $it->category_name = $it->category_name ?: ($it->product?->category?->name ?? 'Mặt hàng chung');
            }

            $orders->push($co);
        }

        $allActiveGroups = \App\Models\StaffGroup::where('status', 'active')->with(['leader'])->get();
        $allCategories = \App\Models\Category::all(['id', 'code', 'name']);

        foreach ($orders as $ord) {
            $ord->supervisor = $ord->order_creator 
                ?: ($ord->salesRep?->name 
                ?: ($ord->dealer?->staff_in_charge 
                ?: ($ord->creator?->name ?: 'Hệ thống')));

            // Tự động chuẩn hóa các đơn/mặt hàng cũ còn vướng cờ báo thiếu
            foreach ($ord->items as $it) {
                $req = (float) ($it->quantity + ($it->gift_quantity ?? 0));
                $picked = (float) ($it->picked_qty ?? 0);
                if ($it->item_status === 'Thiếu hàng' || !empty($it->shortage_reason)) {
                    $it->item_status = ($req > 0 && $picked >= $req) ? 'Đủ hàng' : ($picked > 0 ? 'Đang bốc' : 'Chờ bốc');
                    $it->shortage_reason = null;
                    $it->is_picked = ($req > 0 && $picked >= $req);
                    $it->save();
                }
            }

            if ($ord->status === 'Kho báo thiếu hàng' || $ord->shortage_reported) {
                if ($ord->status === 'Kho báo thiếu hàng') {
                    $anyPicked = $ord->items->contains(fn($x) => (float)($x->picked_qty ?? 0) > 0);
                    $ord->status = $anyPicked ? 'Đang bốc hàng' : 'Soạn hàng';
                }
                $ord->shortage_reported = false;
                $ord->shortage_reported_at = null;
                $ord->shortage_reported_note = null;
                $ord->save();
            }

            // Đánh giá tiến độ từng nhóm bốc hàng trên TOÀN BỘ mặt hàng của đơn
            $evalProgress = self::evaluateOrderGroupProgress($ord, $allActiveGroups, $allCategories);
            $ord->groups_progress = $evalProgress['groups'];
            $ord->group_summary = $evalProgress;
        }

        // Gắn thông tin cảnh báo sửa đơn gần đây (trong 24 giờ) vào các đơn hàng để khi F5 / mở lại trang vẫn hiển thị cảnh báo
        $recentModEvents = \App\Models\PickingSyncEvent::where('event_type', 'ORDER_ITEMS_MODIFIED')
            ->where('created_at', '>=', now()->subHours(24))
            ->latest('id')
            ->get();

        $processedOrders = [];
        foreach ($recentModEvents as $ev) {
            $ordId = (int) $ev->order_id;
            if (isset($processedOrders[$ordId])) continue;
            $processedOrders[$ordId] = true;

            $matchedOrd = $orders->firstWhere('id', $ordId);
            if ($matchedOrd) {
                $extra = $ev->extra_data ?? [];
                $matchedOrd->_hasItemsModified = true;
                $matchedOrd->_modifiedAlert = [
                    'orderId'    => $ordId,
                    'orderCode'  => $matchedOrd->order_code,
                    'time'       => $extra['modifiedAt'] ?? ($ev->created_at ? $ev->created_at->format('H:i:s d/m/Y') : ''),
                    'performer'  => $ev->performer_name ?? ($extra['performer'] ?? 'Kinh doanh'),
                    'pickers'    => $ev->assigned_staff ?: (!empty($extra['assignedPickers']) ? implode(', ', $extra['assignedPickers']) : 'Chưa phân công'),
                    'supervisor' => $extra['supervisor'] ?? ($extra['orderCreator'] ?? 'Admin Kho'),
                    'changes'    => $extra['changes'] ?? [],
                ];

                $changedSkus = collect($extra['changes'] ?? [])->pluck('sku')->toArray();
                foreach ($matchedOrd->items as $it) {
                    if (empty($changedSkus) || in_array($it->sku, $changedSkus)) {
                        $it->_justModified = true;
                        $it->_forceUnlock = true;
                    }
                }
            }
        }

        $warehouses = \App\Models\Warehouse::all();
        $branches = \App\Models\Branch::all();
        $currentUser = auth()->user() ?? \App\Models\User::first();

        // 1. Phân quyền xem bốc xếp không phân loại sản phẩm
        $isSuperUser = $currentUser->hasRole('admin')
            || $currentUser->hasRole('Admin')
            || $currentUser->hasRole('super-admin')
            || $currentUser->id === 1
            || in_array($currentUser->department ?? '', ['Ban Giám Đốc', 'IT', 'Quản Lý', 'Executive']);

        $canViewAllCategories = $isSuperUser || $currentUser->hasPermission('inv_picking.view_all_categories');

        // 2. Lọc chỉ những nhân viên có quyền soạn bốc hàng (loại trừ kế toán, sales, v.v.)
        $pickingStaff = \App\Models\User::with(['roles', 'userRolePermissions.permission'])
            ->select('id', 'name', 'email', 'department')
            ->orderBy('name')
            ->get()
            ->filter(function ($u) {
                return (int)$u->id === 1
                    || $u->hasRole('admin')
                    || $u->hasRole('Admin')
                    || $u->hasRole('super-admin')
                    || $u->hasRole('warehouse')
                    || $u->hasPermission('inv_picking.pick')
                    || $u->hasPermission('inv_picking.assign');
            })
            ->values();

        // 3. Tìm các nhóm nhân sự mà user này tham gia (đang active)
        $leaderGroups = $allActiveGroups->filter(fn($g) => (int)$g->user_id === (int)$currentUser->id);
        $memberGroups = $allActiveGroups->filter(fn($g) => $g->hasMember($currentUser->id));
        $userGroups = $leaderGroups->merge($memberGroups)->unique('id');
        $allCategoryIds = $allCategories->pluck('id')->map(fn($id) => (int)$id)->toArray();

        $userCategoryIds = $userGroups->pluck('category_ids')
            ->flatten()
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values()
            ->toArray();

        $leaderCategoryIds = array_values($leaderGroups->pluck('category_ids')
            ->flatten()
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->toArray());

        if ($canViewAllCategories) {
            $leaderCategoryIds = array_values(array_unique(array_merge($leaderCategoryIds, $allCategoryIds)));
            $userCategoryIds = array_values(array_unique($allCategoryIds));
        }

        // 4. Xây dựng bản đồ nhân sự theo nhóm phụ trách từng loại sản phẩm (chỉ gồm người có quyền soạn bốc)
        $categoryStaffMap = [];
        foreach ($allCategories as $cat) {
            $catId = (int)$cat->id;
            $catGroups = $allActiveGroups->filter(fn($g) => $g->hasCategory($catId));
            if ($catGroups->isNotEmpty()) {
                $eligibleUserIds = [];
                foreach ($catGroups as $cg) {
                    if ($cg->user_id) {
                        $eligibleUserIds[] = (int)$cg->user_id;
                    }
                    if (!empty($cg->user_ids) && is_array($cg->user_ids)) {
                        foreach ($cg->user_ids as $uid) {
                            $eligibleUserIds[] = (int)$uid;
                        }
                    }
                }
                $eligibleUserIds = array_unique(array_filter($eligibleUserIds));
                $eligibleStaff = $pickingStaff->whereIn('id', $eligibleUserIds)->values();
                $categoryStaffMap[$catId] = $eligibleStaff->isNotEmpty() ? $eligibleStaff : $pickingStaff;
            } else {
                $categoryStaffMap[$catId] = $pickingStaff;
            }
        }
        $categoryStaffMap['default'] = $pickingStaff;

        // Lấy danh sách thành viên trong các nhóm của user để phân công
        if ($canViewAllCategories) {
            $groupStaff = $pickingStaff;
        } else {
            $groupMemberIds = $userGroups->pluck('user_ids')->flatten()->filter()->map(fn($id) => (int)$id)->unique()->values()->toArray();
            $leaderIds = $userGroups->pluck('user_id')->filter()->map(fn($id) => (int)$id)->all();
            $allMemberIds = array_unique(array_merge($groupMemberIds, $leaderIds));
            $groupStaff = $pickingStaff->whereIn('id', $allMemberIds)->values();
            if ($groupStaff->isEmpty()) {
                $groupStaff = $pickingStaff;
            }
        }
        $staff = $pickingStaff;

        // Nếu user KHÔNG có quyền xem tất cả loại sản phẩm: lọc chỉ giữ lại mặt hàng thuộc category_ids của nhóm user
        if (!$canViewAllCategories) {
            foreach ($orders as $ord) {
                $filteredItems = $ord->items->filter(function ($it) use ($userCategoryIds) {
                    $catId = (int)($it->category_id ?? $it->product?->category_id);
                    return in_array($catId, $userCategoryIds);
                })->values();
                $ord->setRelation('items', $filteredItems);
            }
            $orders = $orders->filter(function ($ord) {
                return $ord->items->isNotEmpty();
            })->values();
        }

        $userGroupsData = $userGroups->map(function ($g) use ($currentUser) {
            return [
                'id' => $g->id,
                'name' => $g->name,
                'code' => $g->code,
                'is_leader' => (int)$g->user_id === (int)$currentUser->id,
                'category_ids' => $g->category_ids ?? [],
            ];
        })->values()->toArray();

        $discrepancies = \App\Models\DeliveryDiscrepancy::with(['order', 'product'])
            ->whereDate('created_at', '>=', now()->subDays(3))
            ->latest('id')
            ->get();

        return view('inventory.picking', compact(
            'orders', 
            'warehouses', 
            'branches', 
            'staff', 
            'currentUser',
            'canViewAllCategories',
            'userCategoryIds',
            'leaderCategoryIds',
            'allCategories',
            'userGroups',
            'userGroupsData',
            'groupStaff',
            'categoryStaffMap',
            'discrepancies'
        ));
    }

    /**
     * Kiểm tra user có quyền phân công người khác soạn mặt hàng này không
     */
    public function canUserAssignItem(?\App\Models\User $user, $item): bool
    {
        if (!$user) return true;

        if ($user->hasRole('admin') || $user->hasRole('Admin') || $user->hasRole('super-admin') || $user->id === 1) {
            return true;
        }
        if (isset($user->department) && in_array($user->department, ['Ban Giám Đốc', 'IT', 'Quản Lý', 'Executive'])) {
            return true;
        }
        if ($user->hasPermission('inv_picking.view_all_categories')) {
            return true;
        }

        $catId = $item?->category_id ?? $item?->product?->category_id;
        if ($catId) {
            return \App\Models\StaffGroup::where('status', 'active')
                ->where('user_id', $user->id)
                ->get()
                ->contains(fn($g) => $g->hasCategory($catId));
        }

        return \App\Models\StaffGroup::where('status', 'active')->where('user_id', $user->id)->exists();
    }

    /**
     * Đánh giá tiến độ bốc hàng và kiểm hàng theo từng nhóm nhân sự bốc hàng
     * Trạng thái đơn hàng CHỈ được coi là hoàn tất khi 100% các nhóm cùng hoàn thành!
     */
    public static function evaluateOrderGroupProgress($order, $activeGroups = null, $allCategories = null): array
    {
        if ($activeGroups === null) {
            $activeGroups = \App\Models\StaffGroup::where('status', 'active')->with(['leader'])->get();
        }
        if ($allCategories === null) {
            $allCategories = \App\Models\Category::all()->keyBy('id');
        }

        $items = $order->items;
        if (!$items || $items->isEmpty()) {
            return [
                'groups' => [],
                'all_groups_picking_completed' => true,
                'all_groups_qc_completed' => true,
                'has_shortage' => false,
                'pending_picking_group_names' => [],
                'pending_qc_group_names' => [],
                'total_groups' => 0,
                'completed_picking_groups' => 0,
                'completed_qc_groups' => 0,
            ];
        }

        $groupBuckets = [];

        foreach ($items as $it) {
            $catId = (int)($it->category_id ?? $it->product?->category_id);
            $matchedGroup = $activeGroups->first(function ($g) use ($catId) {
                return $catId && $g->hasCategory($catId);
            });

            $gKey = $matchedGroup ? (int)$matchedGroup->id : 0;
            if (!isset($groupBuckets[$gKey])) {
                $groupBuckets[$gKey] = [
                    'group_id' => $gKey,
                    'group_name' => $matchedGroup ? $matchedGroup->name : 'Nhóm Kho Chung',
                    'leader_name' => $matchedGroup?->leader?->name ?? 'Admin Kho',
                    'category_ids' => $matchedGroup ? ($matchedGroup->category_ids ?? []) : [],
                    'items_count' => 0,
                    'picked_count' => 0,
                    'checked_count' => 0,
                    'has_shortage' => false,
                    'shortage_items_count' => 0,
                ];
            }

            $req = (float)($it->quantity + ($it->gift_quantity ?? 0));
            $isPicked = ($req > 0 && (float)$it->picked_qty >= $req) || ((bool)$it->is_picked && (float)$it->picked_qty >= $req);
            $isChecked = (bool)$it->is_checked || ($req > 0 && (float)$it->checked_qty >= $req);

            $groupBuckets[$gKey]['items_count']++;
            if ($isPicked) {
                $groupBuckets[$gKey]['picked_count']++;
            }
            if ($isChecked) {
                $groupBuckets[$gKey]['checked_count']++;
            }
        }

        $groups = [];
        $allGroupsPickingCompleted = true;
        $allGroupsQcCompleted = true;
        $hasShortage = false;
        $pendingPickingGroups = [];
        $pendingQcGroups = [];
        $completedPickingCount = 0;
        $completedQcCount = 0;

        foreach ($groupBuckets as $gData) {
            $isPickingDone = $gData['items_count'] > 0 && $gData['picked_count'] >= $gData['items_count'] && !$gData['has_shortage'];
            $isQcDone = $gData['items_count'] > 0 && $gData['checked_count'] >= $gData['items_count'];

            $gData['is_picking_completed'] = $isPickingDone;
            $gData['is_qc_completed'] = $isQcDone;
            $gData['picking_progress_percent'] = $gData['items_count'] > 0 ? round(($gData['picked_count'] / $gData['items_count']) * 100) : 0;
            $gData['qc_progress_percent'] = $gData['items_count'] > 0 ? round(($gData['checked_count'] / $gData['items_count']) * 100) : 0;

            if ($gData['has_shortage']) {
                $hasShortage = true;
            }

            if (!$isPickingDone) {
                $allGroupsPickingCompleted = false;
                $pendingPickingGroups[] = $gData['group_name'] . ' (' . $gData['picked_count'] . '/' . $gData['items_count'] . ')';
            } else {
                $completedPickingCount++;
            }

            if (!$isQcDone) {
                $allGroupsQcCompleted = false;
                $pendingQcGroups[] = $gData['group_name'] . ' (' . $gData['checked_count'] . '/' . $gData['items_count'] . ')';
            } else {
                $completedQcCount++;
            }

            $groups[] = $gData;
        }

        // Kiểm tra bổ sung toàn bộ items để đảm bảo tính toàn vẹn 100%
        $overallItemsPicked = $items->every(function ($i) {
            $r = (float)($i->quantity + ($i->gift_quantity ?? 0));
            return $r > 0 && (float)$i->picked_qty >= $r;
        });
        $overallItemsChecked = $items->every(function ($i) {
            $r = (float)($i->quantity + ($i->gift_quantity ?? 0));
            return (bool)$i->is_checked || ($r > 0 && (float)$i->checked_qty >= $r);
        });

        $allGroupsPickingCompleted = $allGroupsPickingCompleted && $overallItemsPicked && !$hasShortage;
        $allGroupsQcCompleted = $allGroupsQcCompleted && $overallItemsChecked && $allGroupsPickingCompleted;

        return [
            'groups' => array_values($groups),
            'all_groups_picking_completed' => $allGroupsPickingCompleted,
            'all_groups_qc_completed' => $allGroupsQcCompleted,
            'has_shortage' => $hasShortage,
            'pending_picking_group_names' => $pendingPickingGroups,
            'pending_qc_group_names' => $pendingQcGroups,
            'total_groups' => count($groups),
            'completed_picking_groups' => $completedPickingCount,
            'completed_qc_groups' => $completedQcCount,
        ];
    }

    /**
     * API: Cơ chế tách đơn nợ hàng đã được gỡ bỏ
     */
    public function splitBackorder(\Illuminate\Http\Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Cơ chế tách đơn nợ hàng đã được gỡ bỏ.',
        ], 410);
    }

    /**
     * API thống nhất cho nút [Lưu], [Chuyển kiểm] (bên Bốc) và [Lưu], [Giao xe] (bên Kiểm).
     * Ngăn không tự động chuyển trạng thái khi mới nhập số lượng hoặc mới bấm Lưu.
     */
    public function savePickingOrQc(Request $request)
    {
        $raw = [];
        if ($request->getContent()) {
            $raw = json_decode($request->getContent(), true) ?: [];
        }
        $payload = array_merge($request->all(), $raw);

        $mode = $payload['mode'] ?? 'picking'; // 'picking' | 'qc'
        $action = $payload['action'] ?? 'save'; // 'save' | 'transfer_to_qc' | 'handover_to_vehicle'
        $ordersInput = $payload['orders'] ?? [];
        $authUser = auth()->user();
        $performer = $authUser?->name ?? ($payload['performerName'] ?? 'Nhân sự kho');
        $inspector = $payload['inspectorName'] ?? $performer;

        if (empty($ordersInput) || !is_array($ordersInput)) {
            return response()->json(['success' => false, 'message' => 'Không có dữ liệu đơn hàng để lưu!'], 422);
        }

        $isSuper = $authUser && (
            $authUser->id === 1
            || $authUser->hasRole('admin')
            || $authUser->hasRole('Admin')
            || $authUser->hasRole('super-admin')
            || in_array($authUser->department ?? '', ['Ban Giám Đốc', 'IT', 'Quản Lý', 'Executive'])
        );
        $adminOverride = !empty($payload['adminOverride']) && $isSuper;

        if ($mode === 'qc') {
            $canQc = $isSuper || ($authUser && (
                $authUser->hasRole('warehouse')
                || $authUser->hasPermission('inv_picking.qc')
                || $authUser->hasPermission('inv_qc.qc')
                || $authUser->hasPermission('inv_qc.approve')
                || $authUser->hasPermission('inv_picking.assign')
                || $authUser->hasPermission('inv_picking.view_all_categories')
                || \App\Models\StaffGroup::where('status', 'active')->where('user_id', $authUser->id)->exists()
            ));
            if (!$canQc) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền thao tác Kiểm hàng (QC)!'], 403);
            }
        }

        $curNameLower = mb_strtolower(trim((string)$performer));
        $transferredStatuses = \App\Models\Order::PICKING_TRANSFERRED_STATUSES;
        $updatedOrders = [];
        $shouldRefreshAvailability = false;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($ordersInput as $ordData) {
                $orderId = $ordData['orderId'] ?? ($ordData['id'] ?? null);
                if (!$orderId) continue;

                $order = null;
                $isConsignment = false;
                if (is_numeric($orderId) && (int)$orderId >= 900000) {
                    $order = \App\Models\ConsignmentOrder::with(['items.product.category', 'staff', 'warehouse'])->find((int)$orderId - 900000);
                    $isConsignment = (bool)$order;
                }
                if (!$order) {
                    $order = \App\Models\ConsignmentOrder::with(['items.product.category', 'staff', 'warehouse'])->where('code', $orderId)->first();
                    $isConsignment = (bool)$order;
                }
                if (!$order) {
                    $order = \App\Models\Order::with(['items.product.category'])->where('id', $orderId)->orWhere('order_code', $orderId)->first();
                }
                if (!$order) continue;

                $orderCode = $isConsignment ? $order->code : $order->order_code;

                if (in_array($order->status, ['Đang vận chuyển', 'Đã giao', 'Giao thành công', 'Hủy', 'Đã hủy'])) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Đơn hàng {$orderCode} đang ở trạng thái '{$order->status}', không thể chỉnh sửa số lượng bốc/kiểm!"
                    ], 422);
                }

                if ($mode === 'picking' && in_array($order->status, ['Đã kiểm hàng', 'Đã kiểm', 'Đã kiểm xong', 'Đã kiểm hàng (Sẵn sàng xuất xe)', 'Sẵn sàng xuất xe'], true)) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Đơn hàng {$orderCode} đã kiểm hàng & giao xe ({$order->status}), không thể sửa số lượng bốc!"
                    ], 422);
                }

                // Kiểm tra điều kiện Giao xe bên Kiểm: bên Bốc bắt buộc phải bấm [Chuyển kiểm] trước
                $alreadyTransferred = in_array($order->status, $transferredStatuses, true)
                    || ($isConsignment && $order->status === 'in_progress');

                if ($mode === 'qc' && $action === 'handover_to_vehicle' && !$alreadyTransferred) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Không thể giao xe! Đơn hàng {$orderCode} chưa bốc xong hoặc chưa bấm 'Chuyển kiểm'."
                    ], 422);
                }

                $itemsInput = $ordData['items'] ?? [];
                $itemsInputById = [];
                $itemsInputBySku = [];
                foreach ($itemsInput as $iIn) {
                    if (!empty($iIn['id'])) $itemsInputById[(string)$iIn['id']] = $iIn;
                    if (!empty($iIn['sku'])) $itemsInputBySku[(string)$iIn['sku']] = $iIn;
                }

                foreach ($order->items as $orderItem) {
                    $iIn = $itemsInputById[(string)$orderItem->id] ?? ($itemsInputBySku[(string)$orderItem->sku] ?? null);
                    $reqTotal = (float)($orderItem->quantity + ($orderItem->gift_quantity ?? 0));

                    if ($mode === 'picking') {
                        if ($iIn !== null) {
                            $oldPicked = (float)($orderItem->picked_qty ?? 0);
                            $newPicked = max(0.0, (float)($iIn['picked_qty'] ?? $oldPicked));
                            if ($reqTotal > 0 && $newPicked > $reqTotal) {
                                $newPicked = $reqTotal;
                            }

                            $qtyChanged = abs($newPicked - $oldPicked) > 0.0001;
                            $assignedRaw = trim((string)($orderItem->assigned_staff ?? ''));
                            $assignedNameLower = mb_strtolower($assignedRaw);
                            $assignedListLower = array_values(array_filter(
                                array_map(fn($s) => mb_strtolower(trim((string)$s)), (array)($orderItem->assigned_staff_list ?? [])),
                                fn($s) => $s !== '' && $s !== 'null'
                            ));
                            $isUnassigned = ($assignedNameLower === '' || $assignedNameLower === 'null') && empty($assignedListLower);

                            if ($qtyChanged) {
                                $isAllowed = $isUnassigned
                                    || ($assignedNameLower === $curNameLower)
                                    || in_array($curNameLower, $assignedListLower, true)
                                    || $adminOverride
                                    || $isSuper;

                                if (!$isAllowed) {
                                    \Illuminate\Support\Facades\DB::rollBack();
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Mặt hàng [{$orderItem->sku}] của đơn {$orderCode} đã giao cho {$orderItem->assigned_staff}! Bạn không thể sửa số lượng bốc của người khác."
                                    ], 403);
                                }
                            }

                            // Nếu chưa phân công mà thao tác bốc / chuyển kiểm -> tự nhận cho người thao tác
                            if ($isUnassigned && ($newPicked > 0 || $action === 'transfer_to_qc')) {
                                $orderItem->assigned_staff = $performer;
                                $orderItem->assigned_staff_list = [$performer];
                            } else {
                                if (array_key_exists('assigned_staff', $iIn)) {
                                    $stVal = trim((string)($iIn['assigned_staff'] ?? ''));
                                    $orderItem->assigned_staff = ($stVal !== '' && $stVal !== 'null') ? $stVal : null;
                                }
                                if (isset($iIn['assigned_staff_list']) && is_array($iIn['assigned_staff_list'])) {
                                    $cleanList = array_values(array_filter($iIn['assigned_staff_list'], fn($s) => $s && trim((string)$s) !== '' && trim((string)$s) !== 'null'));
                                    $orderItem->assigned_staff_list = $cleanList;
                                }
                            }

                            $orderItem->picked_qty = $newPicked;
                            $orderItem->shortage_reason = null;

                            if ($reqTotal > 0 && $newPicked >= $reqTotal) {
                                $orderItem->is_picked = true;
                                $orderItem->item_status = 'Đủ hàng';
                            } elseif ($newPicked > 0) {
                                $orderItem->is_picked = false;
                                $orderItem->item_status = 'Đang bốc';
                            } else {
                                $orderItem->is_picked = false;
                                $orderItem->item_status = 'Chờ bốc';
                            }
                            $orderItem->save();
                        } elseif ($action === 'transfer_to_qc') {
                            $picked = (float)($orderItem->picked_qty ?? 0);
                            if ($orderItem->item_status === 'Thiếu hàng' || !empty($orderItem->shortage_reason)) {
                                $orderItem->item_status = ($reqTotal > 0 && $picked >= $reqTotal) ? 'Đủ hàng' : ($picked > 0 ? 'Đang bốc' : 'Chờ bốc');
                                $orderItem->shortage_reason = null;
                                $orderItem->save();
                            }
                        }
                    } elseif ($mode === 'qc') {
                        $pickedCap = (float)($orderItem->picked_qty ?? 0);
                        $maxCheck = $alreadyTransferred ? $pickedCap : $reqTotal;

                        if ($iIn !== null) {
                            $newChecked = isset($iIn['checked_qty']) ? max(0.0, (float)$iIn['checked_qty']) : (float)($orderItem->checked_qty ?? 0);
                            if ($newChecked > $maxCheck) {
                                $newChecked = $maxCheck;
                            }
                            $orderItem->checked_qty = $newChecked;
                            if ($action === 'handover_to_vehicle') {
                                $orderItem->is_checked = true;
                                if ($newChecked <= 0 && $pickedCap > 0 && empty($iIn['checked_qty_explicit'])) {
                                    $orderItem->checked_qty = $pickedCap;
                                }
                            } else {
                                $orderItem->is_checked = isset($iIn['is_checked']) ? (bool)$iIn['is_checked'] : ($newChecked >= $maxCheck && $maxCheck > 0);
                            }
                            if (array_key_exists('check_notes', $iIn)) {
                                $orderItem->check_notes = $iIn['check_notes'];
                            }
                            if ($orderItem->is_checked || $orderItem->checked_qty > 0) {
                                $orderItem->checked_by = $inspector;
                                $orderItem->checked_at = now()->format('d/m/Y H:i');
                            }
                            $orderItem->save();
                        } elseif ($action === 'handover_to_vehicle') {
                            $orderItem->is_checked = true;
                            $orderItem->checked_qty = $pickedCap;
                            $orderItem->checked_by = $inspector;
                            $orderItem->checked_at = now()->format('d/m/Y H:i');
                            $orderItem->save();
                        }
                    }
                }

                $order->load(['items.product.category']);
                $hasAnyShort = $order->items->contains(function ($x) {
                    $r = (float)($x->quantity + ($x->gift_quantity ?? 0));
                    return $r > 0 && ((float)$x->picked_qty + 0.0001) < $r;
                });

                if ($mode === 'picking') {
                    if ($action === 'transfer_to_qc') {
                        $order->shortage_reported = false;
                        $order->shortage_reported_at = null;
                        $order->shortage_reported_note = null;
                        $oldSt = $order->status;
                        $order->status = 'Đã bốc xong';
                        $order->save();

                        if ($order instanceof \App\Models\Order) {
                            $order->logOperation(
                                'Chuyển Kiểm Hàng',
                                $hasAnyShort
                                    ? "Bên Bốc đã lưu số lượng thực bốc (có mặt hàng thiếu) và bấm Chuyển kiểm."
                                    : "Bên Bốc đã hoàn tất & bấm Chuyển kiểm.",
                                $performer,
                                $oldSt,
                                $order->status
                            );
                        }
                    } else {
                        // action === 'save': Chỉ lưu số lượng đã bốc, KHÔNG tự động chuyển kiểm / không mở Giao xe
                        $order->shortage_reported = false;
                        $order->shortage_reported_at = null;
                        $order->shortage_reported_note = null;
                        if (!$alreadyTransferred) {
                            $anyPicked = $order->items->contains(fn($x) => (float)$x->picked_qty > 0 || (bool)$x->is_picked);
                            $order->status = $anyPicked ? 'Đang bốc hàng' : 'Soạn hàng';
                        }
                        $order->save();
                    }
                } elseif ($mode === 'qc') {
                    if ($action === 'handover_to_vehicle') {
                        $oldSt = $order->status;
                        $order->status = 'Đã kiểm hàng';
                        $order->save();

                        if ($order instanceof \App\Models\ConsignmentOrder && !$order->stock_deducted && $order->approved_at) {
                            $order->transferStockToEmployeeHoldingWarehouse(auth()->id(), $performer);
                        } elseif ($order instanceof \App\Models\Order) {
                            app(\App\Http\Controllers\OrderController::class)->syncStockForStatus($order, $oldSt, false);
                            $shouldRefreshAvailability = true;
                        }
                        if ($order instanceof \App\Models\Order) {
                            $order->logOperation(
                                'Xác Nhận Kiểm & Giao Xe',
                                "Bên Kiểm ({$inspector}) đã xác nhận kiểm hàng, chuyển Giao xe và trừ tồn kho thực tế" . ($hasAnyShort ? ' (đơn bốc thiếu nhưng đã chuyển kiểm).' : '.'),
                                $performer,
                                $oldSt,
                                $order->status
                            );
                        }
                    } else {
                        // action === 'save' bên Kiểm: giữ nguyên trạng thái bốc nếu chưa chuyển kiểm; nếu đã chuyển kiểm thì đặt Đang kiểm hàng
                        if ($alreadyTransferred && in_array($order->status, ['Đã bốc xong', 'Kiểm hàng'], true)) {
                            $order->status = 'Đang kiểm hàng';
                            $order->save();
                        }
                    }
                }

                $groupProgress = self::evaluateOrderGroupProgress($order);

                try {
                    PickingSyncEvent::create([
                        'order_id'          => (string)$orderId,
                        'event_type'        => ($action === 'save') ? 'ORDER_PROGRESS_SAVED' : 'ORDER_STATUS_CHANGED',
                        'performer_name'    => (string)$performer,
                        'order_status'      => (string)$order->status,
                        'shortage_reported' => (bool)$order->shortage_reported,
                        'extra_data'        => [
                            'orderId'       => (int)$orderId,
                            'orderCode'     => $orderCode,
                            'newStatus'     => $order->status,
                            'stockDeducted' => (bool)$order->stock_deducted,
                            'mode'          => $mode,
                            'action'        => $action,
                            'groupProgress' => $groupProgress,
                            'items'         => $order->items->map(fn($x) => [
                                'id'                  => $x->id,
                                'sku'                 => $x->sku,
                                'picked_qty'          => (float)$x->picked_qty,
                                'is_picked'           => (bool)$x->is_picked,
                                'item_status'         => $x->item_status,
                                'shortage_reason'     => $x->shortage_reason,
                                'assigned_staff'      => $x->assigned_staff,
                                'assigned_staff_list' => $x->assigned_staff_list,
                                'checked_qty'         => (float)$x->checked_qty,
                                'is_checked'          => (bool)$x->is_checked,
                                'check_notes'         => $x->check_notes,
                                'checked_by'          => $x->checked_by,
                                'checked_at'          => $x->checked_at,
                            ])->values()->toArray(),
                        ],
                        'created_at'        => now(),
                    ]);
                } catch (\Throwable $evErr) {
                    \Log::warning('PickingSyncEvent create failed in savePickingOrQc: ' . $evErr->getMessage());
                }

                $updatedOrders[] = [
                    'id'                => (int)$orderId,
                    'status'            => $order->status,
                    'shortage_reported' => (bool)$order->shortage_reported,
                    'stock_deducted'    => (bool)$order->stock_deducted,
                    'groups_progress'   => $groupProgress['groups'],
                    'group_summary'     => $groupProgress,
                    'items'             => $order->items->map(fn($x) => [
                        'id'                  => $x->id,
                        'sku'                 => $x->sku,
                        'picked_qty'          => (float)$x->picked_qty,
                        'is_picked'           => (bool)$x->is_picked,
                        'item_status'         => $x->item_status,
                        'shortage_reason'     => $x->shortage_reason,
                        'assigned_staff'      => $x->assigned_staff,
                        'assigned_staff_list' => $x->assigned_staff_list,
                        'checked_qty'         => (float)$x->checked_qty,
                        'is_checked'          => (bool)$x->is_checked,
                        'check_notes'         => $x->check_notes,
                        'checked_by'          => $x->checked_by,
                        'checked_at'          => $x->checked_at,
                    ])->values()->toArray(),
                ];
            }

            \Illuminate\Support\Facades\DB::commit();

            if ($shouldRefreshAvailability) {
                \App\Http\Controllers\OrderController::refreshAvailabilityCache();
            }

            return response()->json([
                'success'       => true,
                'updatedOrders' => $updatedOrders,
                'message'       => match ($action) {
                    'transfer_to_qc'      => 'Đã lưu số lượng thực bốc và Chuyển Kiểm hàng thành công!',
                    'handover_to_vehicle' => 'Đã xác nhận Kiểm hàng, chuyển Giao xe và trừ tồn kho thực tế thành công!',
                    default               => $mode === 'qc' ? 'Đã lưu số lượng kiểm hàng!' : 'Đã lưu số lượng đã bốc (chưa chuyển kiểm)!',
                },
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('savePickingOrQc failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu dữ liệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Nhân viên xe tự phân bổ hàng thiếu cho các đơn trên xe / lô bốc
     * Tự động cắt giảm số lượng đơn gốc theo phân bổ và sinh Đơn Giao Sau (Backorder)
     */
    public function allocateVehicleShortage(\Illuminate\Http\Request $request)
    {
        $raw = json_decode($request->getContent(), true) ?? [];
        $allocations = $request->input('allocations') ?? ($raw['allocations'] ?? []);
        $note = $request->input('note') ?? ($raw['note'] ?? 'Phân bổ số lượng thực bốc theo đơn');
        $autoCreateBackorder = false;
        $markPickedIfReady = $request->input('markPickedIfReady', false) ?? ($raw['markPickedIfReady'] ?? false);

        if (empty($allocations)) {
            return response()->json(['success' => false, 'message' => 'Không có dữ liệu phân bổ'], 422);
        }

        // Nhóm allocations theo order_id
        $groupedByOrder = [];
        foreach ($allocations as $alloc) {
            $orderId = $alloc['order_id'] ?? null;
            if ($orderId) {
                $groupedByOrder[$orderId][] = $alloc;
            }
        }

        $createdBackorders = [];
        $updatedOrderCodes = [];

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($groupedByOrder as $orderId => $orderAllocations) {
                $order = \App\Models\Order::with('items')->find($orderId);
                if (!$order) continue;

                $shortageItemsForSplit = [];

                foreach ($orderAllocations as $alloc) {
                    $itemId = $alloc['item_id'] ?? null;
                    $sku = $alloc['sku'] ?? null;
                    $allocatedQty = (float)($alloc['allocated_qty'] ?? 0);
                    $reqQty = (float)($alloc['req_qty'] ?? 0);

                    // Tìm item trong order
                    $item = $order->items->first(function ($it) use ($itemId, $sku) {
                        if ($itemId && $it->id == $itemId) return true;
                        if ($sku && $it->sku == $sku) return true;
                        return false;
                    });

                    if (!$item) continue;

                    $item->picked_qty = $allocatedQty;

                    if ($allocatedQty >= $reqQty && $reqQty > 0) {
                        $item->is_picked = true;
                        $item->item_status = 'Đủ hàng';
                        $item->shortage_reason = null;
                        $item->save();
                    } else {
                        $item->is_picked = false;
                        $item->item_status = $allocatedQty > 0 ? 'Đang bốc' : 'Chờ bốc';
                        $item->shortage_reason = null;
                        $item->save();
                    }
                }

                // Nếu có hàng thiếu và bật tự động tách đơn nợ
                if (!empty($shortageItemsForSplit) && $autoCreateBackorder) {
                    $splitRes = \App\Services\BackorderService::splitOrderOnShortage($order, $shortageItemsForSplit, $note);
                    if ($splitRes['success'] && isset($splitRes['backorder'])) {
                        $createdBackorders[] = [
                            'parent_code'    => $order->order_code,
                            'backorder_code' => $splitRes['backorder']->order_code,
                            'backorder_id'   => $splitRes['backorder']->id,
                            'total_amount'   => $splitRes['backorder']->total_amount,
                        ];
                    }
                    // Reload order sau khi tách
                    $order = $order->fresh(['items']);
                }

                // Cập nhật trạng thái đơn (KHÔNG tự chuyển sang Đã bốc xong trừ khi markPickedIfReady được bật tường minh)
                if ($order && $order->items) {
                    $alreadyTransferred = in_array($order->status, \App\Models\Order::PICKING_TRANSFERRED_STATUSES, true);
                    if (!$alreadyTransferred) {
                        $allPicked = $order->items->count() > 0 && $order->items->every(fn($it) => (bool)$it->is_picked && (float)$it->picked_qty >= (float)$it->quantity);
                        if ($allPicked && $markPickedIfReady) {
                            $order->status = 'Đã bốc xong';
                            $order->shortage_reported = false;
                        } else {
                            $order->status = 'Đang bốc hàng';
                        }
                        $order->save();
                    }
                    $updatedOrderCodes[] = $order->order_code;
                }

                // Ghi log PickingSyncEvent
                try {
                    \App\Models\PickingSyncEvent::create([
                        'order_id'       => $order->id,
                        'action'         => 'vehicle_shortage_allocated',
                        'status'         => $order->status,
                        'performer_name' => auth()->user()?->name ?? 'Nhân viên xe',
                        'details'        => [
                            'allocations' => $orderAllocations,
                            'note'        => $note,
                        ]
                    ]);
                } catch (\Throwable $e) {}
            }

            \Illuminate\Support\Facades\DB::commit();

            \App\Http\Controllers\OrderController::refreshAvailabilityCache();

            return response()->json([
                'success'           => true,
                'message'           => 'Đã phân bổ hàng trên xe thành công! ' . (count($createdBackorders) > 0 ? 'Đã tự động tạo ' . count($createdBackorders) . ' đơn giao sau.' : ''),
                'createdBackorders' => $createdBackorders,
                'updatedOrderCodes' => $updatedOrderCodes,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error allocateVehicleShortage: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi phân bổ hàng thiếu: ' . $e->getMessage(),
            ], 500);
        }
    }
}
