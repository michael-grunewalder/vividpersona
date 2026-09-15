@php
    $catalog = [
        'niches' => \App\Support\PersonaOptions::NICHES,
        'vibes' => \App\Support\PersonaOptions::VIBES,
        'skinTones' => \App\Support\PersonaOptions::SKIN_TONES,
        'hairColors' => \App\Support\PersonaOptions::HAIR_COLORS,
        'hairLengthsFemale' => \App\Support\PersonaOptions::HAIR_LENGTHS_FEMALE,
        'hairLengthsMale' => \App\Support\PersonaOptions::HAIR_LENGTHS_MALE,
        'hairTextures' => \App\Support\PersonaOptions::HAIR_TEXTURES,
        'eyeColors' => \App\Support\PersonaOptions::EYE_COLORS,
        'buildsFemale' => \App\Support\PersonaOptions::BUILDS_FEMALE,
        'buildsMale' => \App\Support\PersonaOptions::BUILDS_MALE,
        'ethnicities' => \App\Support\PersonaOptions::ETHNICITIES,
        'aspectRatios' => \App\Support\PersonaOptions::ASPECT_RATIOS,
        'providers' => $providers
            ->map(fn ($item) => [
                'machine_name' => $item['service']->value,
                'friendly_name' => $item['service']->label(),
                'models' => $item['models'],
            ])
            ->values()
            ->all(),
        'llmProviders' => $llmProviders
            ->map(fn ($item) => ['value' => $item->value, 'label' => $item->label()])
            ->values()
            ->all(),
    ];
    $steps = ['Basics', 'References', 'Story', 'Look', 'Generate'];
@endphp

<x-layouts.dashboard :title="__('personas.create_title')">
    <x-page-header :title="__('personas.create_title')" :subtitle="__('personas.create_subtitle')" />

    <form
        method="POST"
        action="{{ route('personas.store') }}"
        enctype="multipart/form-data"
        x-data="{
            catalog: {{ Illuminate\Support\Js::from($catalog) }},
            data: {
                name: '', gender: 'Female', age: '', niches: [], nicheCustom: '',
                backstory: '', personality: 50,
                ethnicity: '', skinTone: '', hairColor: '', hairLength: 'Short', hairTexture: 'Straight',
                eyeColor: '', build: 'Petite', uniqueFeatures: '',
                vibeWords: '', aspectRatio: '9:16', provider: '', model: '', llmProvider: '', enhance: false,
            },
            step: 1,
            init() {
                if (this.catalog.providers.length) {
                    this.data.provider = this.catalog.providers[0].machine_name;
                    this.data.model = Object.keys(this.catalog.providers[0].models)[0] || '';
                }
            },
            next() {
                if (this.step === 1 && (! this.data.name || ! this.data.gender)) return;
                if (this.step === 1 && this.data.age && Number(this.data.age) < 18) return;
                if (this.step === 5) { this.$root.submit(); return; }
                this.step = Math.min(this.step + 1, 5);
            },
            prev() { this.step = Math.max(this.step - 1, 1); },
            randomize() {
                this.data.ethnicity = this.catalog.ethnicities[Math.floor(Math.random() * this.catalog.ethnicities.length)];
                this.data.skinTone = this.catalog.skinTones[Math.floor(Math.random() * this.catalog.skinTones.length)];
                this.data.hairColor = this.catalog.hairColors[Math.floor(Math.random() * this.catalog.hairColors.length)];
                this.data.hairLength = this.hairLengths[Math.floor(Math.random() * this.hairLengths.length)];
                this.data.hairTexture = this.catalog.hairTextures[Math.floor(Math.random() * this.catalog.hairTextures.length)];
                this.data.eyeColor = this.catalog.eyeColors[Math.floor(Math.random() * this.catalog.eyeColors.length)];
                this.data.build = this.builds[Math.floor(Math.random() * this.builds.length)];
            },
            get models() {
                const p = this.catalog.providers.find(p => p.machine_name === this.data.provider);
                return p ? p.models : {};
            },
            get hairLengths() { return this.data.gender === 'Male' ? this.catalog.hairLengthsMale : this.catalog.hairLengthsFemale; },
            get builds() { return this.data.gender === 'Male' ? this.catalog.buildsMale : this.catalog.buildsFemale; },
            get vibes() {
                const g = this.data.gender;
                return this.catalog.vibes.filter(v => v.genders.length === 0 || v.genders.includes(g));
            },
            toggleVibe(v) {
                this.data.vibeWords = this.data.vibeWords === v ? '' : v;
            },
        }"
        class="mt-8"
    >
        @csrf

        <input type="hidden" name="name" x-model="data.name" />
        <input type="hidden" name="gender" x-model="data.gender" />
        <input type="hidden" name="age" x-model="data.age" />
        <input type="hidden" name="niche_custom" x-model="data.nicheCustom" />
        <input type="hidden" name="backstory" x-model="data.backstory" />
        <input type="hidden" name="personality" x-model="data.personality" />
        <input type="hidden" name="ethnicity" x-model="data.ethnicity" />
        <input type="hidden" name="skin_tone" x-model="data.skinTone" />
        <input type="hidden" name="hair_color" x-model="data.hairColor" />
        <input type="hidden" name="hair_length" x-model="data.hairLength" />
        <input type="hidden" name="hair_texture" x-model="data.hairTexture" />
        <input type="hidden" name="eye_color" x-model="data.eyeColor" />
        <input type="hidden" name="build" x-model="data.build" />
        <input type="hidden" name="unique_features" x-model="data.uniqueFeatures" />
        <input type="hidden" name="vibe_words" x-model="data.vibeWords" />
        <input type="hidden" name="aspect_ratio" x-model="data.aspectRatio" />
        <input type="hidden" name="provider" x-model="data.provider" />
        <input type="hidden" name="model" x-model="data.model" />
        <input type="hidden" name="llm_provider" x-model="data.llmProvider" />
        <input type="hidden" name="enhance" value="1" x-show="data.enhance" />

        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            @foreach ($steps as $i => $label)
                <button
                    type="button"
                    class="btn btn-sm {{ $loop->first ? '' : '' }}"
                    :class="step === {{ $i + 1 }} ? 'btn-primary' : 'btn-ghost'"
                >{{ $i + 1 }}. {{ __('personas.step_'.strtolower($label)) }}</button>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- Step 1: Basics --}}
            <x-card x-show="step === 1" class="lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-forms.input name="name" :label="__('personas.name')" x-model="data.name" placeholder="Kayla" required />
                    <x-forms.select
                        name="gender"
                        :label="__('personas.gender')"
                        :options="['Female' => __('personas.female'), 'Male' => __('personas.male')]"
                        x-model="data.gender"
                        @change="data.hairLength = hairLengths[0] || data.hairLength; data.build = builds[0] || data.build"
                        :selected="old('gender')"
                    />
                    <x-forms.input name="age" type="number" min="18" :label="__('personas.age')" x-model="data.age" required />
                </div>

                <div class="mt-4">
                    <x-forms.label>{{ __('personas.niches') }}</x-forms.label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="n in catalog.niches" :key="n">
                            <label class="cursor-pointer">
                                <input type="checkbox" name="niches[]" :value="n" x-model="data.niches" class="peer hidden" />
                                <span class="badge badge-lg badge-outline peer-checked:badge-primary transition" x-text="n"></span>
                            </label>
                        </template>
                    </div>
                    <div class="mt-3" x-show="data.niches.includes('Other')">
                        <x-forms.input name="niche_custom" :label="__('personas.niche_custom')" x-model="data.nicheCustom" />
                    </div>
                </div>

                <p class="mt-4 text-xs text-error" x-show="step === 1 && data.age && Number(data.age) < 18">{{ __('personas.age_min') }}</p>
            </x-card>

            {{-- Step 2: References --}}
            <x-card :title="__('personas.refs_title')" x-show="step === 2" class="lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-forms.label>{{ __('personas.face_ref') }}</x-forms.label>
                        <input type="file" name="face_ref" accept="image/*" class="file-input file-input-bordered file-input-sm mt-2 w-full" />
                    </div>
                    <div>
                        <x-forms.label>{{ __('personas.style_ref') }}</x-forms.label>
                        <input type="file" name="style_ref" accept="image/*" class="file-input file-input-bordered file-input-sm mt-2 w-full" />
                    </div>
                </div>
                <p class="mt-3 text-xs text-base-content/50">{{ __('personas.refs_hint') }}</p>
            </x-card>

            {{-- Step 3: Story --}}
            <x-card :title="__('personas.story_title')" x-show="step === 3" class="lg:col-span-2">
                <div class="space-y-1.5">
                    <x-forms.label>{{ __('personas.backstory') }}</x-forms.label>
                    <textarea name="backstory" x-model="data.backstory" rows="4" class="textarea textarea-bordered w-full" placeholder="{{ __('personas.backstory_placeholder') }}"></textarea>
                </div>

                <div class="mt-4">
                    <x-forms.label>{{ __('personas.personality') }}</x-forms.label>
                    <input type="range" min="0" max="100" x-model.number="data.personality" class="range range-primary mt-2 w-full" />
                    <p class="mt-1 text-sm text-base-content/60" x-text="data.personality + ' — ' + (data.personality < 35 ? '{{ __('personas.introverted') }}' : (data.personality > 65 ? '{{ __('personas.extroverted') }}' : '{{ __('personas.balanced') }}'))"></p>
                </div>
            </x-card>

            {{-- Step 4: Look --}}
            <x-card :title="__('personas.look_title')" x-show="step === 4" class="lg:col-span-2">
                <div class="flex justify-end">
                    <x-button type="button" size="sm" variant="outline" @click="randomize()">🎲 {{ __('personas.randomize') }}</x-button>
                </div>

                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <x-forms.select name="ethnicity" :label="__('personas.ethnicity')" :options="collect($catalog['ethnicities'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.ethnicity" />
                    <x-forms.select name="skin_tone" :label="__('personas.skin_tone')" :options="collect($catalog['skinTones'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.skinTone" />
                    <x-forms.select name="hair_color" :label="__('personas.hair_color')" :options="collect($catalog['hairColors'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.hairColor" />
                    <x-forms.select name="hair_length" :label="__('personas.hair_length')" :options="[]" x-model="data.hairLength">
                        <template x-for="h in hairLengths" :key="h">
                            <option :value="h" :selected="h === data.hairLength" x-text="h"></option>
                        </template>
                    </x-forms.select>
                    <x-forms.select name="hair_texture" :label="__('personas.hair_texture')" :options="collect($catalog['hairTextures'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.hairTexture" />
                    <x-forms.select name="eye_color" :label="__('personas.eye_color')" :options="collect($catalog['eyeColors'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.eyeColor" />
                    <x-forms.select name="build" :label="__('personas.build')" :options="[]" x-model="data.build">
                        <template x-for="b in builds" :key="b">
                            <option :value="b" :selected="b === data.build" x-text="b"></option>
                        </template>
                    </x-forms.select>
                    <x-forms.input name="unique_features" :label="__('personas.unique_features')" x-model="data.uniqueFeatures" />
                </div>

                <div class="mt-5">
                    <x-forms.label>{{ __('personas.vibe') }}</x-forms.label>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <template x-for="v in vibes" :key="v.id">
                            <button type="button" class="rounded-box border p-3 text-left transition"
                                :class="data.vibeWords === v.id ? 'border-primary bg-primary/10' : 'border-base-300 hover:bg-base-200'"
                                @click="toggleVibe(v.id)">
                                <span class="block text-sm font-semibold" x-text="v.id"></span>
                                <span class="block text-xs text-base-content/60" x-text="v.sub"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </x-card>

            {{-- Step 5: Generate --}}
            <x-card :title="__('personas.generate_title')" x-show="step === 5" class="lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-forms.select name="provider" :label="__('personas.provider')" :options="collect($catalog['providers'])->mapWithKeys(fn($p) => [$p['machine_name'] => $p['friendly_name']])->all()" x-model="data.provider" @change="data.model = (() => { const p = catalog.providers.find(p => p.machine_name === $el.value); return p && p.models ? Object.keys(p.models)[0] || '' : ''; })()" />
                    <x-forms.select name="model" :label="__('personas.model')" :options="[]" x-model="data.model">
                        <template x-for="(label, key) in models" :key="key">
                            <option :value="key" :selected="key === data.model" x-text="label"></option>
                        </template>
                    </x-forms.select>
                    <x-forms.select name="aspect_ratio" :label="__('personas.aspect_ratio')" :options="['9:16' => '9:16', '16:9' => '16:9']" x-model="data.aspectRatio" />
                </div>

                <div class="mt-4 max-w-xl" x-show="catalog.llmProviders.length">
                    <x-forms.select
                        name="llm_provider"
                        :label="__('personas.ai_provider')"
                        :options="collect(['' => __('personas.ai_provider_default')])->union($llmProviders->mapWithKeys(fn ($item) => [$item->value => $item->label()]))->all()"
                        x-model="data.llmProvider"
                    />
                </div>

                <label class="mt-4 flex cursor-pointer items-center gap-2 text-sm text-base-content/70">
                    <input type="checkbox" name="enhance" value="1" x-model="data.enhance" class="checkbox checkbox-sm" />
                    {{ __('personas.enhance') }}
                </label>

                <p class="mt-2 text-xs text-base-content/50" x-show="! data.provider">{{ __('personas.connect_hint') }}</p>
            </x-card>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <x-button type="button" variant="ghost" @click="prev()" x-show="step > 1">{{ __('personas.back') }}</x-button>
            <x-button type="button" @click="next()" x-text="step === 5 ? '{{ __('personas.create') }}' : '{{ __('personas.next') }}'">{{ __('personas.next') }}</x-button>
        </div>
    </form>
</x-layouts.dashboard>