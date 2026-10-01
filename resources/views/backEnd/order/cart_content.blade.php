@forelse($cartinfo as $key=>$value)
@php
    $cartProd = isset($cartProducts) && isset($cartProducts[$value->id]) ? $cartProducts[$value->id] : null;
    $availableSizes = $cartProd && $cartProd->sizes ? $cartProd->sizes : collect();
    $availableColors = $cartProd && $cartProd->colors ? $cartProd->colors : collect();
    $availableImages = $cartProd && $cartProd->images ? $cartProd->images : collect();
    $featureImage = ($cartProd?->featuredImage ?? $cartProd?->image)?->image ?? '';
    $currentImage = $featureImage ?: ($value->options->image ?? '');
    $currentSize = $value->options->product_size ?? '';
    $currentColor = $value->options->product_color ?? '';
    $itemDiscount = (float)($value->options->product_discount ?? 0);
    $unitPrice = (float)$value->price;
    $lineTotal = max(0, ($unitPrice - $itemDiscount) * (int)$value->qty);
@endphp
<tr>
  <td class="text-center align-middle">
    @if($availableImages->count() > 1)
      <div class="dropdown d-inline-block">
        <a href="javascript:void(0);" class="d-inline-block position-relative" data-bs-toggle="dropdown" aria-expanded="false" title="Click to change product image">
          @if(!empty($currentImage))
            <img src="{{ asset($currentImage) }}" class="rounded border shadow-sm" style="width: 44px; height: 44px; object-fit: cover;" alt="{{ $value->name }}">
          @else
            <div class="rounded border bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
              <i class="fe-image text-muted"></i>
            </div>
          @endif
          <span class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 16px; height: 16px; font-size: 8px; transform: translate(25%, 25%);" title="Change image">
            <i class="fa fa-camera"></i>
          </span>
        </a>
        <div class="dropdown-menu p-2 shadow-lg border" style="min-width: 210px; max-width: 250px; z-index: 1060;">
          <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
            <span class="font-11 text-dark fw-bold"><i class="fa fa-images text-primary me-1"></i> Select Image</span>
            <span class="badge bg-soft-primary text-primary font-10">{{ $availableImages->count() }} available</span>
          </div>
          <div class="d-flex flex-wrap gap-1 justify-content-start">
            @foreach($availableImages as $imgObj)
              @php $isSelected = ($currentImage === $imgObj->image); @endphp
              <a href="javascript:void(0);" class="cart_image_select d-inline-block border rounded p-1 position-relative {{ $isSelected ? 'border-primary bg-soft-primary' : 'border-light bg-white' }}" data-id="{{ $value->rowId }}" data-image="{{ $imgObj->image }}" title="{{ $isSelected ? 'Currently Selected' : 'Select this image' }}" style="transition: all 0.2s ease;">
                <img src="{{ asset($imgObj->image) }}" class="rounded" style="width: 42px; height: 42px; object-fit: cover;" alt="product gallery">
                @if($isSelected)
                  <span class="position-absolute top-0 end-0 badge bg-primary rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 14px; height: 14px; font-size: 8px; transform: translate(30%, -30%);">
                    <i class="fa fa-check text-white"></i>
                  </span>
                @endif
              </a>
            @endforeach
          </div>
        </div>
      </div>
    @elseif(!empty($currentImage))
      <img src="{{ asset($currentImage) }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;" alt="{{ $value->name }}" title="Default Feature Image">
    @else
      <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
        <i class="fe-image text-muted"></i>
      </div>
    @endif
  </td>
  <td class="align-middle">
    <span class="fw-semibold text-dark d-block font-13">{{ $value->name }}</span>
    @if($cartProd && !empty($cartProd->product_code))
      <span class="badge bg-soft-secondary text-secondary font-11">SKU: {{ $cartProd->product_code }}</span>
    @endif
  </td>
  <td class="align-middle">
    @if($availableSizes->isNotEmpty())
      <div class="input-group input-group-sm mb-1" style="min-width: 120px;">
        <span class="input-group-text py-0 px-1 font-11 text-muted">Size</span>
        <select class="form-select form-select-sm cart_attribute_change" data-id="{{ $value->rowId }}" data-type="size">
          <option value="">Select Size</option>
          @php $sizeMatched = false; @endphp
          @foreach($availableSizes as $size)
            @php 
              $selected = ($currentSize == $size->sizeName);
              if ($selected) { $sizeMatched = true; }
            @endphp
            <option value="{{ $size->sizeName }}" {{ $selected ? 'selected' : '' }}>{{ $size->sizeName }}</option>
          @endforeach
          @if(!empty($currentSize) && !$sizeMatched)
            <option value="{{ $currentSize }}" selected>{{ $currentSize }} (Current)</option>
          @endif
        </select>
      </div>
    @elseif(!empty($currentSize))
      <div class="mb-1"><span class="badge bg-soft-primary text-primary font-12">Size: {{ $currentSize }}</span></div>
    @endif

    @if($availableColors->isNotEmpty())
      <div class="input-group input-group-sm" style="min-width: 120px;">
        <span class="input-group-text py-0 px-1 font-11 text-muted">Color</span>
        <select class="form-select form-select-sm cart_attribute_change" data-id="{{ $value->rowId }}" data-type="color">
          <option value="">Select Color</option>
          @php $colorMatched = false; @endphp
          @foreach($availableColors as $color)
            @php 
              $selected = ($currentColor == $color->colorName);
              if ($selected) { $colorMatched = true; }
            @endphp
            <option value="{{ $color->colorName }}" {{ $selected ? 'selected' : '' }}>{{ $color->colorName }}</option>
          @endforeach
          @if(!empty($currentColor) && !$colorMatched)
            <option value="{{ $currentColor }}" selected>{{ $currentColor }} (Current)</option>
          @endif
        </select>
      </div>
    @elseif(!empty($currentColor))
      <div><span class="badge bg-soft-info text-info font-12">Color: {{ $currentColor }}</span></div>
    @endif

    @if($availableSizes->isEmpty() && $availableColors->isEmpty() && empty($currentSize) && empty($currentColor))
      <span class="text-muted font-12">-</span>
    @endif
  </td>
  <td class="align-middle text-center">
    <div class="d-inline-flex align-items-center border rounded">
      <button type="button" class="btn btn-sm btn-light py-0 px-2 cart_decrement" value="{{ $value->qty }}" data-id="{{ $value->rowId }}"><i class="fa fa-minus font-11"></i></button>
      <input type="text" value="{{ $value->qty }}" class="form-control form-control-sm text-center border-0 p-0" style="width: 36px; font-weight: bold;" readonly />
      <button type="button" class="btn btn-sm btn-light py-0 px-2 cart_increment" value="{{ $value->qty }}" data-id="{{ $value->rowId }}"><i class="fa fa-plus font-11"></i></button>
    </div>
  </td>
  <td class="align-middle text-end font-13">৳{{ number_format($unitPrice, 2) }}</td>
  <td class="align-middle text-center" style="max-width: 85px;">
    <input type="number" step="any" min="0" class="form-control form-control-sm product_discount text-center px-1" value="{{ $itemDiscount }}" placeholder="0.00" data-id="{{ $value->rowId }}" title="Discount (৳)">
  </td>
  <td class="align-middle text-end fw-bold font-13 text-dark">৳{{ number_format($lineTotal, 2) }}</td>
  <td class="align-middle text-center">
    <button type="button" class="btn btn-outline-danger btn-xs cart_remove rounded-circle" data-id="{{ $value->rowId }}" title="Remove item">
      <i class="fa fa-times"></i>
    </button>
  </td>
</tr>
@empty
<tr>
  <td colspan="8" class="text-center text-muted py-4">
    <i class="fe-shopping-bag font-22 d-block mb-1 text-secondary"></i>
    <span>No products in cart. Use the search bar above to add items.</span>
  </td>
</tr>
@endforelse