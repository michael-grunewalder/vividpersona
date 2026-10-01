import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

Alpine.plugin(persist);

Alpine.data('aiModelSelector', (catalog, selected = null) => ({
    families: catalog ?? [],
    familyKey: '',
    versions: [],
    versionKey: '',
    variants: [],
    variantKey: '',
    modelId: '',

    init() {
        if (! this.families.length) return;

        const start = this.locate(selected);
        this.familyKey = start.family;
        this.versions = this.currentFamily.versions;
        this.versionKey = start.version;
        this.variants = this.currentVersion.variants;
        this.variantKey = start.variant;
        this.modelId = this.currentVariant.id;
    },

    onFamily(value) {
        this.familyKey = value;
        this.versions = this.currentFamily.versions;
        this.versionKey = this.versions[0].key;
        this.variants = this.currentVersion.variants;
        this.variantKey = this.variants[0].key;
        this.modelId = this.currentVariant.id;
    },

    onVersion(value) {
        this.versionKey = value;
        this.variants = this.currentVersion.variants;
        this.variantKey = this.variants[0].key;
        this.modelId = this.currentVariant.id;
    },

    onVariant(value) {
        this.variantKey = value;
        this.modelId = this.currentVariant.id;
    },

    get currentFamily() {
        return this.families.find((f) => f.key === this.familyKey) ?? this.families[0];
    },

    get currentVersion() {
        return this.currentFamily.versions.find((v) => v.key === this.versionKey) ?? this.currentFamily.versions[0];
    },

    get currentVariant() {
        return this.currentVersion.variants.find((v) => v.key === this.variantKey) ?? this.currentVersion.variants[0];
    },

    locate(selected) {
        if (! selected) return this.defaultSelection();

        for (const family of this.families) {
            for (const version of family.versions) {
                for (const variant of version.variants) {
                    if (variant.id === selected) {
                        return { family: family.key, version: version.key, variant: variant.key };
                    }
                }
            }
        }

        return this.defaultSelection();
    },

    defaultSelection() {
        const family = this.families[0];
        const version = family.versions[0];
        const variant = version.variants[0];

        return { family: family.key, version: version.key, variant: variant.key };
    },
}));

window.Alpine = Alpine;

Alpine.start();
