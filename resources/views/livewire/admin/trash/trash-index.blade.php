<div
    class="w-full"
    x-data="{
        items: @js($this->trashItems),

        filter: 'all',
        search: '',
        page: 1,
        perPage: 25,
        processingKey: null,

        setFilter(filter) {
            if (this.filter === filter) {
                return;
            }

            this.filter = filter;
            this.page = 1;
        },

        get filteredItems() {
            let items = this.items;

            if (this.filter !== 'all') {
                items = items.filter(
                    item => item.entity_type === this.filter
                );
            }

            const search = this.search
                .trim()
                .toLowerCase();

            if (!search) {
                return items;
            }

            const terms = search
                .split(/\s+/)
                .filter(Boolean);

            return items.filter(item => {
                const searchableText = [
                    item.type_label,
                    item.title,
                    item.detail,
                    item.source,
                    item.owner_name,
                    item.deleted_by_name,
                ]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();

                return terms.every(
                    term => searchableText.includes(term)
                );
            });
        },

        get totalItems() {
            return this.filteredItems.length;
        },

        get totalPages() {
            return Math.max(
                1,
                Math.ceil(
                    this.totalItems / this.perPage
                )
            );
        },

        get paginatedItems() {
            const start = (
                this.page - 1
            ) * this.perPage;

            return this.filteredItems.slice(
                start,
                start + this.perPage
            );
        },

        get firstVisibleItem() {
            if (this.totalItems === 0) {
                return 0;
            }

            return (
                (this.page - 1) * this.perPage
            ) + 1;
        },

        get lastVisibleItem() {
            return Math.min(
                this.page * this.perPage,
                this.totalItems
            );
        },

        previousPage() {
            if (this.page > 1) {
                this.page--;
            }
        },

        nextPage() {
            if (this.page < this.totalPages) {
                this.page++;
            }
        },

        itemKey(item) {
            return `${item.entity_type}-${item.id}`;
        },

        removeItem(type, id) {
            this.items = this.items.filter(
                item => !(
                    item.entity_type === type
                    && Number(item.id) === Number(id)
                )
            );

            if (this.page > this.totalPages) {
                this.page = this.totalPages;
            }
        },

        async restoreItem(item, wire) {
            if (this.processingKey !== null) {
                return;
            }

            this.processingKey = this.itemKey(item);

            try {
                await wire.restore(
                    item.entity_type,
                    Number(item.id)
                );
            } catch (error) {
                console.error(
                    'Errore durante il ripristino dal cestino:',
                    error
                );
            } finally {
                this.processingKey = null;
            }
        },

        async deleteItem(item, wire) {
            if (this.processingKey !== null) {
                return;
            }

            const confirmed = window.confirm(
                'Questa operazione è irreversibile. Vuoi eliminare definitivamente questo elemento?'
            );

            if (!confirmed) {
                return;
            }

            this.processingKey = this.itemKey(item);

            try {
                await wire.forceDelete(
                    item.entity_type,
                    Number(item.id)
                );
            } catch (error) {
                console.error(
                    'Errore durante l\'eliminazione definitiva:',
                    error
                );
            } finally {
                this.processingKey = null;
            }
        },
    }"
    x-init="
        $watch(
            'search',
            () => {
                page = 1;
            }
        )
    "
    x-on:trash-item-removed.window="
        removeItem(
            $event.detail.type,
            $event.detail.id
        )
    "
>
    <div class="flex items-center justify-between mb-4">
        <x-page-title label="Cestino" />

        <span class="text-sm text-gray-custom-4">
            Gli elementi vengono eliminati definitivamente dopo 30 giorni.
        </span>
    </div>

    <x-card>
        <div
            class="flex flex-col gap-4 mb-5 lg:flex-row lg:items-center lg:justify-between"
        >
            <div class="flex items-center gap-2 overflow-x-auto">
                <button
                    type="button"
                    x-on:click="setFilter('all')"
                    x-bind:class="
                        filter === 'all'
                            ? 'bg-azure-custom text-white border-azure-custom'
                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                    "
                    class="px-4 py-2 text-sm font-medium border rounded-md transition cursor-pointer whitespace-nowrap"
                >
                    Tutto
                </button>

                <button
                    type="button"
                    x-on:click="setFilter('practice')"
                    x-bind:class="
                        filter === 'practice'
                            ? 'bg-azure-custom text-white border-azure-custom'
                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                    "
                    class="px-4 py-2 text-sm font-medium border rounded-md transition cursor-pointer whitespace-nowrap"
                >
                    Pratiche
                </button>

                <button
                    type="button"
                    x-on:click="setFilter('lead')"
                    x-bind:class="
                        filter === 'lead'
                            ? 'bg-azure-custom text-white border-azure-custom'
                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                    "
                    class="px-4 py-2 text-sm font-medium border rounded-md transition cursor-pointer whitespace-nowrap"
                >
                    Lead
                </button>

                <button
                    type="button"
                    x-on:click="setFilter('customer')"
                    x-bind:class="
                        filter === 'customer'
                            ? 'bg-azure-custom text-white border-azure-custom'
                            : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
                    "
                    class="px-4 py-2 text-sm font-medium border rounded-md transition cursor-pointer whitespace-nowrap"
                >
                    Anagrafiche
                </button>
            </div>

            <div class="w-full lg:max-w-sm">
                <flux:input
                    x-model.debounce.150ms="search"
                    icon="magnifying-glass"
                    placeholder="Cerca nel cestino..."
                />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-left">
                        <th class="px-3 py-3 font-medium">Tipo</th>
                        <th class="px-3 py-3 font-medium">Elemento</th>
                        <th class="px-3 py-3 font-medium">Dettaglio</th>
                        <th class="px-3 py-3 font-medium">Collaboratore</th>
                        <th class="px-3 py-3 font-medium">Eliminato da</th>
                        <th class="px-3 py-3 font-medium">Eliminato il</th>
                        <th class="px-3 py-3 font-medium">Eliminazione definitiva</th>
                        <th class="px-3 py-3 text-right font-medium">Azioni</th>
                    </tr>
                </thead>

                <tbody>
                    <template
                        x-for="item in paginatedItems"
                        x-bind:key="itemKey(item)"
                    >
                        <tr class="border-b border-gray-100">
                            <td class="px-3 py-4">
                                <span
                                    class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full"
                                    x-bind:class="{
                                        'bg-blue-50 text-blue-700':
                                            item.entity_type === 'customer',
                                        'bg-orange-50 text-orange-700':
                                            item.entity_type === 'lead',
                                        'bg-violet-50 text-violet-700':
                                            item.entity_type === 'practice',
                                    }"
                                    x-text="item.type_label"
                                ></span>
                            </td>

                            <td
                                class="px-3 py-4 font-medium"
                                x-text="item.title"
                            ></td>

                            <td class="px-3 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <span x-text="item.detail"></span>

                                    <span
                                        x-show="item.source"
                                        class="text-xs text-gray-custom-4"
                                        x-text="item.source"
                                    ></span>
                                </div>
                            </td>

                            <td
                                class="px-3 py-4"
                                x-text="item.owner_name"
                            ></td>

                            <td
                                class="px-3 py-4"
                                x-text="item.deleted_by_name"
                            ></td>

                            <td
                                class="px-3 py-4 whitespace-nowrap"
                                x-text="item.deleted_at"
                            ></td>

                            <td
                                class="px-3 py-4 whitespace-nowrap"
                                x-text="item.purge_at"
                            ></td>

                            <td class="px-3 py-4">
                                <div class="flex justify-end gap-2">
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="primary"
                                        x-bind:disabled="processingKey !== null"
                                        x-on:click="restoreItem(item, $wire)"
                                    >
                                        <span
                                            x-show="processingKey !== itemKey(item)"
                                        >
                                            Ripristina
                                        </span>

                                        <span
                                            x-show="processingKey === itemKey(item)"
                                        >
                                            Attendi...
                                        </span>
                                    </flux:button>

                                    @if (auth()->user()->isSuperAdmin())
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="danger"
                                        x-bind:disabled="processingKey !== null"
                                        x-on:click="deleteItem(item, $wire)"
                                    >
                                        <span
                                            x-show="processingKey !== itemKey(item)"
                                        >
                                            Elimina
                                        </span>

                                        <span
                                            x-show="processingKey === itemKey(item)"
                                        >
                                            Attendi...
                                        </span>
                                    </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr
                        x-show="totalItems === 0"
                        x-cloak
                    >
                        <td
                            colspan="8"
                            class="px-4 py-12 text-center text-gray-custom-4"
                        >
                            Nessun elemento presente nel cestino.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            x-show="totalItems > 0"
            x-cloak
            class="flex flex-col gap-3 pt-5 mt-5 border-t border-gray-100 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="text-sm text-gray-custom-4">
                <span x-text="firstVisibleItem"></span>
                -
                <span x-text="lastVisibleItem"></span>
                di
                <span x-text="totalItems"></span>
                elementi
            </div>

            <div class="flex items-center gap-3">
                <flux:button
                    type="button"
                    size="sm"
                    x-bind:disabled="page <= 1"
                    x-on:click="previousPage()"
                >
                    Precedente
                </flux:button>

                <span class="text-sm text-gray-custom-4">
                    Pagina

                    <span
                        class="font-medium text-gray-700"
                        x-text="page"
                    ></span>

                    di

                    <span
                        class="font-medium text-gray-700"
                        x-text="totalPages"
                    ></span>
                </span>

                <flux:button
                    type="button"
                    size="sm"
                    x-bind:disabled="page >= totalPages"
                    x-on:click="nextPage()"
                >
                    Successiva
                </flux:button>
            </div>
        </div>
    </x-card>
</div>
