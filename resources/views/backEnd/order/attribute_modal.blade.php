<!-- Product Attribute Selection Modal -->
<div class="modal fade" id="productAttributeModal" tabindex="-1" aria-labelledby="productAttributeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-16" id="productAttributeModalLabel"><i class="fa fa-tags me-1"></i> Select Product Attributes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                    <img id="attr_modal_img" src="" alt="Product" style="width: 55px; height: 55px; object-fit: cover; border-radius: 4px; margin-right: 12px; border: 1px solid #dee2e6;">
                    <div>
                        <h6 id="attr_modal_name" class="mb-1 font-14 font-weight-bold text-dark"></h6>
                        <div>
                            <span class="text-primary font-weight-bold font-14" id="attr_modal_price"></span>
                            <span class="badge bg-soft-success text-success ms-2 font-11" id="attr_modal_stock"></span>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="attr_modal_product_id" value="">

                <!-- Size selection -->
                <div id="attr_modal_size_group" class="mb-3" style="display: none;">
                    <label class="form-label font-weight-bold d-block mb-1">Select Size <span class="text-danger">*</span></label>
                    <div id="attr_modal_sizes" class="d-flex flex-wrap gap-2"></div>
                </div>

                <!-- Color selection -->
                <div id="attr_modal_color_group" class="mb-3" style="display: none;">
                    <label class="form-label font-weight-bold d-block mb-1">Select Color <span class="text-danger">*</span></label>
                    <div id="attr_modal_colors" class="d-flex flex-wrap gap-2"></div>
                </div>

                <!-- Quantity -->
                <div class="mb-2">
                    <label class="form-label font-weight-bold mb-1">Quantity</label>
                    <input type="number" id="attr_modal_qty" class="form-control form-control-sm" value="1" min="1" style="width: 90px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="attr_modal_submit_btn"><i class="fa fa-cart-plus me-1"></i> Add to Order</button>
            </div>
        </div>
    </div>
</div>
