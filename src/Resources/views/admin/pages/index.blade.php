<x-admin::layouts>
    <x-slot:title>
        {{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? $customization_page[ 'title' ] : '' }} {{ __(' Sections') }}
    </x-slot>

    <v-cust-section>

        <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                {{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? $customization_page[ 'title' ] : '' }} {{ __(' Sections') }}
            </p>
            <div class="flex gap-x-[10px] items-center">
                <a
                    href="{{ route('wc_customization.admin.customization.index') }}" 
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                    
                >Back</a>

                <button
                    type="button"
                    class="primary-button"
                    >
                    {{ __('Create Section') }}
                </button>
            </div> 
        </div>

        <!-- DataGrid Shimmer -->
        <x-admin::shimmer.datagrid />
    </v-cust-section>

    @pushOnce('scripts')

        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

        <script
            type="text/x-template"
            id="v-cust-section-template"
        >

            <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
                <p class="text-xl text-gray-800 dark:text-white font-bold">
                    {{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? $customization_page[ 'title' ] : '' }} {{ __(' Sections') }}
                </p>

                <div class="flex gap-x-2.5 items-center">
                    <a
                        href="{{ route('wc_customization.admin.customization.index') }}" 
                        class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                        
                    >Back</a>

                    <!-- Section Create Button -->
                    <button
                        type="button"
                        class="primary-button"
                        @click="resetSectionForm();$refs.sectionUpdateOrCreateModal.toggle()"
                    >
                        {{ __('Create Section') }}
                    </button>
                </div>
            </div>

            <x-admin::datagrid :src="route('wc_customization.admin.customization.pages.index', $page_slug)" ref="datagrid">
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
                            <a @click="editSectionModal(record.actions.find(action => action.index === 'section_edit')?.url)">
                                <span
                                    :class="record.actions.find(action => action.index === 'section_edit')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                >
                                </span>
                            </a>
                            <a @click="editModal(record.actions.find(action => action.index === 'section_exit')?.url)">
                                <span
                                    :class="record.actions.find(action => action.index === 'section_exit')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                >
                                </span>
                            </a>
                            <a :href="record.actions.find(action => action.index === 'section_settings')?.url">
                                <span
                                    :class="record.actions.find(action => action.index === 'section_settings')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                >
                                </span>
                            </a>

                            <a @click="deleteSection(record.actions.find(action => action.index === 'section_delete')?.url)">
                                <span
                                    :class="record.actions.find(action => action.index === 'section_delete')?.icon"
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
                    @submit="handleSubmit($event, updateOrCreateSection)"
                    ref="createSectionForm"
                >

                    <x-admin::modal ref="sectionUpdateOrCreateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg text-gray-800 dark:text-white font-bold">
                                <span>{{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? $customization_page[ 'title' ] : '' }} {{ __(' Sections') }}</span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="section_id"
                                v-model="section.id"
                            />

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="page_slug"
                                :value="$page_slug"
                            />

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    {{ __('Section Title') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="title"
                                    name="title"
                                    rules="required"
                                    v-model="section.title"
                                    label="Section Title"
                                    placeholder="Section Title"
                                />

                                <x-admin::form.control-group.error control-name="title" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    {{ __('Section Slug') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="slug"
                                    name="slug"
                                    rules="required"
                                    v-model="section.slug"
                                    label="Section Slug"
                                    placeholder="Section Slug"
                                    ::disabled="section.id"
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

            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modalForm"
            >
                <form
                    @submit="handleSubmit($event, updateOrCreate)"
                    ref="createSectionDetailForm"
                >

                    <x-admin::modal ref="sectionDetailUpdateOrCreateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg text-gray-800 dark:text-white font-bold">
                                <span>{{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? $customization_page[ 'title' ] : '' }} {{ __(' Section Details') }}</span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content class="section_from_content" style="max-height: 700px; overflow-y: auto;">

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="page_slug"
                                v-model="section_details.page_slug"
                            />

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="section_slug"
                                v-model="section_details.section_slug"
                            />

                            <span v-if="section_details.section_form">
                                <div class="grid grid-cols-2 gap-4" v-html="section_details.section_form"></div>
                            </span>

                            <span v-if="repeaters">
                                <div v-for="repeater in repeaters" class="mb-4">

                                    <x-admin::form.control-group.label>@{{repeater.title}}</x-admin::form.control-group.label>
                                    
                                    <!-- Repeater Fields -->
                                    <div class="p-4 bg-white dark:bg-gray-900 rounded box-shadow">
                                        
                                        <v-cust-section-repeater-item
                                            v-for='(single_repeater, index) in all_repeater_details[repeater.name]'
                                            :single_repeater="single_repeater"
                                            :key="index"
                                            :index="index"
                                            :repeater_name="repeater.name"
                                            :repeater_fields="repeater.repeater_fields"
                                            :pre_file_url="pre_file_url"
                                            @onRemoveRepeaterItem="removeRepeaterItem($event, repeater.name)"
                                        >
                                        </v-cust-section-repeater-item>

                                        <div
                                            class="secondary-button max-w-max mt-4"
                                            @click="addRepeaterItem(repeater)"
                                        >
                                            Add
                                        </div>

                                    </div>

                                </div>
                            </span>


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
            app.component('v-cust-section', {
                template: '#v-cust-section-template',

                data() {
                    return {
                        section_details: {
                            section_form: null,
                            page_slug: null,
                            section_slug: null,
                            image: [],
                        },

                        repeaters: [],
                        field_details: [],
                        pre_file_url: null,

                        section: {
                            page_slug: '@{{$page_slug}}'
                        },

                        all_repeater_details: []
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

                    addRepeaterItem(repeater) {

                        var single_repeater_detail_arr = [];
                        single_repeater_detail_arr = this.all_repeater_details[repeater.name]
                        
                        single_repeater_detail_arr.push({});
                        this.all_repeater_details[repeater.name] = single_repeater_detail_arr;

                    },

                    removeRepeaterItem(single_repeater_items, repeater_name) {
                        
                        let index = this.all_repeater_details[repeater_name].indexOf(single_repeater_items)

                        this.all_repeater_details[repeater_name].splice(index, 1)
                    },

                    updateOrCreate(params, { resetForm, setErrors  }) {
                        let formData = new FormData(this.$refs.createSectionDetailForm);

                        this.$axios.post("{{ route('wc_customization.customization.store') }}", formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then((response) => {
                            this.$refs.sectionDetailUpdateOrCreateModal.close();

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
                                this.section_details = {
                                    ...response.data.data,
                                };

                                this.repeaters = response.data.data.repeaters;
                                this.pre_file_url = response.data.data.pre_file_url;
                                this.field_details = response.data.data.field_details;
                                var all_field_details = response.data.data.field_details;

                                var all_repeater_details_arr = [];
                                this.repeaters.forEach( function (repeater_detail, index){
                                    all_repeater_details_arr[ repeater_detail.name ] = all_field_details[repeater_detail.name] ?? [];
                                });
                                this.all_repeater_details = all_repeater_details_arr;

                                this.$refs.sectionDetailUpdateOrCreateModal.toggle();

                                jQuery(document).ready(function(){
                                    jQuery('.section_from_content').parent().css({
                                        'max-width': '750px',
                                    });
                                });

                            })
                    },

                    resetForm() {
                        this.section_details = {
                            image: [],
                        };
                    },

                    // section operation ------------------------

                    updateOrCreateSection(params, { resetSectionForm, setErrors  }) {
                        let formData = new FormData(this.$refs.createSectionForm);

                        this.$axios.post("{{ route('wc_customization.section.store') }}", formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then((response) => {
                            if ( response.data.status_code == 500 && response.data.status == 'error' ) {

                                this.$emitter.emit('add-flash', { type: 'error', message: response.data.message });

                            } else {

                                this.$refs.sectionUpdateOrCreateModal.close();

                                this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                this.$refs.datagrid.get();

                                resetSectionForm();
                                
                            }
                        })
                        .catch(error => {
                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    editSectionModal(url) {
                        this.$axios.get(url)
                            .then((response) => {
                                this.section = {
                                    ...response.data.data,
                                };

                                this.$refs.sectionUpdateOrCreateModal.toggle();
                            })
                    },

                    resetSectionForm() {
                        this.section = {};
                    },

                    deleteSection(url) {
                        this.$emitter.emit('open-confirm-modal', {
                            message: 'Are you sure you want to delete section and also delete this all settings and values?',
                            agree: () => {
                                this.$axios.delete(url)
                                .then(response => {
                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                    this.$refs.datagrid.get();
                                })
                                .catch((error) => {
                                    this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                                });
                            }
                        });
                    }
                },
            });
        </script>

        <!-- v customization repeater item form template -->
        <script type="text/x-template" id="v-cust-section-repeater-item-template">
            <div class="flex justify-between mt-4">
                <div class="flex gap-4 flex-1 max-sm:flex-wrap max-sm:flex-1 p-2 rounded box-shadow" style="overflow-y: auto; max-height: 185px;">

                    <div v-if="repeater_fields.length" class="grid grid-cols-2 gap-4 pt-2">

                        <div v-for="repeater_field in repeater_fields">

                            <div v-if="repeater_field.type == 'text'" class="mb-4" >

                                <span v-if="repeater_field.required == true">

                                    <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium required">@{{ repeater_field.title }}</label>

                                    <input 
                                        :type="repeater_field.type"
                                        :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                        required="required"
                                        v-model="single_repeater[repeater_field.name]"
                                        class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                        :placeholder="repeater_field.title"
                                    >

                                </span>

                                <span v-else>

                                    <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium">@{{ repeater_field.title }}</label>

                                    <input 
                                        :type="repeater_field.type"
                                        :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                        v-model="single_repeater[repeater_field.name]"
                                        class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                        :placeholder="repeater_field.title"
                                    >
                                    
                                </span>

                            </div>

                            <div v-if="repeater_field.type == 'textarea'" class="mb-4" >

                                <span v-if="repeater_field.required == true">

                                    <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium required">@{{ repeater_field.title }}</label>

                                    <textarea 
                                        :type="repeater_field.type" 
                                        :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                        class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800" 
                                        :placeholder="repeater_field.title"
                                        required="required"
                                        v-model="single_repeater[repeater_field.name]"
                                    ></textarea>

                                </span>

                                <span v-else>

                                    <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium">@{{ repeater_field.title }}</label>

                                    <textarea 
                                        :type="repeater_field.type" 
                                        :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                        class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800" 
                                        :placeholder="repeater_field.title"
                                        v-model="single_repeater[repeater_field.name]"
                                    ></textarea>

                                </span>

                            </div>

                            <div v-if="repeater_field.type == 'file'" class="mb-4" >

                                <div v-if="repeater_field.required == true">
                                    
                                    <div v-if="single_repeater[repeater_field.name]">

                                        <label 
                                            class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium"
                                        ><div v-html="repeater_field.title"></div></label>

                                        <div v-if="repeater_field.multiple == true">

                                            <input 
                                                :type="repeater_field.type"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + '][]']"
                                                value=""
                                                class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                                multiple="multiple"
                                            >

                                        </div>

                                        <div v-else>

                                            <input 
                                                :type="repeater_field.type"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                                value=""
                                                class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                            >

                                        </div>

                                    </div>

                                    <div v-else>

                                        <label 
                                            class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium required"
                                        ><div v-html="repeater_field.title"></div></label>

                                        <div v-if="repeater_field.multiple == true">

                                            <input 
                                                :type="repeater_field.type"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + '][]']"
                                                value=""
                                                class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                                required="required"
                                                multiple="multiple"
                                            >

                                        </div>

                                        <div v-else>

                                            <input 
                                                :type="repeater_field.type"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                                value=""
                                                class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                                required="required"
                                            >

                                        </div>

                                    </div>

                                </div>

                                <div v-else>

                                    <label 
                                        class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium"
                                    ><div v-html="repeater_field.title"></div></label>

                                    <div v-if="repeater_field.multiple == true">

                                        <input 
                                            :type="repeater_field.type"
                                            :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + '][]']"
                                            value=""
                                            class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                            multiple="multiple"
                                        >

                                    </div>

                                    <div v-else>

                                        <input 
                                            :type="repeater_field.type"
                                            :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                            value=""
                                            class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800"
                                        >

                                    </div>

                                </div>

                                <div v-if="repeater_field.multiple == true">

                                    <div 
                                        v-if="single_repeater[repeater_field.name]"
                                        class="flex justify-start items-center"
                                    >

                                        <div 
                                            v-if="Array.isArray(single_repeater[repeater_field.name])"
                                            v-for="(file_path, file_index) in single_repeater[repeater_field.name]"
                                            class="mt-2"
                                        >
                                            <a 
                                                :href="pre_file_url + file_path" 
                                                target="_blank"
                                            >
                                                <img 
                                                    :src="pre_file_url + file_path" 
                                                    class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                >
                                            </a>

                                            <input 
                                                type="hidden"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][hidden][' + repeater_field.name + '][' + file_index + ']']"
                                                v-model="file_path"
                                            >

                                        </div>

                                        <div v-else class="mt-2" >

                                            <a 
                                                :href="pre_file_url + single_repeater[repeater_field.name]" 
                                                target="_blank"
                                            >
                                                <img 
                                                    :src="pre_file_url + single_repeater[repeater_field.name]" 
                                                    class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                >
                                            </a>

                                            <input 
                                                type="hidden"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][hidden][' + repeater_field.name + ']']"
                                                v-model="single_repeater[repeater_field.name]"
                                            >

                                        </div>

                                    </div>

                                </div>

                                <div v-else>

                                    <div 
                                        v-if="single_repeater[repeater_field.name]"
                                        class="flex justify-start items-center"
                                    >
                                        <div 
                                            v-if="Array.isArray(single_repeater[repeater_field.name])"
                                            v-for="(file_path, file_index) in single_repeater[repeater_field.name]"
                                            class="mt-2"
                                        >
                                            <a 
                                                :href="pre_file_url + file_path" 
                                                target="_blank"
                                            >
                                                <img 
                                                    :src="pre_file_url + file_path" 
                                                    class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                >
                                            </a>

                                            <input 
                                                type="hidden"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][hidden][' + repeater_field.name + '][' + file_index + ']']"
                                                v-model="file_path"
                                            >

                                        </div>

                                        <div v-else class="mt-2" >

                                            <a 
                                                :href="pre_file_url + single_repeater[repeater_field.name]" 
                                                target="_blank"
                                            >
                                                <img 
                                                    :src="pre_file_url + single_repeater[repeater_field.name]" 
                                                    class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                                                >
                                            </a>

                                            <input 
                                                type="hidden"
                                                :name="['repeater_data[' + repeater_name + '][' + index + '][hidden][' + repeater_field.name + ']']"
                                                v-model="single_repeater[repeater_field.name]"
                                            >

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div v-if="repeater_field.type == 'select'" class="mb-4" >

                                <span v-if="repeater_field.required == true">

                                    <label 
                                        class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium required"
                                    ><div v-html="repeater_field.title"></div></label>

                                    <div v-if="repeater_field.multiple == true">

                                        <select
                                            :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                            class="custom-select flex w-full min:w-1/2 h-10 py-1.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 ltr:pr-10 rtl:pl-10"
                                            v-model="single_repeater[repeater_field.name]"
                                            required="required"
                                            multiple="multiple"
                                        >
                                            <option
                                                v-for='(rp_option_val, rp_option_key) in repeater_field.options'
                                                :value="rp_option_key"
                                                :text="rp_option_val"
                                            >
                                            </option>

                                        </select>

                                    </div>

                                    <div v-else>

                                        <select
                                            :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                            class="custom-select flex w-full min:w-1/2 h-10 py-1.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 ltr:pr-10 rtl:pl-10"
                                            v-model="single_repeater[repeater_field.name]"
                                            required="required"
                                        >
                                            <option value="">@{{ repeater_field.title }}</option>

                                            <option
                                                v-for='(rp_option_val, rp_option_key) in repeater_field.options'
                                                :value="rp_option_key"
                                                :text="rp_option_val"
                                            >
                                            </option>

                                        </select>

                                    </div>

                                </span>

                                <span v-else>
                                
                                    <label 
                                        class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium"
                                    ><div v-html="repeater_field.title"></div></label>

                                    <div v-if="repeater_field.multiple == true">

                                        <select
                                            :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                            class="custom-select flex w-full min:w-1/2 h-10 py-1.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 ltr:pr-10 rtl:pl-10"
                                            v-model="single_repeater[repeater_field.name]"
                                            multiple="multiple"
                                        >
                                            <option
                                                v-for='(rp_option_val, rp_option_key) in repeater_field.options'
                                                :value="rp_option_key"
                                                :text="rp_option_val"
                                            >
                                            </option>

                                        </select>

                                    </div>

                                    <div v-else>

                                        <select
                                            :name="['repeater_data[' + repeater_name + '][' + index + '][' + repeater_field.name + ']']"
                                            class="custom-select flex w-full min:w-1/2 h-10 py-1.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 ltr:pr-10 rtl:pl-10"
                                            v-model="single_repeater[repeater_field.name]"
                                        >
                                            <option value="">@{{ repeater_field.title }}</option>

                                            <option
                                                v-for='(rp_option_val, rp_option_key) in repeater_field.options'
                                                :value="rp_option_key"
                                                :text="rp_option_val"
                                            >
                                            </option>

                                        </select>

                                    </div>
                                    

                                </span>

                            </div>

                        </div>

                    </div>

                </div>

                <span
                    class="icon-delete max-h-9 max-w-9 text-2xl p-1.5 rounded-md cursor-pointer transition-all hover:bg-gray-100 dark:hover:bg-gray-950 max-sm:place-self-center"
                    @click="removeRepeaterItem(repeater_name)"
                >
                </span>
            </div>
            
        </script>

        <!-- v customization repeater item component -->
        <script type="module">
            app.component('v-cust-section-repeater-item', {
                template: "#v-cust-section-repeater-item-template",

                props: ['index', 'single_repeater', 'repeater_fields', 'repeater_name', 'pre_file_url'],

                data() {
                    return {
                        repeater_field: null
                    }
                },

                computed: {
                    
                },

                methods: {
                    removeRepeaterItem(repeater_name) {
                        this.$emit('onRemoveRepeaterItem', this.single_repeater, repeater_name)
                    },
                }
            });
        </script>

    @endPushOnce
</x-admin::layouts>
