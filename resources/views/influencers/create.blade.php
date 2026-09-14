@php
    $catalog = [
        'niches' => \App\Support\InfluencerOptions::NICHES,
        'vibes' => \App\Support\InfluencerOptions::VIBES,
        'skinTones' => \App\Support\InfluencerOptions::SKIN_TONES,
        'hairColors' => \App\Support\InfluencerOptions::HAIR_COLORS,
        'hairLengthsFemale' => \App\Support\InfluencerOptions::HAIR_LENGTHS_FEMALE,
        'hairLengthsMale' => \App\Support\InfluencerOptions::HAIR_LENGTHS_MALE,
        'hairTextures' => \App\Support\InfluencerOptions::HAIR_TEXTURES,
        'eyeColors' => \App\Support\InfluencerOptions::EYE_COLORS,
        'buildsFemale' => \App\Support\InfluencerOptions::BUILDS_FEMALE,
        'buildsMale' => \App\Support\InfluencerOptions::BUILDS_MALE,
        'ethnicities' => \App\Support\InfluencerOptions::ETHNICITIES,
        'aspectRatios' => \App\Support\InfluencerOptions::ASPECT_RATIOS,
        'providers' => $providers
            ->map(fn ($item) => [
                'machine_name' => $item['provider']->machine_name,
                'friendly_name' => $item['provider']->friendly_name,
                'models' => $item['models'],
            ])
            ->values()
            ->all(),
    ];
    $steps = ['Basics', 'References', 'Story', 'Look', 'Generate'];
@endphp

<x-layouts.dashboard :title="__('influencers.create_title')">
    <x-page-header :title="__('influencers.create_title')" :subtitle="__('influencers.create_subtitle')" />

    <form
        method="POST"
        action="{{ route('influencers.store') }}"
        enctype="multipart/form-data"
        x-data="{
            catalog: {{ Illuminate\Support\Js::from($catalog) }},
            data: {
                name: '', gender: 'Female', age: '', niches: [], nicheCustom: '',
                backstory: '', personality: 50,
                ethnicity: '', skinTone: '', hairColor: '', hairLength: 'Short', hairTexture: 'Straight',
                eyeColor: '', build: 'Petite', uniqueFeatures: '',
                vibeWords: '', aspectRatio: '9:16', provider: '', model: '', enhance: false,
            },
            step: 1,
            next() {
                if (this.step === 1 && (! this.data.name || ! this.data.gender)) return;
                if (this.step === 1 && this.data.age && Number(this.data.age) < 18) return;
                if (this.step === 5) { this.$el.submit(); return; }
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
        <input type="hidden" name="enhance" value="1" x-show="data.enhance" />

        <div class="flex items-center gap-2 overflow-x-auto pb-2">
            @foreach ($steps as $i => $label)
                <button
                    type="button"
                    class="btn btn-sm {{ $loop->first ? '' : '' }}"
                    :class="step === {{ $i + 1 }} ? 'btn-primary' : 'btn-ghost'"
                >{{ $i + 1 }}. {{ __('influencers.step_'.strtolower($label)) }}</button>
            @endforeach
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            {{-- Step 1: Basics --}}
            <x-card x-show="step === 1" class="lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-forms.input name="name" :label="__('influencers.name')" x-model="data.name" placeholder="Kayla" required />
                    <x-forms.select
                        name="gender"
                        :label="__('influencers.gender')"
                        :options="['Female' => __('influencers.female'), 'Male' => __('influencers.male')]"
                        x-model="data.gender"
                        @change="data.hairLength = hairLengths[0] || data.hairLength; data.build = builds[0] || data.build"
                        :selected="old('gender')"
                    />
                    <x-forms.input name="age" type="number" min="18" :label="__('influencers.age')" x-model="data.age" required />
                </div>

                <div class="mt-4">
                    <x-forms.label>{{ __('influencers.niches') }}</x-forms.label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="n in catalog.niches" :key="n">
                            <label class="cursor-pointer">
                                <input type="checkbox" name="niches[]" :value="n" x-model="data.niches" class="peer hidden" />
                                <span class="badge badge-lg badge-outline peer-checked:badge-primary transition" x-text="n"></span>
                            </label>
                        </template>
                    </div>
                    <div class="mt-3" x-show="data.niches.includes('Other')">
                        <x-forms.input name="niche_custom" :label="__('influencers.niche_custom')" x-model="data.nicheCustom" />
                    </div>
                </div>

                <p class="mt-4 text-xs text-error" x-show="step === 1 && data.age && Number(data.age) < 18">{{ __('influencers.age_min') }}</p>
            </x-card>

            {{-- Step 2: References --}}
            <x-card :title="__('influencers.refs_title')" x-show="step === 2" class="lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-forms.label>{{ __('influencers.face_ref') }}</x-forms.label>
                        <input type="file" name="face_ref" accept="image/*" class="file-input file-input-bordered file-input-sm mt-2 w-full" />
                    </div>
                    <div>
                        <x-forms.label>{{ __('influencers.style_ref') }}</x-forms.label>
                        <input type="file" name="style_ref" accept="image/*" class="file-input file-input-bordered file-input-sm mt-2 w-full" />
                    </div>
                </div>
                <p class="mt-3 text-xs text-base-content/50">{{ __('influencers.refs_hint') }}</p>
            </x-card>

            {{-- Step 3: Story --}}
            <x-card :title="__('influencers.story_title')" x-show="step === 3" class="lg:col-span-2">
                <div class="space-y-1.5">
                    <x-forms.label>{{ __('influencers.backstory') }}</x-forms.label>
                    <textarea name="backstory" x-model="data.backstory" rows="4" class="textarea textarea-bordered w-full" placeholder="{{ __('influencers.backstory_placeholder') }}"></textarea>
                </div>

                <div class="mt-4">
                    <x-forms.label>{{ __('influencers.personality') }}</x-forms.label>
                    <input type="range" min="0" max="100" x-model.number="data.personality" class="range range-primary mt-2 w-full" />
                    <p class="mt-1 text-sm text-base-content/60" x-text="data.personality + ' — ' + (data.personality < 35 ? '{{ __('influencers.introverted') }}' : (data.personality > 65 ? '{{ __('influencers.extroverted') }}' : '{{ __('influencers.balanced') }}'))"></p>
                </div>
            </x-card>

            {{-- Step 4: Look --}}
            <x-card :title="__('influencers.look_title')" x-show="step === 4" class="lg:col-span-2">
                <div class="flex justify-end">
                    <x-button type="button" size="sm" variant="outline" @click="randomize()">🎲 {{ __('influencers.randomize') }}</x-button>
                </div>

                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    <x-forms.select name="ethnicity" :label="__('influencers.ethnicity')" :options="collect($catalog['ethnicities'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.ethnicity" />
                    <x-forms.select name="skin_tone" :label="__('influencers.skin_tone')" :options="collect($catalog['skinTones'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.skinTone" />
                    <x-forms.select name="hair_color" :label="__('influencers.hair_color')" :options="collect($catalog['hairColors'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.hairColor" />
                    <x-forms.select name="hair_length" :label="__('influencers.hair_length')" :options="[]" x-model="data.hairLength">
                        <template x-for="h in hairLengths" :key="h">
                            <option :value="h" x-text="h"></option>
                        </template>
                    </x-forms.select>
                    <x-forms.select name="hair_texture" :label="__('influencers.hair_texture')" :options="collect($catalog['hairTextures'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.hairTexture" />
                    <x-forms.select name="eye_color" :label="__('influencers.eye_color')" :options="collect($catalog['eyeColors'])->mapWithKeys(fn($e) => [$e => $e])->all()" x-model="data.eyeColor" />
                    <x-forms.select name="build" :label="__('influencers.build')" :options="[]" x-model="data.build">
                        <template x-for="b in builds" :key="b">
                            <option :value="b" x-text="b"></option>
                        </template>
                    </x-forms.select>
                    <x-forms.input name="unique_features" :label="__('influencers.unique_features')" x-model="data.uniqueFeatures" />
                </div>

                <div class="mt-5">
                    <x-forms.label>{{ __('influencers.vibe') }}</x-forms.label>
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
            <x-card :title="__('influencers.generate_title')" x-show="step === 5" class="lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-forms.select name="provider" :label="__('influencers.provider')" :options="collect($catalog['providers'])->mapWithKeys(fn($p) => [$p['machine_name'] => $p['friendly_name']])->all()" x-model="data.provider" @change="data.model = Object.keys(models)[0] || ''" />
                    <x-forms.select name="model" :label="__('influencers.model')" :options="[]" x-model="data.model">
                        <template x-for="(label, key) in models" :key="key">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </x-forms.select>
                    <x-forms.select name="aspect_ratio" :label="__('influencers.aspect_ratio')" :options="['9:16' => '9:16', '16:9' => '16:9']" x-model="data.aspectRatio" />
                </div>

                <label class="mt-4 flex cursor-pointer items-center gap-2 text-sm text-base-content/70">
                    <input type="checkbox" name="enhance" value="1" x-model="data.enhance" class="checkbox checkbox-sm" />
                    {{ __('influencers.enhance') }}
                </label>

                <p class="mt-2 text-xs text-base-content/50" x-show="! data.provider">{{ __('influencers.connect_hint') }}</p>
            </x-card>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <x-button type="button" variant="ghost" @click="prev()" x-show="step > 1">{{ __('influencers.back') }}</x-button>
            <x-button type="button" @click="next()" x-text="step === 5 ? '{{ __('influencers.create') }}' : '{{ __('influencers.next') }}'">{{ __('influencers.next') }}</x-button>
        </div>
    </form>
</x-layouts.dashboard>