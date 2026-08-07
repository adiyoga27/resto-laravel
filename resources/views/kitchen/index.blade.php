@extends('layouts.app')
@section('title', 'Panel Dapur')

@push('scripts')
<script>
let polling;
function fetchOrders(){
    fetch('{{ route("kitchen.orders") }}').then(r => r.json()).then(orders => {
        const c = $('#ordersContainer');
        if(!orders.length){ c.html('<div class="col-12 text-center py-5 text-muted"><i class="bi bi-check-circle display-4"></i><p class="mt-2">Semua order selesai!</p></div>'); return; }
        c.html(orders.map(o => `
            <div class="col-md-6 col-lg-4">
                <div class="card kitchen-order status-${o.order_status} shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-bold">#${o.order_number}</span>
                                <span class="badge bg-secondary order-type-badge ml-1">${o.order_type}</span>
                                ${o.restaurant_table ? '<span class="badge bg-info order-type-badge ml-1">Meja '+o.restaurant_table.table_number+'</span>' : ''}
                            </div>
                            <span class="badge bg-${o.order_status==='baru'?'primary':o.order_status==='diproses'?'warning':'success'}">${o.order_status}</span>
                        </div>
                        <ul class="list-group list-group-flush small mb-2">
                            ${o.order_items.map(i => `<li class="list-group-item px-0 d-flex justify-content-between"><span>${i.quantity}x ${i.menu_item?.name ?? 'Menu'}</span>${i.notes ? '<small class="text-muted">('+i.notes+')</small>' : ''}</li>`).join('')}
                        </ul>
                        ${o.notes ? '<small class="text-muted">📝 '+o.notes+'</small>' : ''}
                        <div class="mt-3 d-flex">
                            ${o.order_status==='baru' ? '<button class="btn btn-warning btn-sm w-100" onclick="updateStatus('+o.id+',\'diproses\')"><i class="bi bi-play-fill mr-1"></i>Mulai Masak</button>' : ''}
                            ${o.order_status==='diproses' ? '<button class="btn btn-success btn-sm w-100" onclick="updateStatus('+o.id+',\'siap\')"><i class="bi bi-check-lg mr-1"></i>Siap</button>' : ''}
                            ${o.order_status==='siap' ? '<button class="btn btn-primary btn-sm w-100" onclick="updateStatus('+o.id+',\'selesai\')"><i class="bi bi-truck mr-1"></i>Antarkan</button>' : ''}
                        </div>
                    </div>
                </div>
            </div>`).join(''));
    });
}
function updateStatus(id, status){
    fetch('/kitchen/orders/'+id+'/status', {
        method:'PATCH', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({status})
    }).then(r => r.json()).then(() => fetchOrders());
}
$(function(){ fetchOrders(); polling = setInterval(fetchOrders, 5000); });
$(document).on('visibilitychange', function(){ if(document.hidden) clearInterval(polling); else { fetchOrders(); polling = setInterval(fetchOrders, 5000); } });
</script>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <small class="text-muted"><i class="bi bi-arrow-repeat mr-1"></i>Auto-refresh setiap 5 detik</small>
</div>
<div id="ordersContainer" class="row g-4">
    <div class="col-12 text-center py-5 text-muted"><i class="bi bi-hourglass-split display-4"></i><p class="mt-2">Memuat order...</p></div>
</div>
@endsection
