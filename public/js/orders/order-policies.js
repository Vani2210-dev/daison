/**
 * Standalone Order Policies Module for Antigravity ERP
 * Automatically attaches policy evaluation, selection, and mutual-exclusivity
 * methods to Alpine's orderFormApp instance and provides global fallbacks.
 */
(function() {
    function getOrderApp() {
        const el = document.querySelector('.order-form-app-wrapper');
        if (el && window.Alpine && typeof window.Alpine.$data === 'function') {
            return window.Alpine.$data(el);
        }
        if (el && el._x_dataStack && el._x_dataStack.length > 0) {
            return el._x_dataStack[0];
        }
        return null;
    }

    const OrderPolicyMethods = {
        getSelectedPolicies() {
            const ids = Array.isArray(this.formData.sales_policy_ids) ? this.formData.sales_policy_ids.filter(Boolean) : [];
            return ids.map(id => this.getSalesPolicyById(id)).filter(Boolean);
        },

        getPolicyAppliedProductsSummary(policy) {
            if (!policy) return '';
            const claimed = (this.items || []).filter(it => it._claimedByPolicyId && String(it._claimedByPolicyId) === String(policy.id));
            if (claimed.length > 0) {
                const names = Array.from(new Set(claimed.map(it => it.name || it.sku || 'Sản phẩm')));
                if (names.length <= 2) {
                    return names.join(', ');
                }
                return names.slice(0, 2).join(', ') + ' và ' + (names.length - 2) + ' SP khác';
            }
            const pTargets = this.getNormalizedPolicyProductIds(policy);
            if (pTargets.length > 0) {
                const targetProds = pTargets.map(id => this.getProduct(id)?.name).filter(Boolean);
                if (targetProds.length > 0) {
                    return targetProds.length <= 2 ? targetProds.join(', ') : targetProds.slice(0, 2).join(', ') + '...';
                }
            }
            return policy.apply_scope === 'order' ? 'Toàn đơn hàng' : 'Nhóm sản phẩm áp dụng';
        },

        getPolicyBenefitDescription(policy) {
            if (!policy) return '';
            if (policy.policy_type === 'quantity') {
                const gifts = (this.items || []).filter(it => it.isPromotionalGift && String(it.promoPolicyId || '') === String(policy.id));
                if (gifts.length > 0) {
                    return gifts.map(g => 'Tặng +' + (g.quantity || 1) + ' ' + (g.unit || 'SP') + ' (' + (g.name || 'Quà') + ')').join('; ');
                }
                const ev = this.getPolicyEvaluation(policy);
                return ev.benefitText ? `Tặng hàng (${ev.benefitText})` : 'Tặng hàng kèm';
            }
            if (policy.policy_type === 'percentage') {
                const claimed = (this.items || []).filter(it => it._claimedByPolicyId && String(it._claimedByPolicyId) === String(policy.id));
                const totalDisc = claimed.reduce((sum, it) => sum + (Number(it.discount_amount) || 0), 0);
                const pct = Number(policy.discount_percent) || 0;
                return totalDisc > 0
                    ? `Chiết khấu ${pct}% (-${this.formatNumber(totalDisc)} đ)`
                    : `Chiết khấu ${pct}%`;
            }
            if (policy.policy_type === 'amount') {
                const claimed = (this.items || []).filter(it => it._claimedByPolicyId && String(it._claimedByPolicyId) === String(policy.id));
                const totalDisc = claimed.reduce((sum, it) => sum + (Number(it.discount_amount) || 0), 0);
                return totalDisc > 0
                    ? `Giảm trực tiếp -${this.formatNumber(totalDisc)} đ`
                    : `Giảm -${this.formatNumber(policy.discount_amount || 0)} đ`;
            }
            return 'Khuyến mại áp dụng';
        },

        getAvailablePoliciesToAdd() {
            const selectedIds = new Set((this.formData.sales_policy_ids || []).map(id => String(id)));
            if (this.formData.order_policy_id) selectedIds.add(String(this.formData.order_policy_id));

            // Chỉ xét những sản phẩm chưa bị bất kỳ chính sách nào chiếm dụng
            const unclaimedItems = (this.items || []).filter(it =>
                !it.isPromotionalGift &&
                it.checked !== false &&
                it.product_id &&
                !it._claimedByPolicyId
            );

            if (unclaimedItems.length === 0) return [];

            const available = [];
            (this.salesPoliciesCatalog || []).forEach(p => {
                if (!p || selectedIds.has(String(p.id))) return;
                if (!this.isCustomerEligibleForPolicy(p)) return;

                const ev = this.evaluatePolicyBenefitsOnItems(p, unclaimedItems);
                if (ev.eligible) {
                    available.push(p);
                }
            });

            return available;
        },

        addPolicyById(policyId) {
            if (!policyId) return;
            const idStr = String(policyId);
            if (!Array.isArray(this.formData.sales_policy_ids)) {
                this.formData.sales_policy_ids = [];
            }
            if (!this.formData.sales_policy_ids.map(String).includes(idStr)) {
                this.formData.sales_policy_ids.push(idStr);
            }
            this.evaluateOrderPolicies();
        },

        removeSelectedPolicy(policyId) {
            if (!policyId) return;
            const idStr = String(policyId);
            if (Array.isArray(this.formData.sales_policy_ids)) {
                this.formData.sales_policy_ids = this.formData.sales_policy_ids.filter(id => String(id) !== idStr);
            }
            if (String(this.formData.order_policy_id || '') === idStr) {
                this.formData.order_policy_id = '';
            }
            this.cleanupEffectsOfRemovedPolicy(idStr);
            this.evaluateOrderPolicies();
        },

        clearAllOrderPolicies() {
            const oldIds = Array.isArray(this.formData.sales_policy_ids) ? [...this.formData.sales_policy_ids] : [];
            if (this.formData.order_policy_id) oldIds.push(this.formData.order_policy_id);

            this.formData.order_policy_id = '';
            this.formData.sales_policy_ids = [];
            this.formData.sales_policy_id = '';

            (this.items || []).forEach(it => {
                if (!it.isPromotionalGift) {
                    it._claimedByPolicyId = null;
                    it.selected_policy_id = '';
                    if (it._autoPromoDiscount && !it._manualDiscount) {
                        it.discount_percent = 0;
                        it.discount_amount = 0;
                        it._autoPromoDiscount = false;
                        it._appliedPromoPolicyId = null;
                        it.amount = Math.max(0, Math.round((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)));
                    }
                }
            });

            for (let i = this.items.length - 1; i >= 0; i--) {
                const g = this.items[i];
                if (g.isPromotionalGift && (g._giftKey || g.promoPolicyId)) {
                    this.items.splice(i, 1);
                }
            }

            if (this._autoOrderDiscount && !this._manualOrderDiscount) {
                this.orderDiscountPercent = 0;
                this.formData.discount_amount = 0;
                this._autoOrderDiscount = false;
            }

            this.evaluateOrderPolicies();
        },

        evaluatePolicyBenefitsOnItems(policy, availableItems) {
            if (!policy || !this.isCustomerEligibleForPolicy(policy)) {
                return { eligible: false, reason: 'Chỉ áp dụng đại lý chỉ định', benefitText: '', statusText: 'Không thuộc đối tượng đại lý', claimedItems: [], qualifyingGifts: [], totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0 };
            }

            const matchingItems = (availableItems || []).filter(it =>
                !it.isPromotionalGift &&
                it.checked !== false &&
                it.product_id &&
                this.isProductInPolicy(policy, it.product_id)
            );

            if (matchingItems.length === 0) {
                return { eligible: false, reason: 'Chưa có SP phù hợp', benefitText: '', statusText: 'Chưa có sản phẩm phù hợp', claimedItems: [], qualifyingGifts: [], totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0 };
            }

            const pTargets = this.getNormalizedPolicyProductIds(policy);
            const isGeneralScope = pTargets.length === 0;

            if (policy.policy_type === 'quantity') {
                const buyQty = Number(policy.buy_qty) || 0;
                const freeQty = Number(policy.free_qty) || 0;
                let freeProductId = policy.free_product_id || null;

                let tiersList = policy.gift_tiers;
                if (typeof tiersList === 'string') {
                    try { tiersList = JSON.parse(tiersList); } catch(e) { tiersList = []; }
                }

                let sortedTiers = [];
                let minBuyRequired = buyQty;
                if (Array.isArray(tiersList) && tiersList.length > 0) {
                    sortedTiers = [...tiersList]
                        .map(t => ({
                            buy_qty: Number(t.buy_qty) || 0,
                            free_qty: Number(t.free_qty) || 0,
                            free_product_id: t.free_product_id || null
                        }))
                        .filter(t => t.buy_qty > 0 && t.free_qty > 0)
                        .sort((a, b) => b.buy_qty - a.buy_qty);
                    if (sortedTiers.length > 0) {
                        minBuyRequired = sortedTiers[sortedTiers.length - 1].buy_qty;
                    }
                }

                if (isGeneralScope) {
                    const productQtyMap = new Map();
                    const productItemsMap = new Map();
                    matchingItems.forEach(it => {
                        const pid = String(it.product_id);
                        const q = Number(it.quantity) || 0;
                        productQtyMap.set(pid, (productQtyMap.get(pid) || 0) + q);
                        if (!productItemsMap.has(pid)) productItemsMap.set(pid, []);
                        productItemsMap.get(pid).push(it);
                    });

                    const qualifyingGifts = [];
                    const claimedItems = [];
                    let maxSingleQty = 0;

                    productQtyMap.forEach((qty, pid) => {
                        if (qty > maxSingleQty) maxSingleQty = qty;
                        let itemFree = 0;
                        let itemFreeProdId = freeProductId || pid;
                        let itemGiftLabel = `[TẶNG KÈM] ${policy.name}`;

                        if (sortedTiers.length > 0) {
                            const matchedTier = sortedTiers.find(t => qty >= t.buy_qty);
                            if (matchedTier) {
                                const mult = Math.floor(qty / matchedTier.buy_qty);
                                itemFree = mult * matchedTier.free_qty;
                                if (matchedTier.free_product_id) {
                                    itemFreeProdId = matchedTier.free_product_id;
                                }
                                itemGiftLabel = `[TẶNG KÈM MUA ${matchedTier.buy_qty} TẶNG ${matchedTier.free_qty}] ${policy.name}`;
                            }
                        } else if (buyQty > 0 && freeQty > 0 && qty >= buyQty) {
                            const mult = Math.floor(qty / buyQty);
                            itemFree = mult * freeQty;
                        }

                        if (itemFree > 0) {
                            const pObj = this.getProduct(itemFreeProdId);
                            qualifyingGifts.push({
                                product_id: itemFreeProdId,
                                qty: itemFree,
                                prod: pObj,
                                giftLabel: itemGiftLabel
                            });
                            const pItems = productItemsMap.get(pid) || [];
                            pItems.forEach(it => claimedItems.push(it));
                        }
                    });

                    const totalFree = qualifyingGifts.reduce((sum, g) => sum + g.qty, 0);

                    if (totalFree <= 0 || qualifyingGifts.length === 0) {
                        const needMore = Math.max(1, Math.ceil((minBuyRequired || 1) - maxSingleQty));
                        return {
                            eligible: false,
                            reason: `Thiếu ${needMore} SP`,
                            benefitText: '',
                            statusText: `Chưa đạt (cần mua tối thiểu ${minBuyRequired} SP cho cùng 1 sản phẩm, hiện có tối đa ${maxSingleQty} SP)`,
                            claimedItems: [],
                            qualifyingGifts: [],
                            totalFree: 0,
                            freeProd: null,
                            giftLabel: '',
                            discountPct: 0,
                            discountAmt: 0
                        };
                    }

                    const firstGift = qualifyingGifts[0];
                    const summaryGiftName = qualifyingGifts.length === 1 
                        ? (firstGift.prod?.name || 'quà tặng') 
                        : `${qualifyingGifts.length} loại quà tặng kèm`;

                    return {
                        eligible: true,
                        reason: '',
                        benefitText: `Tặng +${totalFree}`,
                        statusText: `Đạt: Tặng +${totalFree} SP (${summaryGiftName})`,
                        claimedItems,
                        qualifyingGifts,
                        totalFree,
                        freeProd: firstGift.prod,
                        giftLabel: firstGift.giftLabel,
                        discountPct: 0,
                        discountAmt: 0
                    };
                }

                // Nhóm sản phẩm chỉ định cụ thể
                const totalMatchedQty = matchingItems.reduce((sum, it) => sum + (Number(it.quantity) || 0), 0);
                let matchedTier = null;
                if (sortedTiers.length > 0) {
                    matchedTier = sortedTiers.find(t => totalMatchedQty >= t.buy_qty);
                }

                let totalFree = 0;
                let giftLabel = `[TẶNG KÈM] ${policy.name}`;
                if (matchedTier) {
                    const mult = Math.floor(totalMatchedQty / matchedTier.buy_qty);
                    totalFree = mult * matchedTier.free_qty;
                    if (matchedTier.free_product_id) {
                        freeProductId = matchedTier.free_product_id;
                    }
                    giftLabel = `[TẶNG KÈM MUA ${matchedTier.buy_qty} TẶNG ${matchedTier.free_qty}] ${policy.name}`;
                } else if (buyQty > 0 && freeQty > 0 && totalMatchedQty >= buyQty) {
                    const mult = Math.floor(totalMatchedQty / buyQty);
                    totalFree = mult * freeQty;
                }

                if (totalFree <= 0) {
                    const needMore = Math.max(1, Math.ceil((minBuyRequired || 1) - totalMatchedQty));
                    return {
                        eligible: false,
                        reason: `Thiếu ${needMore} SP`,
                        benefitText: '',
                        statusText: `Chưa đạt (cần thêm ${needMore} SP, hiện có ${this.formatNumber(totalMatchedQty)})`,
                        claimedItems: [],
                        qualifyingGifts: [],
                        totalFree: 0,
                        freeProd: null,
                        giftLabel: '',
                        discountPct: 0,
                        discountAmt: 0
                    };
                }

                let freeProd = null;
                if (freeProductId) freeProd = this.getProduct(freeProductId);
                if (!freeProd && policy.free_product) freeProd = policy.free_product;
                if (!freeProd) {
                    const bestItem = [...matchingItems].sort((a, b) => (Number(b.quantity) || 0) - (Number(a.quantity) || 0))[0];
                    if (bestItem) freeProd = this.getProduct(bestItem.product_id);
                }

                const freeName = freeProd?.name || 'quà tặng';
                return {
                    eligible: true,
                    reason: '',
                    benefitText: `Tặng +${totalFree}`,
                    statusText: `Đạt: Tặng +${totalFree} ${freeProd?.unit || 'SP'} (${freeName})`,
                    claimedItems: matchingItems,
                    qualifyingGifts: [{ product_id: freeProd?.id || matchingItems[0]?.product_id || '', qty: totalFree, prod: freeProd, giftLabel }],
                    totalFree,
                    freeProd,
                    giftLabel,
                    discountPct: 0,
                    discountAmt: 0
                };
            }

            const minVal = Number(policy.min_order_value) || 0;
            const buyQty = Number(policy.buy_qty) || 0;

            let evalItems = matchingItems.filter(it => this.isItemOriginalPriceTier(it));
            if (evalItems.length === 0) {
                return {
                    eligible: false,
                    reason: 'Cần SP Nguyên Giá',
                    benefitText: '',
                    statusText: 'Chỉ áp dụng cho dòng sản phẩm giá Nguyên Giá',
                    claimedItems: [],
                    qualifyingGifts: [],
                    totalFree: 0,
                    freeProd: null,
                    giftLabel: '',
                    discountPct: 0,
                    discountAmt: 0
                };
            }

            const evalQty = evalItems.reduce((sum, it) => sum + (Number(it.quantity) || 0), 0);
            const evalSubtotal = evalItems.reduce((sum, it) => {
                const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                const manualDisc = (it._manualDiscount && !it._autoPromoDiscount) ? (Number(it.discount_amount) || 0) : 0;
                return sum + Math.max(0, lineBase - manualDisc);
            }, 0);

            if (buyQty > 0 && evalQty < buyQty) {
                const needMore = Math.max(1, Math.ceil(buyQty - evalQty));
                return {
                    eligible: false,
                    reason: `Thiếu ${needMore} SP`,
                    benefitText: '',
                    statusText: `Chưa đạt (cần thêm ${needMore} SP, tối thiểu ${buyQty} SP)`,
                    claimedItems: [],
                    qualifyingGifts: [],
                    totalFree: 0,
                    freeProd: null,
                    giftLabel: '',
                    discountPct: 0,
                    discountAmt: 0
                };
            }

            if (minVal > 0 && evalSubtotal < minVal) {
                const needAmt = Math.max(0, minVal - evalSubtotal);
                return {
                    eligible: false,
                    reason: `Thiếu ${this.formatNumber(needAmt)}đ`,
                    benefitText: '',
                    statusText: `Chưa đạt (cần mua thêm ${this.formatNumber(needAmt)} đ nhóm SP này)`,
                    claimedItems: [],
                    qualifyingGifts: [],
                    totalFree: 0,
                    freeProd: null,
                    giftLabel: '',
                    discountPct: 0,
                    discountAmt: 0
                };
            }

            if (policy.policy_type === 'percentage') {
                const discPct = Number(policy.discount_percent) || 0;
                const estAmt = Math.round((evalSubtotal * discPct) / 100);
                return {
                    eligible: discPct > 0 && evalSubtotal > 0,
                    reason: '',
                    benefitText: `CK -${discPct}%`,
                    statusText: `Đạt: Chiết khấu ${discPct}% (-${this.formatNumber(estAmt)} đ)`,
                    claimedItems: evalItems,
                    qualifyingGifts: [],
                    totalFree: 0,
                    freeProd: null,
                    giftLabel: '',
                    discountPct: discPct,
                    discountAmt: estAmt
                };
            }

            if (policy.policy_type === 'amount') {
                const discAmt = Math.min(evalSubtotal, Number(policy.discount_amount) || 0);
                return {
                    eligible: discAmt > 0 && evalSubtotal > 0,
                    reason: '',
                    benefitText: `-${this.formatNumber(discAmt)}đ`,
                    statusText: `Đạt: Giảm trực tiếp ${this.formatNumber(discAmt)} đ`,
                    claimedItems: evalItems,
                    qualifyingGifts: [],
                    totalFree: 0,
                    freeProd: null,
                    giftLabel: '',
                    discountPct: 0,
                    discountAmt: discAmt
                };
            }

            return { eligible: false, reason: 'Chưa hỗ trợ', benefitText: '', statusText: 'Không hợp lệ', claimedItems: [], qualifyingGifts: [], totalFree: 0, freeProd: null, giftLabel: '', discountPct: 0, discountAmt: 0 };
        },

        formatPolicyOptionLabel(policy) {
            if (!policy) return '';
            const unclaimedItems = (this.items || []).filter(it =>
                !it.isPromotionalGift &&
                it.checked !== false &&
                it.product_id &&
                !it._claimedByPolicyId
            );
            const ev = this.evaluatePolicyBenefitsOnItems(policy, unclaimedItems);
            const prefix = ev.eligible ? `[Đạt${ev.benefitText ? ': ' + ev.benefitText : ''}]` : `[${ev.reason}]`;
            return `${prefix} ${policy.name}`;
        },

        getPolicyEvaluation(policy) {
            if (!policy) {
                return { eligible: false, reason: 'Chưa xác định', benefitText: '', statusText: '', totalMatchedQty: 0, matchedSubtotal: 0, matchingItems: [], totalFree: 0, freeProd: null, giftLabel: '', discountAmt: 0, discountPct: 0 };
            }
            const activeItems = (this.items || []).filter(it => !it.isPromotionalGift && it.checked !== false && it.product_id);
            const ev = this.evaluatePolicyBenefitsOnItems(policy, activeItems);
            return {
                ...ev,
                totalMatchedQty: (ev.claimedItems || []).reduce((s, it) => s + (Number(it.quantity) || 0), 0),
                matchingItems: ev.claimedItems || []
            };
        },

        getItemPolicyEvaluation(item, policy) {
            if (!item || !policy) {
                return { eligible: false, reason: 'Chưa xác định', benefitText: '', statusText: '', totalFree: 0, freeProd: null, giftLabel: '', discountAmt: 0, discountPct: 0 };
            }
            const ev = this.evaluatePolicyBenefitsOnItems(policy, [item]);
            return {
                ...ev,
                totalMatchedQty: (Number(item.quantity) || 0),
                matchingItems: [item]
            };
        },

        getApplicableOrderPolicies() {
            const unclaimedItems = (this.items || []).filter(it => !it.isPromotionalGift && it.checked !== false && it.product_id && !it._claimedByPolicyId);
            return (this.salesPoliciesCatalog || []).filter(p => {
                if (!this.isCustomerEligibleForPolicy(p)) return false;
                return this.evaluatePolicyBenefitsOnItems(p, unclaimedItems).eligible === true;
            });
        },

        getOrderScopeApplicablePolicies() {
            return (this.salesPoliciesCatalog || []).filter(p => {
                if (p.apply_scope !== 'order') return false;
                if (this.formData.order_policy_id && String(p.id) === String(this.formData.order_policy_id)) return true;
                return this.getPolicyEvaluation(p).eligible === true;
            });
        },

        getApplicablePoliciesForProduct(item) {
            if (!item || !item.product_id) return [];
            return (this.salesPoliciesCatalog || []).filter(p => {
                return this.getItemPolicyEvaluation(item, p).eligible === true;
            });
        },

        formatItemPolicyOptionLabel(policy, item) {
            if (!policy) return '';
            const ev = this.getItemPolicyEvaluation(item, policy);
            const prefix = ev.eligible ? `[Đạt${ev.benefitText ? ': ' + ev.benefitText : ''}]` : `[${ev.reason}]`;
            return `${prefix} ${policy.name}`;
        },

        getApplicableOrderPoliciesForSlot(slotIndex) {
            return this.getAvailablePoliciesToAdd();
        },

        getRemainingAddablePolicies() {
            return this.getAvailablePoliciesToAdd();
        },

        getSelectedPoliciesSummary() {
            const ids = (this.formData.sales_policy_ids || []).filter(Boolean);
            return ids.map(id => {
                const p = this.getSalesPolicyById(id);
                if (!p) return null;
                const ev = this.getPolicyEvaluation(p);
                return {
                    id: p.id,
                    name: p.name,
                    eligible: ev.eligible,
                    statusText: ev.statusText,
                };
            }).filter(Boolean);
        },

        sanitizeSelectedPoliciesForCustomer() {
            if (!Array.isArray(this.formData.sales_policy_ids)) {
                this.formData.sales_policy_ids = [];
            }
            const oldIds = [...this.formData.sales_policy_ids];
            const filtered = oldIds.filter(id => {
                if (!id) return false;
                const p = this.getSalesPolicyById(id);
                return p && this.isCustomerEligibleForPolicy(p);
            });
            oldIds.forEach(oldId => {
                if (oldId && !filtered.includes(oldId)) {
                    this.cleanupEffectsOfRemovedPolicy(oldId);
                }
            });
            this.formData.sales_policy_ids = filtered;
            this.formData.sales_policy_id = filtered[0] || '';
            this.evaluateOrderPolicies();
        },

        rebindSavedOrderPolicies() {
            if (!this.orderId) return;
            const ids = (this.formData.sales_policy_ids || []).filter(Boolean);
            if (ids.length === 0) return;
            this.evaluateOrderPolicies();
        },

        onItemPolicyChange(item, policyId) {
            if (!item) return;
            if (policyId) {
                this.addPolicyById(policyId);
            } else if (item.selected_policy_id) {
                this.removeSelectedPolicy(item.selected_policy_id);
            }
        },

        onOrderPolicyChange(policyId) {
            if (policyId) {
                this.addPolicyById(policyId);
            } else if (this.formData.order_policy_id) {
                this.removeSelectedPolicy(this.formData.order_policy_id);
            }
        },

        onPrimaryOrderPolicyChange(newId) {
            if (newId) this.addPolicyById(newId);
        },

        onExtraOrderPolicyChange(slotIndex, newId) {
            if (newId) this.addPolicyById(newId);
        },

        addAnotherPolicySlot() {
            // No-op kept for backwards compatibility
        },

        removePolicySlot(slotIndex) {
            // No-op kept for backwards compatibility
        },

        cleanupEffectsOfRemovedItemPolicy(item, policyId) {
            if (!policyId) return;
            this.cleanupEffectsOfRemovedPolicy(policyId);
        },

        cleanupEffectsOfRemovedPolicy(policyId) {
            if (!policyId) return;
            const pidStr = String(policyId);
            const p = this.getSalesPolicyById(policyId);

            for (let i = this.items.length - 1; i >= 0; i--) {
                const g = this.items[i];
                if (g.isPromotionalGift && (String(g.promoPolicyId || '') === pidStr || (g._giftKey && g._giftKey.startsWith(`order_policy_${pidStr}`)) || (p && (g.appliedTierPolicy || '').includes(p.name)))) {
                    this.items.splice(i, 1);
                }
            }
            (this.items || []).forEach(it => {
                if (!it.isPromotionalGift && String(it._appliedPromoPolicyId || '') === pidStr) {
                    it.discount_percent = 0;
                    it.discount_amount = 0;
                    it._autoPromoDiscount = false;
                    it._manualDiscount = false;
                    it._appliedPromoPolicyId = null;
                    it.amount = Math.max(0, Math.round((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)));
                }
                if (String(it._claimedByPolicyId || '') === pidStr) {
                    it._claimedByPolicyId = null;
                }
            });
            if (p && p.apply_scope === 'order' && (p.policy_type === 'percentage' || p.policy_type === 'amount') && this._autoOrderDiscount) {
                this.orderDiscountPercent = 0;
                this.formData.discount_amount = 0;
                this._autoOrderDiscount = false;
            }
        },

        evaluateOrderPolicies() {
            if (this._isApplyingOrderPolicies) return;
            this._isApplyingOrderPolicies = true;

            try {
                const activeGiftKeys = [];
                const salesItems = this.getSalesItemsWithProduct();

                // 1. Reset trạng thái chiếm dụng và chiết khấu tự động trên các dòng hàng
                salesItems.forEach(it => {
                    it._claimedByPolicyId = null;
                    if (it._autoPromoDiscount && !it._manualDiscount) {
                        it.discount_percent = 0;
                        it.discount_amount = 0;
                        it._autoPromoDiscount = false;
                        it._appliedPromoPolicyId = null;
                        it.amount = Math.max(0, Math.round((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)));
                    }
                });

                if (this._autoOrderDiscount && !this._manualOrderDiscount) {
                    this.orderDiscountPercent = 0;
                    this.formData.discount_amount = 0;
                    this._autoOrderDiscount = false;
                }

                // 2. Duyệt tuần tự các chính sách được chọn để chiếm dụng sản phẩm độc quyền
                let remainingItems = [...salesItems];
                const validSelectedPolicyIds = [];
                const selectedIds = Array.isArray(this.formData.sales_policy_ids) ? [...this.formData.sales_policy_ids] : [];

                for (const pid of selectedIds) {
                    const policy = this.getSalesPolicyById(pid);
                    if (!policy) continue;

                    const ev = this.evaluatePolicyBenefitsOnItems(policy, remainingItems);
                    if (ev.eligible && ev.claimedItems && ev.claimedItems.length > 0) {
                        validSelectedPolicyIds.push(String(policy.id));
                        const claimedSet = new Set(ev.claimedItems);
                        ev.claimedItems.forEach(it => {
                            it._claimedByPolicyId = policy.id;
                            it.selected_policy_id = policy.id;
                        });
                        remainingItems = remainingItems.filter(it => !claimedSet.has(it));

                        if (policy.policy_type === 'quantity') {
                            (ev.qualifyingGifts || []).forEach(gInfo => {
                                const freeProd = gInfo.prod || this.getProduct(gInfo.product_id);
                                const freeSku = freeProd?.sku || 'KM-SP';
                                const freeName = freeProd?.name || 'Hàng tặng khuyến mại';
                                const freeUnit = freeProd?.unit || 'cái';
                                const giftKey = `order_policy_${policy.id}_${gInfo.product_id}`;
                                activeGiftKeys.push(giftKey);

                                const existing = this.items.find(g => g.isPromotionalGift && (g._giftKey === giftKey || (String(g.promoPolicyId || '') === String(policy.id) && String(g.product_id || '') === String(freeProd?.id || gInfo.product_id))));
                                if (existing) {
                                    existing._giftKey = giftKey;
                                    existing.promoPolicyId = policy.id;
                                    if (!existing._manualQty) {
                                        existing.quantity = gInfo.qty;
                                    }
                                    existing.sku = freeSku;
                                    existing.name = freeName;
                                    existing.searchQuery = freeName;
                                    existing.unit = freeUnit;
                                    existing.appliedTierPolicy = gInfo.giftLabel;
                                } else {
                                    this.items.push({
                                        _rowKey: 'gift-' + Date.now() + '-' + Math.random(),
                                        id: '',
                                        itemId: freeProd?.id ? String(freeProd.id) : String(gInfo.product_id || ''),
                                        product_id: freeProd?.id || gInfo.product_id || '',
                                        sku: freeSku,
                                        name: freeName,
                                        searchQuery: freeName,
                                        unit: freeUnit,
                                        quantity: gInfo.qty,
                                        gift_quantity: 0,
                                        unitPrice: 0,
                                        originalPrice: 0,
                                        costPrice: Number(freeProd?.cost_price || 0),
                                        discount_percent: 0,
                                        discount_amount: 0,
                                        vat_percent: 0,
                                        vat_amount: 0,
                                        appliedTierPolicy: gInfo.giftLabel,
                                        price_type: 'Khuyến mại',
                                        amount: 0,
                                        checked: true,
                                        isPromotionalGift: true,
                                        promoPolicyId: policy.id,
                                        _giftKey: giftKey,
                                    });
                                }
                                this.showPromoSection = true;
                            });
                        } else if (policy.policy_type === 'percentage') {
                            ev.claimedItems.forEach(it => {
                                if (!it._manualDiscount) {
                                    const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                                    it.discount_percent = ev.discountPct;
                                    it.discount_amount = Math.round((lineBase * ev.discountPct) / 100);
                                    it.amount = Math.max(0, Math.round(lineBase - it.discount_amount));
                                    it._autoPromoDiscount = true;
                                    it._appliedPromoPolicyId = policy.id;
                                }
                            });
                        } else if (policy.policy_type === 'amount') {
                            const totalBase = ev.claimedItems.reduce((sum, it) => sum + ((Number(it.quantity) || 0) * (Number(it.unitPrice) || 0)), 0);
                            let remainingDiscAmt = ev.discountAmt;
                            ev.claimedItems.forEach((it, idx) => {
                                if (!it._manualDiscount) {
                                    const lineBase = (Number(it.quantity) || 0) * (Number(it.unitPrice) || 0);
                                    let itemDiscAmt = 0;
                                    if (idx === ev.claimedItems.length - 1) {
                                        itemDiscAmt = remainingDiscAmt;
                                    } else if (totalBase > 0) {
                                        itemDiscAmt = Math.min(lineBase, Math.round((lineBase / totalBase) * ev.discountAmt));
                                        remainingDiscAmt -= itemDiscAmt;
                                    }
                                    it.discount_amount = itemDiscAmt;
                                    it.discount_percent = lineBase > 0 ? Math.round((itemDiscAmt / lineBase) * 100 * 10) / 10 : 0;
                                    it.amount = Math.max(0, Math.round(lineBase - it.discount_amount));
                                    it._autoPromoDiscount = true;
                                    it._appliedPromoPolicyId = policy.id;
                                }
                            });
                        }
                    }
                }

                // 3. Cập nhật mảng formData.sales_policy_ids & formData.sales_policy_id
                this.formData.sales_policy_ids = validSelectedPolicyIds;
                this.formData.sales_policy_id = validSelectedPolicyIds[0] || '';

                // 4. Xóa các dòng quà tặng tự sinh không còn thỏa điều kiện
                for (let i = this.items.length - 1; i >= 0; i--) {
                    const g = this.items[i];
                    if (g.isPromotionalGift && g._giftKey && !activeGiftKeys.includes(g._giftKey)) {
                        this.items.splice(i, 1);
                    }
                }
                if (this.getGiftItems().length === 0) {
                    const hasManualPromoSection = (this.items || []).some(it => it.isPromotionalGift);
                    if (!hasManualPromoSection) {
                        this.showPromoSection = false;
                    }
                }

                this.calculateTotals();
            } finally {
                this._isApplyingOrderPolicies = false;
            }
        },
    };

    function attachMethodsToInstance(instance) {
        if (!instance) return;
        for (const key of Object.keys(OrderPolicyMethods)) {
            instance[key] = OrderPolicyMethods[key];
        }
    }

    // 1. Intercept window.orderFormApp so returned instance always has policy methods
    let _origOrderFormApp = window.orderFormApp;
    try {
        Object.defineProperty(window, 'orderFormApp', {
            configurable: true,
            enumerable: true,
            get() {
                return function() {
                    const app = _origOrderFormApp ? _origOrderFormApp.apply(this, arguments) : {};
                    attachMethodsToInstance(app);
                    return app;
                };
            },
            set(fn) {
                _origOrderFormApp = fn;
            }
        });
    } catch(e) {
        if (typeof window.orderFormApp === 'function') {
            const prev = window.orderFormApp;
            window.orderFormApp = function() {
                const app = prev.apply(this, arguments);
                attachMethodsToInstance(app);
                return app;
            };
        }
    }

    // 2. Global fallbacks on window so template expressions NEVER throw ReferenceError
    window.getSelectedPolicies = function() {
        const app = getOrderApp();
        return app && typeof app.getSelectedPolicies === 'function' ? app.getSelectedPolicies() : [];
    };
    window.getAvailablePoliciesToAdd = function() {
        const app = getOrderApp();
        return app && typeof app.getAvailablePoliciesToAdd === 'function' ? app.getAvailablePoliciesToAdd() : [];
    };
    window.getPolicyBenefitDescription = function(p) {
        const app = getOrderApp();
        return app && typeof app.getPolicyBenefitDescription === 'function' ? app.getPolicyBenefitDescription(p) : '';
    };
    window.getPolicyAppliedProductsSummary = function(p) {
        const app = getOrderApp();
        return app && typeof app.getPolicyAppliedProductsSummary === 'function' ? app.getPolicyAppliedProductsSummary(p) : '';
    };
    window.formatPolicyOptionLabel = function(p) {
        const app = getOrderApp();
        return app && typeof app.formatPolicyOptionLabel === 'function' ? app.formatPolicyOptionLabel(p) : '';
    };
    window.clearAllOrderPolicies = function() {
        const app = getOrderApp();
        if (app && typeof app.clearAllOrderPolicies === 'function') app.clearAllOrderPolicies();
    };
    window.addPolicyById = function(id) {
        const app = getOrderApp();
        if (app && typeof app.addPolicyById === 'function') app.addPolicyById(id);
    };
    window.removeSelectedPolicy = function(id) {
        const app = getOrderApp();
        if (app && typeof app.removeSelectedPolicy === 'function') app.removeSelectedPolicy(id);
    };

    // 3. Auto-attach on DOMContentLoaded or Alpine initialization
    function ensureAttached() {
        const app = getOrderApp();
        if (app) attachMethodsToInstance(app);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureAttached);
    } else {
        ensureAttached();
    }
    document.addEventListener('alpine:initialized', ensureAttached);
})();
