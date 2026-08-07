@extends('layouts.app')
@section('title', 'POS Kasir')
@push('styles')
<style>
    .pos-wrapper {
        height: calc(100vh - 120px);
        gap: 0;
    }
    .pos-left {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        padding-right: 0;
    }
    .pos-right {
        width: 420px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
    }

    /* Order type pills */
    .order-type-pills {
        display: flex;
        gap: 8px;
    }
    .order-type-pill {
        flex: 1;
        padding: 10px 16px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background: #fff;
        font-weight: 600;
        font-size: 0.85rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .order-type-pill.active {
        border-color: #6366f1;
        background: #eef2ff;
        color: #4338ca;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.15);
    }
    .order-type-pill:not(.locked):hover {
        border-color: #a5b4fc;
        color: #6366f1;
    }
    .order-type-pill.locked {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
        border-color: #e2e8f0;
        pointer-events: none;
    }

    /* Search bar */
    .search-box {
        position: relative;
    }
    .search-box input {
        padding-left: 40px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        height: 44px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .search-box input:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .search-box i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.95rem;
    }

    /* Menu item card */
    .menu-item-card {
        border: 2px solid #f1f5f9;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
    }
    .menu-item-card:hover {
        border-color: #6366f1;
        box-shadow: 0 4px 16px rgba(99, 102, 241, 0.12);
        transform: translateY(-2px);
    }
    .menu-item-card:active {
        transform: scale(0.97);
    }
    .menu-img-wrapper {
        height: 90px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .menu-item-info {
        padding: 10px;
    }
    .menu-item-name {
        font-weight: 700;
        font-size: 0.85rem;
        color: #1e293b;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .menu-item-price {
        font-weight: 700;
        font-size: 0.8rem;
        color: #6366f1;
        background: #eef2ff;
        padding: 2px 10px;
        border-radius: 20px;
        display: inline-block;
    }

    /* Category header */
    .category-header {
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding-bottom: 8px;
        margin-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .category-header .badge-count {
        font-size: 0.7rem;
        background: #f1f5f9;
        color: #64748b;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
    }

    /* Table buttons */
    .table-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .table-btn {
        padding: 6px 14px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .table-btn:hover {
        border-color: #6366f1;
        color: #6366f1;
    }
    .table-btn.selected {
        background: #6366f1;
        color: #fff;
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
    }
    .table-btn.occupied {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }
    .table-btn.occupied:hover {
        border-color: #f59e0b;
        color: #92400e;
    }
    .table-btn.occupied.selected {
        background: #f59e0b;
        color: #fff;
        border-color: #f59e0b;
    }

    /* Cart */
    .cart-section {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .cart-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .cart-body {
        flex: 1;
        overflow-y: auto;
        padding: 12px 16px;
    }
    .cart-footer {
        padding: 16px 20px;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
    }
    .cart-item {
        padding: 12px;
        border-radius: 10px;
        background: #fff;
        border: 1px solid #f1f5f9;
        margin-bottom: 8px;
        transition: all 0.15s ease;
    }
    .cart-item:hover {
        border-color: #e2e8f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }
    .cart-qty-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #6366f1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.15s ease;
        padding: 0;
    }
    .cart-qty-btn:hover {
        background: #eef2ff;
        border-color: #6366f1;
    }
    .cart-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #cbd5e1;
    }
    .cart-empty i {
        font-size: 3.5rem;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    /* Summary */
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        font-size: 0.85rem;
        color: #64748b;
    }
    .summary-divider {
        border-top: 2px dashed #e2e8f0;
        margin: 10px 0;
    }
    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
    }
    .summary-total .amount {
        color: #6366f1;
        font-size: 1.25rem;
    }

    /* Form controls */
    .form-control-sm {
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }
    .form-control-sm:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    select.form-control-sm {
        cursor: pointer;
    }

    .btn-process {
        border-radius: 10px;
        padding: 12px;
        font-weight: 700;
        font-size: 0.95rem;
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
        transition: all 0.2s ease;
        color: #fff;
    }
    .btn-process:hover {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        transform: translateY(-1px);
    }
    .btn-process:disabled {
        background: #cbd5e1;
        box-shadow: none;
        transform: none;
        cursor: not-allowed;
    }

    /* Card wrappers */
    .card-pos {
        border: none;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        background: #fff;
    }

    .order-type-section {
        padding: 14px 20px;
    }
    .order-type-label {
        font-weight: 700;
        font-size: 0.75rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
        display: block;
    }

    .info-section {
        margin-top: 12px;
    }

    /* Scrollbar */
    .cart-body::-webkit-scrollbar,
    .menu-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .cart-body::-webkit-scrollbar-track,
    .menu-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .cart-body::-webkit-scrollbar-thumb,
    .menu-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
</style>
@endpush
@push('styles')
<style>
    @keyframes modalIn {
        from { opacity:0; transform:scale(0.9) translateY(20px); }
        to { opacity:1; transform:scale(1) translateY(0); }
    }
</style>
@endpush
@push('scripts')
<script>
let cart = [], selectedTable = null, selectedOrderType = 'dine-in';
let discount = 0, editingOrderId = null;

$(document).ready(function(){
    updateOrderTypeUI();
    renderCart();

    @if(session('success_order_id'))
    $('#modalOrderNumber').text('#{{ session('success_order_number') }}');
    $('#modalTotal').text('Rp {{ number_format(session('success_order_total'),0,',','.') }}');
    $('#modalItems').text('{{ session('success_order_items') }} item');
    $('#successOverlay').css('display', 'flex');
    @endif
});

function setOrderType(type) {
    selectedOrderType = type;
    updateOrderTypeUI();
    $('#tableSection').toggle(type === 'dine-in');
    if (type !== 'dine-in') {
        selectedTable = null;
        $('.table-btn').removeClass('selected');
    }
    renderCart();
}

function updateOrderTypeUI() {
    $('.order-type-pill').removeClass('active');
    $('.order-type-pill[data-type="' + selectedOrderType + '"]').addClass('active');
}

$(document).on('change', '#paymentMethod', function(){
    $('#transferFields').toggleClass('d-none', $(this).val() !== 'transfer');
});

$(document).on('click', '.menu-item-card', function(){
    const id = $(this).data('id'), name = $(this).data('name'), price = parseFloat($(this).data('price'));
    const existing = cart.find(i => i.id === id);
    if (existing) existing.qty++; else cart.push({id, name, price, qty:1, notes:''});
    const el = $(this);
    el.css('transform', 'scale(0.95)');
    setTimeout(() => el.css('transform', ''), 150);
    renderCart();
});

$(document).on('click', '.table-btn', function(){
    $('.table-btn').removeClass('selected');
    $(this).addClass('selected');
    selectedTable = $(this).data('id');
});

$(document).on('input', '#menuSearch', function(){
    const q = $(this).val().toLowerCase();
    $('.menu-item-wrapper').each(function(){ $(this).toggle($(this).data('name').toLowerCase().includes(q)); });
    $('.category-section').each(function(){ $(this).toggle($(this).find('.menu-item-wrapper:visible').length > 0); });
});

$(document).on('input', '#discountInput', function(){
    discount = parseFloat($(this).val()) || 0;
    renderCart();
});

function renderCart(){
    let html = '', subtotal = 0, count = 0;
    cart.forEach((item, i) => {
        const sub = item.price * item.qty;
        subtotal += sub;
        count += item.qty;
        html += `<div class="cart-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="font-weight-bold" style="font-size:0.95rem;color:#334155;">${item.name}</div>
                <div class="font-weight-bold" style="color:#6366f1;font-size:0.9rem;">Rp ${sub.toLocaleString()}</div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center" style="gap:10px;">
                    <button type="button" class="cart-qty-btn" onclick="updateQty(${i},-1)"><i class="fas fa-minus" style="font-size:10px;"></i></button>
                    <span class="font-weight-bold" style="font-size:1rem;width:24px;text-align:center;">${item.qty}</span>
                    <button type="button" class="cart-qty-btn" onclick="updateQty(${i},1)"><i class="fas fa-plus" style="font-size:10px;"></i></button>
                </div>
                <button type="button" class="btn btn-link text-danger p-0" onclick="removeItem(${i})" style="font-size:0.85rem;"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>`;
    });
    $('#cartItemsList').html(html || `<div class="cart-empty">
        <i class="fas fa-shopping-basket"></i>
        <h6 class="font-weight-bold mb-1" style="color:#94a3b8;">Belum Ada Pesanan</h6>
        <span class="small" style="color:#cbd5e1;">Klik menu untuk menambah pesanan.</span>
    </div>`);

    $('#cartCount').text(count + ' item' + (count !== 1 ? 's' : ''));
    const afterDiscount = Math.max(0, subtotal - discount);
    const tax = afterDiscount * 0.11;
    const total = afterDiscount + tax;
    $('#cartSubtotal').text('Rp ' + subtotal.toLocaleString());

    if (discount > 0) {
        $('#discountRow').removeClass('d-none');
        $('#cartDiscount').text('- Rp ' + discount.toLocaleString());
    } else {
        $('#discountRow').addClass('d-none');
    }

    $('#cartTax').text('Rp ' + tax.toLocaleString());
    $('#cartTotal').text('Rp ' + total.toLocaleString());
    $('#itemsJson').val(JSON.stringify(cart.map(i => ({menu_item_id:i.id, quantity:i.qty, notes:i.notes}))));
    $('#submitBtn').prop('disabled', cart.length === 0);
}
window.updateQty = function(i,d){ cart[i].qty += d; if(cart[i].qty <= 0) cart.splice(i,1); renderCart(); };
window.removeItem = function(i){ cart.splice(i,1); renderCart(); };

function submitOrder(){
    if (!selectedOrderType) { alert('Pilih tipe order terlebih dahulu'); return false; }
    if (selectedOrderType === 'dine-in' && !selectedTable) { alert('Pilih meja untuk dine-in'); return false; }
    $('#orderTypeInput').val(selectedOrderType);
    $('#tableIdInputForm').val(selectedTable);
    $('#customerNameForm').val($('#customerName').val());
    $('#customerPhoneForm').val($('#customerPhone').val());
    $('#discountHidden').val(discount);
    $('#paymentMethodInput').val($('#paymentMethod').val());
    let bankInfo = '';
    if ($('#paymentMethod').val() === 'transfer') {
        bankInfo = $('#bankName').val() + ' - ' + $('#accountNumber').val();
    }
    $('#paymentReferenceInput').val(bankInfo);
    const subtotal = cart.reduce((s,i) => s + (i.price * i.qty), 0);
    const afterDiscount = Math.max(0, subtotal - discount);
    const total = afterDiscount * 1.11;
    $('#paymentAmount').val(total);
    return true;
}

function editOrder(orderId, items) {
    editingOrderId = orderId;
    cart = items.map(i => ({id: i.id, name: i.name, price: i.price, qty: i.qty, notes: ''}));
    discount = 0;
    $('#discountInput').val(0);
    $('#orderForm').attr('action', '/pos/orders/' + orderId);
    $('#methodField').html('<input type="hidden" name="_method" value="PUT">');
    $('#cartTitle').text('Edit Pesanan');
    $('#submitBtnText').text('Update Order');
    $('#resetCartBtn').removeClass('d-none');
    renderCart();
}

function resetCart() {
    editingOrderId = null;
    cart = [];
    discount = 0;
    selectedTable = null;
    $('.table-btn').removeClass('selected');
    $('#discountInput').val(0);
    $('#orderForm').attr('action', '{{ route('pos.orders.store') }}');
    $('#methodField').html('');
    $('#cartTitle').text('Pesanan Baru');
    $('#submitBtnText').text('Proses Order');
    $('#resetCartBtn').addClass('d-none');
    $('#customerName').val('');
    $('#customerPhone').val('');
    $('#notes').val('');
    updateOrderTypeUI();
    $('#tableSection').show();
    renderCart();
}

function printOrder() {
    window.print();
}

function closeSuccessModal() {
    $('#successOverlay').css('display', 'none');
    resetCart();
}
</script>
@endpush
@section('content')
<div class="d-flex pos-wrapper">
    {{-- Left: Menu Area --}}
    <div class="pos-left">
        <div class="card-pos mb-3">
            <div class="order-type-section">
                <span class="order-type-label">Tipe Order</span>
                <div class="order-type-pills">
                    <div class="order-type-pill active" data-type="dine-in" onclick="setOrderType('dine-in')">
                        <i class="fas fa-utensils"></i> Dine In
                    </div>
                    <div class="order-type-pill" data-type="delivery" onclick="setOrderType('delivery')">
                        <i class="fas fa-motorcycle"></i> Delivery
                    </div>
                    <div class="order-type-pill" data-type="pickup" onclick="setOrderType('pickup')">
                        <i class="fas fa-shopping-bag"></i> Pickup
                    </div>
                </div>

                <div id="tableSection" class="info-section">
                    <label class="font-weight-bold small text-muted mb-2 d-block" style="text-transform:uppercase;letter-spacing:0.05em;">Pilih Meja</label>
                    <div class="table-grid">
                        @forelse($tables as $table)
                        <button type="button" data-id="{{ $table->id }}" class="table-btn {{ $table->status->value !== 'kosong' ? 'occupied' : '' }}">
                            <i class="fas fa-chair mr-1"></i> {{ $table->table_number }}
                            <small>({{ $table->capacity }})</small>
                            @if($table->status->value !== 'kosong')
                            <span class="ml-1" style="font-size:0.65rem;opacity:0.8;">&#9679;</span>
                            @endif
                        </button>
                        @empty
                        <span class="text-muted small">Tidak ada meja tersedia</span>
                        @endforelse
                    </div>
                </div>
                <div id="customerSection" class="info-section mb-3">
                    <label class="font-weight-bold small text-muted mb-2 d-block" style="text-transform:uppercase;letter-spacing:0.05em;">Info Customer</label>
                    <div class="row">
                        <div class="col-6"><input type="text" id="customerName" class="form-control form-control-sm" placeholder="Nama"></div>
                        <div class="col-6"><input type="text" id="customerPhone" class="form-control form-control-sm" placeholder="No. HP"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-pos flex-grow-1 d-flex flex-column overflow-hidden">
            <div style="padding:12px 20px;border-bottom:1px solid #f1f5f9;">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="menuSearch" class="form-control" placeholder="Cari menu...">
                </div>
            </div>
            <div class="flex-grow-1 overflow-auto menu-scroll" style="padding:16px 20px;">
                @foreach($menuItems as $categoryName => $items)
                <div class="category-section mb-4">
                    <div class="category-header">
                        <i class="fas fa-tag"></i> {{ $categoryName }}
                        <span class="badge-count">{{ $items->count() }}</span>
                    </div>
                    <div class="row" style="--bs-gutter-x:10px;--bs-gutter-y:10px;">
                        @foreach($items as $item)
                        <div class="col-6 col-md-4 col-lg-3 mb-2 menu-item-wrapper" data-name="{{ $item->name }}">
                            <div class="menu-item-card h-100" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-price="{{ $item->price }}">
                                <div class="menu-img-wrapper">
                                    @if($item->image)
                                    <img src="{{ asset('storage/'.$item->image) }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                    <span style="font-size:2.8rem;">{{ ['🍜','🍛','🍗','🥩','🥤','☕','🍰','🍟','🍕','🥗'][$item->id%10] }}</span>
                                    @endif
                                </div>
                                <div class="menu-item-info text-center">
                                    <div class="menu-item-name" title="{{ $item->name }}">{{ Str::limit($item->name, 22) }}</div>
                                    <span class="menu-item-price">Rp {{ number_format($item->price,0,',','.') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Cart --}}
    <div class="pos-right" style="padding-left:16px;">
        {{-- Active Orders --}}
        @if($activeOrders->isNotEmpty())
        <div class="card-pos mb-2" id="activeOrdersPanel">
            <div class="d-flex justify-content-between align-items-center" style="padding:10px 16px;border-bottom:1px solid #f1f5f9;cursor:pointer;" onclick="$('#activeOrdersBody').slideToggle(200);$(this).find('i.fa-chevron-down, i.fa-chevron-up').toggleClass('fa-chevron-down fa-chevron-up');">
                <span class="font-weight-bold" style="font-size:0.85rem;color:#475569;">
                    <i class="fas fa-clipboard-list mr-2" style="color:#6366f1;"></i>Order Aktif
                </span>
                <span class="d-flex align-items-center" style="gap:8px;">
                    <span class="badge rounded-pill" style="background:#eef2ff;color:#4338ca;font-weight:700;font-size:0.7rem;">{{ $activeOrders->count() }}</span>
                    <i class="fas fa-chevron-down" style="font-size:0.7rem;color:#94a3b8;"></i>
                </span>
            </div>
            <div id="activeOrdersBody" style="display:none;">
                @foreach($activeOrders as $ord)
                <div class="d-flex justify-content-between align-items-center" style="padding:8px 16px;border-bottom:1px solid #f8fafc;">
                    <div style="min-width:0;">
                        <div class="font-weight-bold" style="font-size:0.8rem;color:#1e293b;">#{{ $ord->order_number }}</div>
                        <div style="font-size:0.7rem;color:#94a3b8;">
                            @if($ord->restaurantTable)
                                Meja {{ $ord->restaurantTable->table_number }}
                            @else
                                {{ $ord->order_type->label() }}
                            @endif
                            &middot; {{ $ord->orderItems->sum('quantity') }} item
                            &middot; <span style="color:#6366f1;font-weight:600;">Rp {{ number_format($ord->total,0,',','.') }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm"
                        style="background:#eef2ff;color:#4338ca;border:none;border-radius:6px;font-weight:600;font-size:0.72rem;padding:4px 10px;white-space:nowrap;"
                        onclick="editOrder({{ $ord->id }}, {{ Js::from($ord->orderItems->map(fn($i) => ['id' => $i->menu_item_id, 'name' => $i->menuItem->name, 'price' => (float) $i->price, 'qty' => $i->quantity])) }})">
                        <i class="fas fa-pen mr-1"></i>Lanjutkan
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card-pos cart-section">
            <div class="cart-header" style="flex-shrink:0;">
                <h6 class="mb-0 font-weight-bold" style="font-size:1rem;color:#1e293b;">
                    <i class="fas fa-shopping-cart mr-2" style="color:#6366f1;"></i>
                    <span id="cartTitle">Pesanan Baru</span>
                </h6>
                <div class="d-flex align-items-center" style="gap:8px;">
                    <button type="button" id="resetCartBtn" class="btn btn-sm d-none" style="background:#fef2f2;color:#dc2626;border:none;border-radius:6px;font-weight:600;font-size:0.72rem;padding:4px 10px;" onclick="resetCart()">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <span class="badge rounded-pill" style="background:#eef2ff;color:#4338ca;font-weight:700;padding:6px 12px;font-size:0.8rem;" id="cartCount">0 items</span>
                </div>
            </div>
            <div class="cart-body" id="cartItemsList"></div>
            <div class="cart-footer">
                <div class="mb-3">
                    <div class="summary-row"><span>Subtotal</span><span id="cartSubtotal">Rp 0</span></div>
                    <div class="summary-row d-none" id="discountRow"><span style="color:#ef4444;">Diskon</span><span style="color:#ef4444;" id="cartDiscount">-Rp 0</span></div>
                    <div class="summary-row"><span>Tax (11%)</span><span id="cartTax">Rp 0</span></div>
                    <div class="summary-divider"></div>
                    <div class="summary-total">
                        <span>Total</span>
                        <span class="amount" id="cartTotal">Rp 0</span>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label class="small font-weight-bold text-muted" style="text-transform:uppercase;letter-spacing:0.04em;font-size:0.7rem;">Diskon (Rp)</label>
                    <input type="number" id="discountInput" class="form-control form-control-sm" placeholder="0" min="0" value="0">
                </div>

                <div class="form-group mb-2">
                    <label class="small font-weight-bold text-muted" style="text-transform:uppercase;letter-spacing:0.04em;font-size:0.7rem;">Metode Pembayaran</label>
                    <select id="paymentMethod" class="form-control form-control-sm">
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>
                <div id="transferFields" class="d-none mb-2">
                    <div class="row">
                        <div class="col-6"><input type="text" id="bankName" class="form-control form-control-sm" placeholder="Nama Bank"></div>
                        <div class="col-6"><input type="text" id="accountNumber" class="form-control form-control-sm" placeholder="No. Rekening"></div>
                    </div>
                </div>

                <form method="POST" action="{{ route('pos.orders.store') }}" onsubmit="return submitOrder()" id="orderForm">
                    @csrf
                    <div id="methodField"></div>
                    <input type="hidden" name="order_type" id="orderTypeInput" value="dine-in">
                    <input type="hidden" name="restaurant_table_id" id="tableIdInputForm">
                    <input type="hidden" name="customer_name" id="customerNameForm">
                    <input type="hidden" name="customer_phone" id="customerPhoneForm">
                    <input type="hidden" name="items" id="itemsJson">
                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="cash">
                    <input type="hidden" name="payment_amount" id="paymentAmount">
                    <input type="hidden" name="discount" id="discountHidden" value="0">
                    <input type="hidden" name="payment_reference" id="paymentReferenceInput">
                    <textarea name="notes" rows="2" class="form-control form-control-sm mb-2" placeholder="Catatan pesanan..."></textarea>
                    <button type="submit" id="submitBtn" class="btn btn-process btn-block" disabled>
                        <i class="fas fa-check-circle mr-2"></i><span id="submitBtnText">Proses Order</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;" id="successOverlay">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" onclick="closeSuccessModal()"></div>
    <div style="position:relative;background:#fff;border-radius:16px;overflow:hidden;width:380px;max-width:92vw;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:modalIn 0.3s ease;">
        <div style="background:linear-gradient(135deg,#6366f1,#4f46e5);padding:24px;text-align:center;">
            <div style="width:64px;height:64px;background:rgba(255,255,255,0.2);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <i class="fas fa-check-circle" style="font-size:32px;color:#fff;"></i>
            </div>
            <h5 class="font-weight-bold mb-1" style="color:#fff;">Order Berhasil!</h5>
            <div style="color:rgba(255,255,255,0.85);font-size:0.9rem;" id="modalOrderNumber"></div>
        </div>
        <div style="padding:20px 24px;text-align:center;">
            <div class="d-flex justify-content-center mb-3" style="gap:32px;">
                <div>
                    <div class="small text-muted">Total</div>
                    <div class="font-weight-bold" style="font-size:1.1rem;color:#1e293b;" id="modalTotal"></div>
                </div>
                <div>
                    <div class="small text-muted">Item</div>
                    <div class="font-weight-bold" style="font-size:1.1rem;color:#1e293b;" id="modalItems"></div>
                </div>
            </div>
            <div class="d-flex" style="gap:10px;">
                <button type="button" class="btn btn-process btn-block" style="flex:1;font-size:0.85rem;" onclick="printOrder()">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
                <button type="button" class="btn btn-block" style="flex:1;font-size:0.85rem;background:#f1f5f9;color:#475569;border:none;border-radius:10px;font-weight:700;" onclick="closeSuccessModal()">
                    <i class="fas fa-redo mr-2"></i>Lanjut
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
