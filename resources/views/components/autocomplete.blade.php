@props([
    'name' => 'search',
    'value' => '',
    'placeholder' => 'Buscar...',
    'url' => null,
    'min' => 2,
    'debounce' => 250,
    'submit' => true, 
    'event' => null, 
    'dedupeText' => false,
])

@php
    $id = $attributes->get('id') ?: 'ac_' . \Illuminate\Support\Str::random(6);
@endphp

<div x-data="autocompleteComponent({ url: '{{ $url }}', min: {{ (int) $min }}, debounce: {{ (int) $debounce }}, initial: @js($value), submit: {{ $submit ? 'true' : 'false' }}, event: @js($event), dedupeText: {{ $dedupeText ? 'true' : 'false' }} })" x-modelable="query" @click.away="open = false" {{ $attributes->merge(['class' => 'relative w-full']) }}>
    <input id="{{ $id }}" name="{{ $name }}" type="text" x-model="query" x-ref="input"
        x-on:input="onInput" x-on:keydown.arrow-down.prevent="highlightNext()"
        x-on:keydown.arrow-up.prevent="highlightPrev()" x-on:keydown.enter.prevent="applyHighlighted()"
        placeholder="{{ $placeholder }}"
    class="block w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:border-purple-400 focus:ring-1 focus:ring-purple-400 focus:ring-offset-0 placeholder-gray-400 dark:placeholder-gray-500 pl-10 h-[38px]"
        autocomplete="off" />
    
    {{-- Search Icon (Absolute positioning) --}}
    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>

    <template x-if="open">
        <ul class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-auto">
            <template x-for="(item, index) in suggestions" :key="item.id ?? item.text">
                <li :class="{ 'bg-gray-100 dark:bg-gray-600': index === highlighted }"
                    class="px-3 py-2 text-sm text-gray-700 dark:text-gray-200 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                    x-on:click="select(item)">
                    <span x-text="item.text"></span>
                    <span x-show="item.type" class="ml-2 text-xs text-gray-500" x-text="'(' + item.type + ')'" />
                </li>
            </template>
            <template x-if="!suggestions.length">
                <li class="px-3 py-2 text-sm text-gray-400">Sin coincidencias</li>
            </template>
        </ul>
    </template>
</div>

@once
    <script>
        function autocompleteComponent({ url, min, debounce, initial, submit, event, dedupeText }) {
            return {
                query: initial || '',
                open: false,
                suggestions: [],
                highlighted: -1,
                timer: null,
                async fetchData(q) {
                    if (!url) return [];
                    const params = new URLSearchParams({ q, term: q, search: q });
                    try {
                        const separator = url.includes('?') ? '&' : '?';
                        const res = await fetch(`${url}${separator}${params.toString()}` , {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        if (!res.ok) {
                            console.warn('Autocomplete request failed', res.status, res.statusText);
                            return [];
                        }
                        const contentType = (res.headers.get('content-type') || '').toLowerCase();
                        if (!contentType.includes('application/json')) {
                            console.warn('Autocomplete response is not JSON');
                            return [];
                        }
                        const data = await res.json();
                        const items = (data?.data ?? data ?? []);
                        const textFrom = (obj) => {
                            const candidates = ['text', 'name', 'label', 'title', 'product_name', 'productName', 'full_name', 'fullName', 'display'];
                            for (const key of candidates) {
                                if (typeof obj?.[key] === 'string' && obj[key].trim().length) return obj[key];
                            }
                            if (obj && typeof obj === 'object') {
                                const first = Object.values(obj).find(v => typeof v === 'string' && v.trim().length);
                                if (first) return first;
                            }
                            return String(obj ?? '');
                        };
                        const idFrom = (obj, text) => {
                            return obj?.id ?? obj?.value ?? obj?.product_variant_id ?? obj?.product_id ?? obj?.inventory_id ?? text;
                        };
                        const mapped = items
                            .map(it => {
                                const text = textFrom(it);
                                return {
                                    id: idFrom(it, text),
                                    text,
                                    type: it?.type ?? null,
                                };
                            })
                            .filter(it => typeof it.text === 'string' && it.text.length);
                        if (!dedupeText) return mapped;
                        const uniques = [];
                        const seen = new Set();
                        mapped.forEach(it => {
                            const key = (it.text || '').trim().toLowerCase();
                            if (key && !seen.has(key)) {
                                seen.add(key);
                                uniques.push(it);
                            }
                        });
                        return uniques;
                    } catch (err) {
                        console.error('Autocomplete fetch error', err);
                        return [];
                    }
                },
                onInput(e) {
                    this.highlighted = -1;
                    const q = this.query.trim();
                    if (this.$el) {
                        this.$el.dispatchEvent(new CustomEvent('ac-input', { bubbles: true, detail: this.query }));
                    }
                    if (q.length < min) {
                        this.open = false;
                        this.suggestions = [];
                        return;
                    }
                    clearTimeout(this.timer);
                    this.timer = setTimeout(async () => {
                        try {
                            this.suggestions = await this.fetchData(q);
                            this.open = true;
                        } catch (err) {
                            this.open = false;
                            this.suggestions = [];
                        }
                    }, debounce);
                },
                select(item) {
                    const value = item.text || '';
                    this.query = value;
                    this.open = false;
                    this.suggestions = [];
                    this.highlighted = -1;
                    if (this.$el) {
                        this.$el.dispatchEvent(new CustomEvent('ac-input', { bubbles: true, detail: value }));
                    }
                    this.$nextTick(() => {
                        if (this.$refs.input) this.$refs.input.value = value;
                        if (event && this.$el) {
                            this.$el.dispatchEvent(new CustomEvent(event, { bubbles: true, detail: { text: value, item } }));
                        }
                        if (submit) {
                            const form = this.$el.closest('form');
                            if (form) {
                                if (typeof form.requestSubmit === 'function') form.requestSubmit();
                                else form.submit();
                            }
                        }
                    });
                },
                highlightNext() {
                    if (!this.open) return;
                    this.highlighted = (this.highlighted + 1) % this.suggestions.length;
                },
                highlightPrev() {
                    if (!this.open) return;
                    this.highlighted = (this.highlighted - 1 + this.suggestions.length) % this.suggestions.length;
                },
                applyHighlighted() {
                    if (this.open && this.highlighted >= 0) {
                        this.select(this.suggestions[this.highlighted]);
                    } else {
                        this.open = false;
                        if (event && this.$el) {
                            this.$el.dispatchEvent(new CustomEvent(event, { bubbles: true, detail: { text: this.query, item: null } }));
                        }
                         if (this.$el) {
                            this.$el.dispatchEvent(new CustomEvent('ac-input', { bubbles: true, detail: this.query }));
                        }
                    }
                }
            }
        }
    </script>
@endonce
