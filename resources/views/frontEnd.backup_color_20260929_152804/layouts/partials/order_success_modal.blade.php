<div class="modal fade show" id="orderSuccessModal" tabindex="-1" role="dialog" aria-labelledby="orderSuccessModalLabel" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 999999; display: flex; align-items: center; justify-content: center; overflow-x: hidden; overflow-y: auto; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); padding: 15px;" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg my-auto" role="document" style="max-width: 720px; width: 100%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden; background: #ffffff;">
            <!-- Header with Celebration Icon -->
            <div class="modal-header border-0 text-center flex-column pb-0 pt-4" style="background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);">
                <button type="button" class="btn-close close-order-modal position-absolute" style="top: 16px; right: 16px; cursor: pointer; z-index: 10; border: none; background: transparent;" aria-label="Close">
                    <i class="fa fa-times text-muted" style="font-size: 20px;"></i>
                </button>
                <div class="success-icon-wrapper mb-3" style="width: 76px; height: 76px; margin: 0 auto; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 8px #f0fdf4;">
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h4 class="modal-title font-weight-bold text-dark mb-1" id="orderSuccessModalLabel" style="font-size: 22px;">ধন্যবাদ! আপনার অর্ডারটি সফল হয়েছে</h4>
                <p class="text-muted font-14 mb-3">আমরা দ্রুততম সময়ে আপনার সাথে যোগাযোগ করে অর্ডারটি কনফার্ম করব।</p>
                
                <div class="invoice-badge-box px-3 py-2 rounded mb-3" style="background: #fff7ed; border: 1px dashed #fe5200; display: inline-block;">
                    <span class="font-13 text-muted">ইনভয়েস নম্বর:</span>
                    <strong class="font-16 text-danger ms-1">#{{ $order->invoice_id }}</strong>
                </div>
            </div>

            <!-- Body: Order Information -->
            <div class="modal-body px-4 py-3" style="max-height: 60vh; overflow-y: auto;">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <h6 class="font-weight-bold text-dark font-14 mb-2 pb-1 border-bottom d-flex align-items-center">
                                <i class="fa fa-user me-2 text-primary"></i> কাস্টমার তথ্য
                            </h6>
                            <p class="font-13 text-muted mb-1"><strong>নাম:</strong> {{ $order->shipping?->name ?? 'N/A' }}</p>
                            <p class="font-13 text-muted mb-1"><strong>ফোন:</strong> {{ $order->shipping?->phone ?? 'N/A' }}</p>
                            <p class="font-13 text-muted mb-0"><strong>ঠিকানা:</strong> {{ $order->shipping?->address ?? '' }}, {{ $order->shipping?->area ?? '' }}</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <h6 class="font-weight-bold text-dark font-14 mb-2 pb-1 border-bottom d-flex align-items-center">
                                <i class="fa fa-receipt me-2 text-primary"></i> পেমেন্ট সারাংশ
                            </h6>
                            <p class="font-13 text-muted mb-1 d-flex justify-content-between">
                                <span>পেমেন্ট পদ্ধতি:</span>
                                <strong class="text-uppercase text-dark">{{ $order->payment?->payment_method ?? 'Cash on delivery' }}</strong>
                            </p>
                            <p class="font-13 text-muted mb-1 d-flex justify-content-between">
                                <span>ডেলিভারি চার্জ:</span>
                                <span>৳{{ number_format((float) ($order->shipping_charge ?? 0), 2) }}</span>
                            </p>
                            <p class="font-14 mb-0 pt-1 border-top d-flex justify-content-between">
                                <strong class="text-dark">সর্বমোট প্রদেয়:</strong>
                                <strong class="text-danger font-16">৳{{ number_format((float) ($order->amount ?? 0), 2) }}</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Product Details Table -->
                <div class="order-items-box mb-2">
                    <h6 class="font-weight-bold text-dark font-14 mb-2">অর্ডারকৃত পণ্যসমূহ:</h6>
                    <div class="table-responsive rounded border">
                        <table class="table table-sm table-striped mb-0 font-13 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2">পণ্য</th>
                                    <th class="text-center py-2">পরিমাণ</th>
                                    <th class="text-end pe-3 py-2">মূল্য</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderdetails as $detail)
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="fw-bold text-dark">{{ $detail->product_name }}</div>
                                        @if($detail->product_size || $detail->product_color)
                                        <small class="text-muted">
                                            @if($detail->product_size) সাইজ: {{ $detail->product_size }} @endif
                                            @if($detail->product_color) | কালার: {{ $detail->product_color }} @endif
                                        </small>
                                        @endif
                                    </td>
                                    <td class="text-center py-2">{{ $detail->qty }}</td>
                                    <td class="text-end pe-3 py-2 font-weight-bold">৳{{ number_format((float) (($detail->sale_price ?? 0) * ($detail->qty ?? 1)), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with Action Buttons -->
            <div class="modal-footer border-0 bg-light px-4 py-3 justify-content-between">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4 py-2 font-14 font-weight-bold" style="border-radius: 8px;">
                    <i class="fa fa-home me-1"></i> হোম পেজে যান
                </a>
                <a href="{{ url('customer/order-success/' . $order->id) }}" class="btn btn-primary px-4 py-2 font-14 font-weight-bold" style="background: #fe5200; border-color: #fe5200; border-radius: 8px;">
                    <i class="fa fa-file-text me-1"></i> পূর্ণাঙ্গ ইনভয়েস দেখুন
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        function dismissOrderModal() {
            var modal = document.getElementById('orderSuccessModal');
            if (modal) {
                modal.remove();
            }
            var customModal = document.getElementById('custom-modal');
            if (customModal) {
                customModal.style.display = 'none';
                customModal.innerHTML = '';
            }
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.close-order-modal').forEach(function (btn) {
            btn.addEventListener('click', function () {
                dismissOrderModal();
            });
        });

        var modalOverlay = document.getElementById('orderSuccessModal');
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function (e) {
                if (e.target === modalOverlay) {
                    dismissOrderModal();
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                dismissOrderModal();
            }
        });
    })();
</script>
