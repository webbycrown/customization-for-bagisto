<x-admin::layouts>
    <x-slot:title>
        {{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] . ' Settings' : __('Customization Sections Setting') }}{{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? ' of ' . $customization_page[ 'title' ] . ' Page' : __(' of Page') }}
    </x-slot:title>

    <v-wc-setting>
    
        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                {{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] . ' Settings' : __('Customization Sections Setting') }}{{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? ' of ' . $customization_page[ 'title' ] . ' Page' : __(' of Page') }}
            </p>

            <div class="flex gap-x-[10px] items-center">
                <!-- Back Button -->
                <a  href="{{ route('wc_customization.admin.customization.pages.index', $page_slug) }}" 
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                >Back</a>

                <button
                    type="button"
                    class="primary-button"
                    >
                    {{ __('Create Setting') }}
                </button>
            </div>        
        </div>
    
    </v-wc-setting>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-wc-setting-template"
        >

            <div class="flex  gap-4 justify-between items-center max-sm:flex-wrap">
                <p class="text-xl text-gray-800 dark:text-white font-bold">
                    {{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] . ' Settings' : __('Customization Sections Setting') }}{{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? ' of ' . $customization_page[ 'title' ] . ' Page' : __(' of Page') }}
                </p>

                <div class="flex gap-x-2.5 items-center">
                    <a  href="{{ route('wc_customization.admin.customization.pages.index', $page_slug) }}" 
                        class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                    >Back</a>

                    <!-- Setting Create Button -->
                    <button
                        type="button"
                        class="primary-button"
                        @click="resetForm();$refs.sectionSettingUpdateOrCreateModal.toggle()"
                    >
                        {{ __('Create Setting') }}
                    </button>
                </div>
            </div>

            <x-admin::datagrid :src="route('wc_customization.admin.customization.sections.setting', [ $page_slug, $section_slug ])" ref="datagrid">
                <!-- DataGrid Body -->
                <template #body="{ columns, records, performAction }">
                    <div
                        v-for="record in records"
                        class="row grid gap-2.5 items-center px-4 py-4 border-b dark:border-gray-800 text-gray-600 dark:text-gray-300 transition-all hover:bg-gray-50 dark:hover:bg-gray-950"
                        :style="`grid-template-columns: repeat(${gridsCount}, minmax(0, 1fr))`"
                    >

                        <p v-text="record.id"></p>

                        <p v-text="record.title"></p>

                        <p v-text="record.name"></p>

                        <p v-text="record.type"></p>
                        
                        <p v-text="record.required"></p>
                        
                        <p v-text="record.multiple"></p>
                        
                        <p v-text="record.status"></p>

                        <!-- Actions -->
                        <div class="flex justify-end">
                            <a @click="editModal(record.actions.find(action => action.index === 'setting_edit')?.url)">
                                <span
                                    :class="record.actions.find(action => action.index === 'setting_edit')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                >
                                </span>
                            </a>
                            <span v-if="record.type == 'repeater'">
                                <a :href="record.actions.find(action => action.index === 'repeater_section_settings')?.url">
                                    <span
                                        :class="record.actions.find(action => action.index === 'repeater_section_settings')?.icon"
                                        class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    >
                                    </span>
                                </a>
                            </span>

                            <a @click="deleteSetting(record.actions.find(action => action.index === 'setting_delete')?.url)">
                                <span
                                    :class="record.actions.find(action => action.index === 'setting_delete')?.icon"
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
                    @submit="handleSubmit($event, updateOrCreateSectionSetting)"
                    ref="createSectionSetingForm"
                >

                    <x-admin::modal ref="sectionSettingUpdateOrCreateModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg text-gray-800 dark:text-white font-bold">
                                <span>{{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] . ' Settings' : __('Customization Sections Setting') }}{{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? ' of ' . $customization_page[ 'title' ] . ' Page' : __(' of Page') }}</span>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="section_setting_id"
                                id="section_setting_id"
                                v-model="section_setting_data.id"
                            />

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="page_slug"
                                v-model="page_slug"
                            />

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="section_slug"
                                v-model="section_slug"
                            />

                            <x-admin::form.control-group class="field_status_main">
                                <x-admin::form.control-group.label class="text-gray-800 dark:text-white font-medium">
                                    {{ __('Visible in Section?') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="field_status"
                                    class="cursor-pointer field_status"
                                    name="field_status"
                                    v-model="section_setting_data.status"
                                    value="1"
                                    label="Visible in Section?"
                                >
                                    <!-- Options -->
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="field_status" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required text-gray-800 dark:text-white font-medium required">
                                    {{ __('Select Field Type') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="field_type"
                                    class="cursor-pointer field_type"
                                    name="field_type"
                                    rules="required"
                                    v-model="section_setting_data.type"
                                    label="Select Field Type"
                                    placeholder="Select Field Type"
                                    @change="changeFieldType($event)"
                                >
                                    <!-- Options -->
                                    <option value="">Select Field Type</option>
                                    @if( $types && is_array( $types ) && count( $types ) > 0 )
                                        @foreach( $types as $type_data )
                                            <option value="{{ $type_data[ 'option_key' ] }}">{{ $type_data[ 'option_val' ] }}</option>
                                        @endforeach
                                    @endif
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="field_type" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    {{ __('Field Title') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="field_title"
                                    name="field_title"
                                    class="field_title"
                                    rules="required"
                                    v-model="section_setting_data.title"
                                    label="Field Title"
                                    placeholder="Field Title"
                                />

                                <x-admin::form.control-group.error control-name="field_title" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group id="field_name_main">
                                <x-admin::form.control-group.label class="required">
                                    {{ __('Field Slug') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="field_name"
                                    name="field_name"
                                    class="field_name"
                                    rules="required"
                                    v-model="section_setting_data.name"
                                    label="Field Name"
                                    placeholder="Field Name"
                                    @change="changeFieldCode($event)"
                                />
                                
                                <p class="text-sm text-gray-500 mt-1">
                                    Enter without any special character and white space.
                                </p>

                                <x-admin::form.control-group.error control-name="field_name" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group class="field_required_main">
                                <x-admin::form.control-group.label class="required text-gray-800 dark:text-white font-medium">
                                    {{ __('Field is Required?') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="field_required"
                                    class="cursor-pointer field_required"
                                    name="field_required"
                                    rules="required"
                                    v-model="section_setting_data.required"
                                    value="0"
                                    label="Field is Required?"
                                >
                                    <!-- Options -->
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="field_required" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group class="field_multiple_main hidden">
                                <x-admin::form.control-group.label class="text-gray-800 dark:text-white font-medium">
                                    {{ __('Field is Multiple?') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="field_multiple"
                                    class="cursor-pointer field_multiple"
                                    name="field_multiple"
                                    v-model="section_setting_data.multiple"
                                    value="0"
                                    label="Field is Multiple?"
                                >
                                    <!-- Options -->
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="field_multiple" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group class="field_option_main hidden">
                                <x-admin::form.control-group.label class="text-gray-800 dark:text-white font-medium">
                                    {{ __('Field Options') }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="field_option"
                                    name="field_option"
                                    class="field_option"
                                    v-model="section_setting_data.options"
                                    value=""
                                    label="Field Options"
                                />

                                <p class="text-sm text-gray-500 mt-1">
                                    Enter each option on a new line and value like this ( option_value : option_label )
                                </p>

                                <x-admin::form.control-group.error control-name="field_option" />
                            </x-admin::form.control-group>

                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <div class="flex gap-x-2.5 items-center">
                                <button
                                    type="submit"
                                    class="primary-button btn_field_save"
                                >Save</button>
                            </div>
                        </x-slot>
                    </x-admin::modal>

                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-wc-setting', {
                template: '#v-wc-setting-template',

                data() {
                    return {
                        section_setting_data: {
                            options: null,
                            page_slug: null,
                            section_slug: null,
                            image: [],
                        },

                        page_slug: '{{$page_slug}}',
                        
                        section_slug: '{{$section_slug}}',
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

                    changeFieldType(e) {
                        var field_type_val = e.target.value ? e.target.value : null;
                        if ( field_type_val == 'select' ) {
                            document.querySelector('.field_required_main').classList.remove('hidden');
                            document.querySelector('.field_option_main').classList.remove('hidden');
                            document.querySelector('.field_multiple_main').classList.remove('hidden');
                        } else if ( field_type_val == 'file' || field_type_val == 'product' || field_type_val == 'category' || field_type_val == 'category_product' || field_type_val == 'blog' ) {
                            document.querySelector('.field_required_main').classList.remove('hidden');
                            document.querySelector('.field_multiple_main').classList.remove('hidden');
                            document.querySelector('.field_option_main').classList.add('hidden');
                        } else if ( field_type_val == 'repeater' ) {
                            document.querySelector('.field_required_main').classList.add('hidden');
                            document.querySelector('.field_option_main').classList.add('hidden');
                            document.querySelector('.field_multiple_main').classList.add('hidden');
                        } else {
                            document.querySelector('.field_required_main').classList.remove('hidden');
                            document.querySelector('.field_option_main').classList.add('hidden');
                            document.querySelector('.field_multiple_main').classList.add('hidden');
                        }
                    },

                    changeFieldCode(e) {

                        var field_code_val = e.target.value ? e.target.value : null;
                        var page = '{{$page_slug}}';
                        var section = '{{$section_slug}}';
                        var section_setting_id = document.getElementById('section_setting_id').value;
                        var field_name_already = document.getElementById('field_name_already');

                        if (field_name_already) {
                            field_name_already.remove();
                        }

                        let formData = new FormData();
                        formData.append( 'field_code', field_code_val );
                        formData.append( 'page_slug', page );
                        formData.append( 'section_slug', section );
                        formData.append( 'section_setting_id', section_setting_id );

                        this.$axios.post("{{ route('wc_customization.section.setting.validate') }}", formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then((response) => {
                            if ( response.data.status_code == 200 && response.data.status == 'success' ) {

                                document.querySelector('.btn_field_save').classList.add('hidden');
                                var newP = document.createElement('p');
                                newP.className = 'mt-1 text-red-600 text-xs italic';
                                newP.id = 'field_name_already';
                                newP.innerHTML = response.data.message;
                                document.getElementById("field_name_main").appendChild(newP);

                            } else {
                                document.querySelector('.btn_field_save').classList.remove('hidden');
                            }
                        });
                    },

                    updateOrCreateSectionSetting(params, { resetForm, setErrors  }) {
                        let formData = new FormData(this.$refs.createSectionSetingForm);

                        this.$axios.post("{{ route('wc_customization.section.setting.store') }}", formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then((response) => {
                            this.$refs.sectionSettingUpdateOrCreateModal.close();

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
                                this.section_setting_data = {
                                    ...response.data.data,
                                        
                                        options : response.data.data.type == 'select' 
                                        ? response.data.data.other_settings.options 
                                        : null,
                                        
                                };

                                this.page_slug = '{{$page_slug}}';
                                this.section_slug = '{{$section_slug}}';

                                this.$refs.sectionSettingUpdateOrCreateModal.toggle();

                                setTimeout(function() {
                                    const changeEvent = new Event('change', {
                                        bubbles: true,
                                        cancelable: false
                                    });
                                    const inputElement = document.getElementById('field_type');
                                    if (inputElement) {
                                        inputElement.dispatchEvent(changeEvent);
                                    } else {
                                        console.log('Element with ID "field_type" not found.');
                                    }
                                }, 100);

                            })
                    },

                    resetForm() {
                        this.section_setting_data = {
                            image: [],
                        };
                        this.page_slug = '{{$page_slug}}';
                        this.section_slug = '{{$section_slug}}';
                    },

                    deleteSetting(url) {
                        this.$emitter.emit('open-confirm-modal', {
                            message: 'Are you sure you want to delete section setting and also delete this value?',
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
    @endPushOnce

</x-admin::layouts>