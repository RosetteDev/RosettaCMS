                        <div id="ecommerce" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">E-commerce</h2>
                            <p style="font-size: 0.875rem; color: var(--text-color); margin-bottom: 1rem;">Tax applies to the subtotal minus discounts at checkout. Payment capture is manual/invoice-based in the foundation.</p>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; max-width: 300px;">
                                <label for="tax_rate" style="font-weight: 500; color: var(--text-color);">Tax rate (%)</label>
                                <input type="number" name="tax_rate" id="tax_rate" step="0.01" min="0" max="100"
                                       value="{{ old('tax_rate', $settings->tax_rate ?? 0) }}"
                                       style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;">
                            </div>
                        </div>
