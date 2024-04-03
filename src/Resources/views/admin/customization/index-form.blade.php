<x-admin::layouts>
    <x-slot:title>
        {{ __('Customization') }}
    </x-slot>

    <v-locales>

        <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                {{ __('Customization') }}
            </p>
            <div class="flex gap-x-2.5 items-center">
                {{-- <a
                    href="{{ route('wc_customization.admin.customization.index') }}" 
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                    
                >Back</a> --}}

                <button
                    type="button"
                    class="primary-button"
                    >
                    {{ __('Create Page') }}
                </button>
            </div> 
        </div>

        <!-- DataGrid Shimmer -->
        <x-admin::shimmer.datagrid />
    </v-locales>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-locales-template"
        >

            <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
                <p class="text-xl text-gray-800 dark:text-white font-bold">
                    {{ __('Customization') }}
                </p>

                <div class="flex gap-x-2.5 items-center">
                    {{-- <a
                        href="{{ route('wc_customization.admin.customization.index') }}" 
                        class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                        
                    >Back</a> --}}

                    <!-- Locale Create Button -->
                    <button
                        type="button"
                        class="primary-button"
                        @click="selectedLocales=0;resetForm();$refs.localeUpdateOrCreateModal.toggle()"
                    >
                        {{ __('Create Page') }}
                    </button>
                </div>
            </div>

            <x-admin::datagrid :src="route('wc_customization.admin.customization.index')" ref="datagrid">
                <!-- DataGrid Body -->
                <template #body="{ columns, records, performAction }">
                    <div
                        v-for="record in records"
                        class="row grid gap-2.5 items-center px-4 py-4 border-b dark:border-gray-800 text-gray-600 dark:text-gray-300 transition-all hover:bg-gray-50 dark:hover:bg-gray-950"
                        :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                    >

                        <!-- Code -->
                        <p v-text="record.title"></p>

                        <!-- Name -->
                        <p v-text="record.slug"></p>

                        <!-- Actions -->
                        <div class="flex justify-end">
                            <a @click="selectedLocales=1; editModal(record.actions.find(action => action.index === 'page_edit')?.url)">
                                <span
                                    :class="record.actions.find(action => action.index === 'page_edit')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                >
                                </span>
                            </a>
                            <a :href="record.actions.find(action => action.index === 'page_exit')?.url">
                                <span
                                    :class="record.actions.find(action => action.index === 'page_exit')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                >
                                </span>
                            </a>
                        </div>
                    </div>
                </template>
            </x-admin::datagrid>

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, updateOrCreate)"
                    ref="createLocaleForm"
                >

                    <x-admin::modal ref="localeUpdateOrCreateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg text-gray-800 dark:text-white font-bold">
                                <span>Pages</span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="page_id"
                                v-model="locale.page_id"
                            />

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    {{ __('Page Title') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="title"
                                    name="title"
                                    rules="required"
                                    v-model="locale.title"
                                    label="Page Title"
                                    placeholder="Page Title"
                                    {{-- ::disabled="locale.id" --}}
                                />

                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    {{ __('Page Slug') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="slug"
                                    name="slug"
                                    rules="required"
                                    v-model="locale.slug"
                                    label="Page Slug"
                                    placeholder="Page Slug"
                                    {{-- ::disabled="locale.id" --}}
                                />

                                <x-admin::form.control-group.error control-name="slug" />
                            </x-admin::form.control-group>

                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <div class="flex gap-x-2.5 items-center">
                                <button
                                    type="submit"
                                    class="primary-button"
                                >Save</button>
                            </div>
                        </x-slot>
                    </x-admin::modal>

                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-locales', {
                template: '#v-locales-template',

                data() {
                    return {
                        locale: {
                            section_form: null,
                            page_slug: null,
                            section_slug: null,
                            image: [],
                        },

                        selectedLocales: 0,
                    }
                },

                computed: {
                    gridsCount() {
                        let count = this.$refs.datagrid.available.columns.length;

                        if (this.$refs.datagrid.available.actions.length) {
                            ++count;
                        }

                        if (this.$refs.datagrid.available.massActions.length) {
                            ++count;
                        }

                        return count;
                    },
                },

                methods: {
                    updateOrCreate(params, { resetForm, setErrors  }) {
                        let formData = new FormData(this.$refs.createLocaleForm);

                        // if (params.id) {
                        //     formData.append('_method', 'put');
                        // }

                        this.$axios.post(params.id ? "{{ route('wc_customization.page.store') }}" : "{{ route('wc_customization.page.store') }}", formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then((response) => {
                            this.$refs.localeUpdateOrCreateModal.close();

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$refs.datagrid.get();

                            resetForm();
                        })
                        .catch(error => {
                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    editModal(url) {
                        this.$axios.get(url)
                            .then((response) => {
                                this.locale = {
                                    ...response.data.data,
                                        // image: response.data.data.logo_path
                                        // ? [{ id: 'logo_url', url: response.data.data.logo_url }]
                                        // : [],
                                };

                                this.$refs.localeUpdateOrCreateModal.toggle();
                            })
                    },

                    resetForm() {
                        this.locale = {
                            image: [],
                        };
                    }
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
