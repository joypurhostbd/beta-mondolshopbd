@php
    $subtotalRaw = Cart::instance('pos_shopping')->subtotal();
    $subtotal = (float) str_replace(',', '', $subtotalRaw);
    $shipping = (float) Session::get('pos_shipping', 0);
    $total_discount = (float) (Session::get('pos_discount', 0) + Session::get('product_discount', 0));
    $total = max(0, ($subtotal + $shipping) - $total_discount);
@endphp
<tr class="table-light">
    <td class="text-muted font-13">Sub Total</td>
    <td class="text-end fw-semibold font-13" id="calc_subtotal" data-val="{{ $subtotal }}">৳{{ number_format($subtotal, 2) }}</td>
</tr>
<tr>
    <td class="text-muted font-13">Shipping Fee</td>
    <td class="text-end fw-semibold font-13" id="calc_shipping" data-val="{{ $shipping }}">৳{{ number_format($shipping, 2) }}</td>
</tr>
<tr>
    <td class="text-muted font-13">Item Discounts</td>
    <td class="text-end text-danger font-13" id="calc_discount" data-val="{{ $total_discount }}">-৳{{ number_format($total_discount, 2) }}</td>
</tr>
<tr class="table-active border-top border-2">
    <td class="fw-bold text-dark font-14">Payable Total</td>
    <td class="text-end fw-bold text-success font-15" id="calc_total" data-val="{{ $total }}">৳{{ number_format($total, 2) }}</td>
</tr>