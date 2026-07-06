@push('scripts')
    <script>
        function saleInvoiceForm(config) {
            return {
                clients: config.clients || [],
                initialVariants: config.initialVariants || [],
                categoryOptions: config.categoryOptions || [],
                brandOptions: config.brandOptions || [],
                productSearchUrl: config.productSearchUrl || '',
                selectedClientId: config.selectedClient || '',
                selectedCurrency: config.selectedCurrency || 'NIO',
                selectedPaymentMethod: config.selectedPaymentMethod || '',
                isCredit: Boolean(config.initialIsCredit),
                clientSearch: '',
                openClientResults: false,
                isProductModalOpen: false,
                productSearchTerm: '',
                productFilters: {
                    category_id: '',
                    brand_id: '',
                },
                paginatedProducts: [],
                productMeta: {
                    current_page: 1,
                    last_page: 1,
                    per_page: 12,
                    total: 0,
                },
                isLoadingProducts: false,
                variantIndex: {},
                productSearchTimer: null,
                items: [],

                init() {
                    if (this.selectedClientId) {
                        const selectedClient = this.clients.find((client) => String(client.id) === String(this.selectedClientId));
                        if (selectedClient) {
                            this.clientSearch = selectedClient.name;
                        }
                    }

                    if (Array.isArray(this.initialVariants) && this.initialVariants.length > 0) {
                        this.initialVariants.forEach((variant) => {
                            this.rememberVariant(variant);
                        });
                    }

                    if (Array.isArray(config.initialItems) && config.initialItems.length > 0) {
                        config.initialItems.forEach((storedItem) => {
                            const variant = this.variantIndex[String(storedItem.product_variant_id)];
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

                pickClient(client) {
                    this.selectedClientId = String(client.id);
                    this.clientSearch = client.name;
                    this.openClientResults = false;
                },

                normalizeVariant(rawVariant) {
                    return {
                        id: String(rawVariant.id),
                        label: rawVariant.label || rawVariant.product_name || 'Producto',
                        sku: rawVariant.sku || '',
                        unit_price: Number(rawVariant.unit_price) || 0,
                        credit_price: rawVariant.credit_price === null || rawVariant.credit_price === undefined
                            ? null
                            : Number(rawVariant.credit_price),
                        tax_percentage: Number(rawVariant.tax_percentage) || 0,
                        stock: Number(rawVariant.stock) || 0,
                        category_name: rawVariant.category_name || null,
                        brand_name: rawVariant.brand_name || null,
                        image_url: rawVariant.image_url || null,
                    };
                },

                rememberVariant(variant) {
                    const normalized = this.normalizeVariant(variant);
                    this.variantIndex[String(normalized.id)] = normalized;
                    return normalized;
                },

                openProductModal() {
                    this.isProductModalOpen = true;
                    this.searchProducts(1);
                },

                closeProductModal() {
                    this.isProductModalOpen = false;
                },

                onProductSearchInput() {
                    clearTimeout(this.productSearchTimer);
                    this.productSearchTimer = setTimeout(() => {
                        this.searchProducts(1);
                    }, 300);
                },

                async searchProducts(page = 1) {
                    if (!this.productSearchUrl || page < 1) {
                        return;
                    }

                    this.isLoadingProducts = true;

                    try {
                        const params = new URLSearchParams({
                            per_page: String(this.productMeta.per_page || 12),
                            page: String(page),
                        });

                        const term = (this.productSearchTerm || '').trim();
                        if (term.length > 0) {
                            params.set('q', term);
                        }

                        if (this.productFilters.category_id) {
                            params.set('filter[category_id]', this.productFilters.category_id);
                        }

                        if (this.productFilters.brand_id) {
                            params.set('filter[brand_id]', this.productFilters.brand_id);
                        }

                        const separator = this.productSearchUrl.includes('?') ? '&' : '?';
                        const response = await fetch(`${this.productSearchUrl}${separator}${params.toString()}`, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });

                        if (!response.ok) {
                            throw new Error(`Request failed with status ${response.status}`);
                        }

                        const payload = await response.json();
                        const rows = Array.isArray(payload?.data) ? payload.data : [];

                        this.paginatedProducts = rows.map((variant) => this.rememberVariant(variant));

                        this.productMeta = {
                            current_page: Number(payload?.meta?.current_page || 1),
                            last_page: Number(payload?.meta?.last_page || 1),
                            per_page: Number(payload?.meta?.per_page || 12),
                            total: Number(payload?.meta?.total || this.paginatedProducts.length),
                        };
                    } catch (error) {
                        this.paginatedProducts = [];
                        this.productMeta = {
                            current_page: 1,
                            last_page: 1,
                            per_page: this.productMeta.per_page || 12,
                            total: 0,
                        };
                    } finally {
                        this.isLoadingProducts = false;
                    }
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
                    const normalizedVariant = this.rememberVariant(variant);
                    const existingItem = this.items.find((item) => item.product_variant_id === String(normalizedVariant.id));
                    if (existingItem) {
                        existingItem.quantity = Number(existingItem.quantity || 0) + 1;
                    } else {
                        this.items.push(this.makeItem(normalizedVariant));
                    }
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                syncItemPricesBySaleType() {
                    this.items = this.items.map((item) => {
                        const variant = this.variantIndex[String(item.product_variant_id)];
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
