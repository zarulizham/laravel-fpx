<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailsModalLabel" data-role="title">FPX Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <h6>Transaction Info</h6>
                        <div class="border rounded p-3 bg-body-tertiary">
                            <div><strong>Order Number:</strong> <span data-role="order-number">-</span></div>
                            <div><strong>Exchange Order Number:</strong> <span data-role="exchange-order-number">-</span></div>
                            <div><strong>Reference ID:</strong> <span data-role="reference-id">-</span></div>
                            <div><strong>Reference Type:</strong> <span data-role="reference-type">-</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Request Payload</h6>
                        <pre class="border rounded p-3 bg-body-secondary mb-0"><code data-role="request-payload">{}</code></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>Response Payload</h6>
                        <pre class="border rounded p-3 bg-body-secondary mb-0"><code data-role="response-payload">{}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var modalElement = document.getElementById('transactionDetailsModal');

        if (!modalElement) {
            return;
        }

        modalElement.addEventListener('show.bs.modal', function(event) {
            var triggerButton = event.relatedTarget;

            if (!triggerButton) {
                return;
            }

            var modelString = triggerButton.getAttribute('data-model');

            if (!modelString) {
                return;
            }

            var model;

            try {
                model = JSON.parse(modelString);
            } catch (error) {
                return;
            }

            var title = modalElement.querySelector('[data-role="title"]');
            var orderNumber = modalElement.querySelector('[data-role="order-number"]');
            var exchangeOrderNumber = modalElement.querySelector('[data-role="exchange-order-number"]');
            var referenceId = modalElement.querySelector('[data-role="reference-id"]');
            var referenceType = modalElement.querySelector('[data-role="reference-type"]');
            var requestPayload = modalElement.querySelector('[data-role="request-payload"]');
            var responsePayload = modalElement.querySelector('[data-role="response-payload"]');

            if (title) {
                title.textContent = 'FPX Transaction Details #' + (model.id ?? '-');
            }

            if (orderNumber) {
                orderNumber.textContent = model.order_number ?? '-';
            }

            if (exchangeOrderNumber) {
                exchangeOrderNumber.textContent = model.exchange_order_number ?? '-';
            }

            if (referenceId) {
                referenceId.textContent = model.reference_id ?? '-';
            }

            if (referenceType) {
                referenceType.textContent = model.reference_type ?? '-';
            }

            if (requestPayload) {
                requestPayload.textContent = JSON.stringify(model.request_payload ?? {}, null, 2);
            }

            if (responsePayload) {
                responsePayload.textContent = JSON.stringify(model.response_payload ?? {}, null, 2);
            }
        });
    })();
</script>
