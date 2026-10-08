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
        $shortageReported = (bool) ($request->input('shortageReported') ?? ($raw['shortageReported'] ?? false));
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
            $order = \App\Models\Order::where('id', $orderId)->orWhere('order_code', $orderId)->first();
            if ($order) {
                if ($shortageReported) {
                    $order->shortage_reported = true;
                    $order->shortage_reported_at = now();
                    if (!empty($extraData['shortageNote']) || !empty($shortageReason)) {
                        $order->shortage_reported_note = $extraData['shortageNote'] ?? $shortageReason;
                    }
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

                        if ($isPickedQtyUpdate && empty($extraData['quickPickAll'])) {
                            $authUser = auth()->user();
                            $performer = $authUser?->name ?? $performerName;
                            $curName = mb_strtolower(trim($performer));
                            $assignedName = mb_strtolower(trim($orderItem->assigned_staff ?? ''));
                            $assignedList = array_map(fn($s) => mb_strtolower(trim($s)), (array)($orderItem->assigned_staff_list ?? []));
                            $isSuper = $authUser && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin') || $authUser->id === 1);
                            $adminOverride = !empty($raw['adminOverride']) || !empty($extraData['adminOverride']);

                            $isAllowed = ($assignedName !== '' && $assignedName === $curName) 
                                || in_array($curName, $assignedList) 
                                || ($isSuper && $adminOverride);

                            if (!$isAllowed) {
                                return response()->json([
                                    'success' => false,
                                    'message' => 'Bạn không được phân công soạn mặt hàng này! Chỉ ' . ($orderItem->assigned_staff ?: 'người được phân công') . ' mới được sửa số lượng bốc.'
                                ], 403);
                            }
                        }

                        if ($isPickedQtyUpdate) {
                            if ($reqQtyTotal > 0 && $pickedQty > $reqQtyTotal) {
                                $pickedQty = $reqQtyTotal;
                            }
                            $orderItem->picked_qty = $pickedQty;
                            if ($reqQtyTotal > 0 && $pickedQty >= $reqQtyTotal) {
                                $orderItem->is_picked = true;
                                $orderItem->item_status = 'Đủ hàng';
                                $orderItem->shortage_reason = null;
                            }
                        }
                        if ($itemStatus) {
                            $orderItem->item_status = $itemStatus;
                            if ($itemStatus === 'Đủ hàng' || ($reqQtyTotal > 0 && $orderItem->picked_qty >= $reqQtyTotal)) {
                                $orderItem->is_picked = true;
                                $orderItem->item_status = 'Đủ hàng';
                                $orderItem->shortage_reason = null;
                            }
                        }
                        if ($shortageReason !== null) {
                            if ($reqQtyTotal > 0 && $orderItem->picked_qty >= $reqQtyTotal) {
                                $orderItem->shortage_reason = null;
                                $orderItem->item_status = 'Đủ hàng';
                            } else {
                                $orderItem->shortage_reason = $shortageReason;
                            }
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
                            if (isset($extraData['isChecked'])) $orderItem->is_checked = (bool)$extraData['isChecked'];
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
                        $itReq = (float) ($it->quantity + ($it->gift_quantity ?? 0));
                        $it->is_checked = true;
                        $it->checked_qty = $it->picked_qty > 0 ? $it->picked_qty : $itReq;
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
                        $itReq = (float) ($it->quantity + ($it->gift_quantity ?? 0));
                        $it->is_checked = true;
                        $it->checked_qty = $it->picked_qty > 0 ? $it->picked_qty : $itReq;
                        $it->checked_by = $performerName;
                        $it->checked_at = now()->format('d/m/Y H:i');
                        $it->save();
                    }
                }

                // ĐÁNH GIÁ TIẾN ĐỘ TỪNG NHÓM VÀ TOÀN BỘ ĐƠN HÀNG
                // Trạng thái đơn CHỈ được chuyển đổi triệt để khi TẤT CẢ các nhóm cùng hoàn thành!
                $order->load(['items.product.category']);
                $groupProgress = self::evaluateOrderGroupProgress($order);

                if (!in_array($order->status, ['Đang vận chuyển', 'Đã giao'])) {
                    if ($groupProgress['has_shortage']) {
                        $order->status = 'Kho báo thiếu hàng';
                    } elseif ($groupProgress['all_groups_qc_completed']) {
                        $order->status = 'Đã kiểm hàng';
                        $order->shortage_reported = false;
                        $order->shortage_reported_at = null;
                        $order->shortage_reported_note = null;
                    } elseif ($groupProgress['all_groups_picking_completed']) {
                        $order->status = $groupProgress['completed_qc_groups'] > 0 ? 'Đang kiểm hàng' : 'Đã bốc xong';
                        $order->shortage_reported = false;
                        $order->shortage_reported_at = null;
                        $order->shortage_reported_note = null;
                    } else {
                        // Chưa đủ tất cả các nhóm hoàn thành:
                        // Nếu đã có nhóm hoặc sản phẩm được kiểm hàng (QC): chuyển thành 'Đang kiểm hàng'
                        // Nếu đã có nhóm hoàn tất bốc hoặc có sản phẩm đã bốc: chuyển thành 'Đang bốc hàng'
                        if ($groupProgress['completed_qc_groups'] > 0 || $order->items->contains(fn($it) => (bool)$it->is_checked)) {
                            $order->status = 'Đang kiểm hàng';
                        } elseif ($groupProgress['completed_picking_groups'] > 0 || $order->items->contains(fn($it) => (bool)$it->is_picked || (float)$it->picked_qty > 0)) {
                            $order->status = 'Đang bốc hàng';
                        } else {
                            if (in_array($order->status, ['Đã bốc xong', 'Đã kiểm hàng', 'Kho đã xuất đủ', 'Đã kiểm hàng (Sẵn sàng xuất xe)'])) {
                                $order->status = 'Soạn hàng';
                            }
                        }
                    }
                }
                $order->save();

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
     * API: Báo thiếu hàng cho đơn hàng và gửi cảnh báo tới Người phụ trách đơn hàng
     */
    public function reportShortage(Request $request)
    {
        $raw = json_decode($request->getContent(), true) ?? [];
        $orderId = $request->input('orderId') ?? ($raw['orderId'] ?? null);
        $shortageItems = $request->input('shortageItems') ?? ($raw['shortageItems'] ?? []);
        $reporterName = $request->input('reporterName') ?? ($raw['reporterName'] ?? (auth()->user()?->name ?? 'Nhân sự kho'));
        $note = $request->input('note') ?? ($raw['note'] ?? null);

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'Missing orderId'], 422);
        }

        $order = \App\Models\Order::with(['items', 'dealer'])->where('id', $orderId)->orWhere('order_code', $orderId)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Cập nhật trạng thái đơn sang Kho báo thiếu hàng
        $order->status = 'Kho báo thiếu hàng';
        $order->shortage_reported = true;
        $order->shortage_reported_at = now();
        if ($note) {
            $order->shortage_reported_note = $note;
        }
        $order->save();

        // Cập nhật từng mặt hàng thiếu
        foreach ($shortageItems as $sIt) {
            $sSku = $sIt['sku'] ?? null;
            $sId = $sIt['id'] ?? null;
            $target = null;
            if ($sId) $target = $order->items->firstWhere('id', $sId);
            if (!$target && $sSku) $target = $order->items->firstWhere('sku', $sSku);

            if ($target) {
                $target->item_status = 'Thiếu hàng';
                if (!empty($sIt['reason'])) {
                    $target->shortage_reason = $sIt['reason'];
                }
                if (isset($sIt['picked_qty'])) {
                    $target->picked_qty = (float) $sIt['picked_qty'];
                }
                $target->save();
            }
        }

        // Xác định Người phụ trách đơn hàng (người tạo đơn / NV kinh doanh / NV chăm sóc đại lý)
        $supervisor = $order->order_creator 
            ?: ($order->salesRep?->name 
            ?: ($order->dealer?->staff_in_charge 
            ?: ($order->creator?->name ?: 'Admin KBTech')));

        // Tạo sự kiện đồng bộ Realtime PickingSyncEvent gửi tới Người phụ trách đơn hàng
        $event = PickingSyncEvent::create([
            'order_id'            => (string) $order->id,
            'event_type'          => 'SHORTAGE_REPORTED',
            'performer_name'      => (string) $reporterName,
            'assigned_staff'      => (string) $supervisor, // Người phụ trách đơn nhận thông báo
            'order_status'        => 'Kho báo thiếu hàng',
            'shortage_reported'   => true,
            'extra_data'          => [
                'orderId'        => (int) $order->id,
                'orderCode'      => $order->order_code,
                'customerName'   => $order->customer_name,
                'warehouse'      => $order->warehouse,
                'reporter'       => $reporterName,
                'supervisor'     => $supervisor,
                'shortageItems'  => $shortageItems,
                'reportedAt'     => now()->format('H:i:s d/m/Y'),
                'note'           => $note,
                'message'        => "Kho báo thiếu hàng đơn {$order->order_code} tới người phụ trách: {$supervisor}",
            ],
            'created_at'          => now(),
        ]);

        return response()->json([
            'success'    => true,
            'message'    => "Đã gửi cảnh báo thiếu hàng tới người phụ trách đơn hàng: {$supervisor}",
            'supervisor' => $supervisor,
            'order'      => $order->fresh(['items']),
            'event_id'   => $event->id
        ]);
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
            if ($orderToPick) {
                if (!empty($orderToPick->delivery_date)) {
                    $isDebt = ($orderToPick->status === 'Nợ hàng') || str_contains($orderToPick->notes ?? '', '[ĐƠN NỢ HÀNG]') || str_starts_with($orderToPick->order_code, 'DH-NO');
                    if (!$isDebt) {
                        if (in_array($orderToPick->status, ['Chốt đơn', 'Đã duyệt'])) {
                            $orderToPick->status = 'Soạn hàng';
                        }
                        if ($request->filled('picking_date')) {
                            $orderToPick->picking_date = $request->query('picking_date');
                        } elseif (empty($orderToPick->picking_date)) {
                            $orderToPick->picking_date = now()->format('Y-m-d');
                        }
                        $orderToPick->save();
                    }
                }
            }
        }

        // Bốc hàng & Kiểm hàng CHỈ LẤY những đơn hàng có dữ liệu ngày giao hàng (delivery_date) VÀ ngày soạn hàng (picking_date)
        $orders = \App\Models\Order::with(['items.product.category', 'dealer', 'creator', 'salesRep'])
            ->whereIn('status', $allowedStatuses)
            ->whereNotNull('delivery_date')
            ->whereNotNull('picking_date')
            ->latest('id')
            ->get();

        $allActiveGroups = \App\Models\StaffGroup::where('status', 'active')->with(['leader'])->get();
        $allCategories = \App\Models\Category::all(['id', 'code', 'name']);

        foreach ($orders as $ord) {
            $ord->supervisor = $ord->order_creator 
                ?: ($ord->salesRep?->name 
                ?: ($ord->dealer?->staff_in_charge 
                ?: ($ord->creator?->name ?: 'Admin KBTech')));

            // Đánh giá tiến độ từng nhóm bốc hàng trên TOÀN BỘ mặt hàng của đơn
            $evalProgress = self::evaluateOrderGroupProgress($ord, $allActiveGroups, $allCategories);
            $ord->groups_progress = $evalProgress['groups'];
            $ord->group_summary = $evalProgress;

            // Tự động kiểm tra và dọn dẹp các món đã bốc đủ nhưng còn vướng cờ thiếu do cắt đơn trước đó
            $hasAnyActualShortage = $evalProgress['has_shortage'];
            $allActualPicked = $evalProgress['all_groups_picking_completed'];

            foreach ($ord->items as $it) {
                $req = (float) ($it->quantity + ($it->gift_quantity ?? 0));
                if ($req > 0 && (float)$it->picked_qty >= $req) {
                    if ($it->item_status === 'Thiếu hàng' || !empty($it->shortage_reason)) {
                        $it->item_status = 'Đủ hàng';
                        $it->shortage_reason = null;
                        $it->is_picked = true;
                        $it->save();
                    }
                }
            }

            if (!$hasAnyActualShortage && $ord->status === 'Kho báo thiếu hàng') {
                $ord->status = $allActualPicked ? 'Đã bốc xong' : 'Soạn hàng';
                $ord->shortage_reported = false;
                $ord->shortage_reported_at = null;
                $ord->shortage_reported_note = null;
                $ord->save();
            }
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
        $pickingStaff = \App\Models\User::select('id', 'name', 'email', 'department')
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
        $allActiveGroups = \App\Models\StaffGroup::where('status', 'active')->with(['leader'])->get();
        $leaderGroups = $allActiveGroups->filter(fn($g) => (int)$g->user_id === (int)$currentUser->id);
        $memberGroups = $allActiveGroups->filter(fn($g) => $g->hasMember($currentUser->id));
        $userGroups = $leaderGroups->merge($memberGroups)->unique('id');

        $allCategories = \App\Models\Category::all(['id', 'code', 'name']);
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
            'categoryStaffMap'
        ));
    }

    /**
     * Kiểm tra user có quyền phân công người khác soạn mặt hàng này không
     */
    public function canUserAssignItem(?\App\Models\User $user, ?\App\Models\OrderItem $item): bool
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
    public static function evaluateOrderGroupProgress(\App\Models\Order $order, $activeGroups = null, $allCategories = null): array
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
            $isPicked = ($it->is_picked || ($req > 0 && (float)$it->picked_qty >= $req)) && $it->item_status !== 'Thiếu hàng';
            $isChecked = (bool)$it->is_checked || ($req > 0 && (float)$it->checked_qty >= $req);
            $isShort = $req > 0 && (float)$it->picked_qty < $req && ($it->item_status === 'Thiếu hàng' || !empty($it->shortage_reason));

            $groupBuckets[$gKey]['items_count']++;
            if ($isPicked) {
                $groupBuckets[$gKey]['picked_count']++;
            }
            if ($isChecked) {
                $groupBuckets[$gKey]['checked_count']++;
            }
            if ($isShort) {
                $groupBuckets[$gKey]['has_shortage'] = true;
                $groupBuckets[$gKey]['shortage_items_count']++;
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
            return ($i->is_picked || ($r > 0 && (float)$i->picked_qty >= $r)) && $i->item_status !== 'Thiếu hàng';
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
}

