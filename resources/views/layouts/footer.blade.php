@php
    $footerShop = auth()->user()?->shops()?->first();
@endphp
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">{{ date('Y') }} © CF Manager.</div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">CF Manager Operations Console #{{ $footerShop?->id ?: auth()->id() }}</div>
            </div>
        </div>
    </div>
</footer>
