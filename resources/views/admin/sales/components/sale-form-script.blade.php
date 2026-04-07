@push('scripts')
    <script>
        function saleInvoiceForm(config) {
            return {
                clients: config.clients || [],
                variants: config.variants || [],
                selectedClientId: config.selectedClient || '',
                selectedCurrency: config.selectedCurrency || 'NIO',
                selectedPaymentMethod: config.selectedPaymentMethod || '',
                isCredit: Boolean(config.initialIsCredit),
                clientSearch: '',
                productSearch: '',
                openClientResults: false,
                openProductResults: false,
                items: [],

                init() {
                    if (this.selectedClientId) {
                        const selectedClient = this.clients.find((client) => String(client.id) === String(this.selectedClientId));
                        if (selectedClient) {
                            this.clientSearch = selectedClient.name;
                        }
                    }

                    if (Array.isArray(config.initialItems) && config.initialItems.length > 0) {
                        config.initialItems.forEach((storedItem) => {
                            const variant = this.variants.find((v) => String(v.id) === String(storedItem.product_variant_id));
                            if (variant) {
                                this.items.push(this.makeItem(variant, storedItem));
                            }
                        });
                    }

                    this.$watch('isCredit', () => {
                        this.syncItemPricesBySaleType();
                    });
                },

                get filteredClients() {
                    const term = (this.clientSearch || '').toLowerCase().trim();
                    if (!term) {
                        return this.clients.slice(0, 10);
                    }

                    return this.clients
                        .filter((client) => {
                            return `${client.name} ${client.document}`.toLowerCase().includes(term);
                        })
                        .slice(0, 10);
                },

                get filteredVariants() {
                    const term = (this.productSearch || '').toLowerCase().trim();
                    if (!term) {
                        return this.variants.slice(0, 10);
                    }

                    return this.variants
                        .filter((variant) => {
                            return `${variant.label} ${variant.sku}`.toLowerCase().includes(term);
                        })
                        .slice(0, 12);
                },

                pickClient(client) {
                    this.selectedClientId = String(client.id);
                    this.clientSearch = client.name;
                    this.openClientResults = false;
                },

                currentUnitPrice(variant) {
                    if (this.isCredit && variant.credit_price !== null) {
                        return Number(variant.credit_price) || 0;
                    }

                    return Number(variant.unit_price) || 0;
                },

                makeItem(variant, stored = null) {
                    const quantity = stored?.quantity ? Number(stored.quantity) : 1;
                    const discount = stored?.discount ? Boolean(Number(stored.discount)) : false;
                    const discountPercentage = stored?.discount_percentage ? Number(stored.discount_percentage) : 0;

                    return {
                        key: `${Date.now()}-${Math.random().toString(36).slice(2)}`,
                        product_variant_id: String(variant.id),
                        label: variant.label,
                        sku: variant.sku,
                        unit_price: this.currentUnitPrice(variant),
                        quantity: quantity > 0 ? quantity : 1,
                        discount,
                        discount_percentage: discountPercentage,
                        tax_percentage: Number(variant.tax_percentage) || 0,
                    };
                },

                addItemFromVariant(variant) {
                    const existingItem = this.items.find((item) => item.product_variant_id === String(variant.id));
                    if (existingItem) {
                        existingItem.quantity = Number(existingItem.quantity || 0) + 1;
                    } else {
                        this.items.push(this.makeItem(variant));
                    }

                    this.productSearch = '';
                    this.openProductResults = false;
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                syncItemPricesBySaleType() {
                    this.items = this.items.map((item) => {
                        const variant = this.variants.find((v) => String(v.id) === String(item.product_variant_id));
                        if (!variant) {
                            return item;
                        }

                        return {
                            ...item,
                            unit_price: this.currentUnitPrice(variant),
                        };
                    });
                },

                calculateLineBase(item) {
                    const quantity = Number(item.quantity) || 0;
                    const unitPrice = Number(item.unit_price) || 0;
                    return quantity * unitPrice;
                },

                calculateLineDiscount(item) {
                    if (!item.discount) {
                        return 0;
                    }

                    const percentage = Number(item.discount_percentage) || 0;
                    return this.calculateLineBase(item) * (percentage / 100);
                },

                calculateLineTax(item) {
                    const taxable = this.calculateLineBase(item) - this.calculateLineDiscount(item);
                    const percentage = Number(item.tax_percentage) || 0;
                    return taxable * (percentage / 100);
                },

                calculateLineTotal(item) {
                    return (this.calculateLineBase(item) - this.calculateLineDiscount(item)) + this.calculateLineTax(item);
                },

                calculateSubtotal() {
                    return this.items.reduce((acc, item) => acc + this.calculateLineBase(item), 0);
                },

                calculateTotalDiscount() {
                    return this.items.reduce((acc, item) => acc + this.calculateLineDiscount(item), 0);
                },

                calculateTotalTax() {
                    return this.items.reduce((acc, item) => acc + this.calculateLineTax(item), 0);
                },

                calculateGrandTotal() {
                    return this.calculateSubtotal() - this.calculateTotalDiscount() + this.calculateTotalTax();
                },

                formatMoney(value) {
                    const amount = Number(value) || 0;
                    const formatter = new Intl.NumberFormat('es-NI', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });

                    const prefix = this.selectedCurrency === 'USD' ? '$' : 'C$';
                    return `${prefix}${formatter.format(amount)}`;
                },
            };
        }
    </script>
@endpush
