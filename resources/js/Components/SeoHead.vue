<script setup>
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    seo: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();

const siteName = computed(() => props.seo.siteName || "LUMIÈRE");
const title = computed(() => props.seo.title || siteName.value);
const description = computed(
    () =>
        props.seo.description ||
        "Belanja pakaian modern pria, wanita, dan unisex di LUMIÈRE.",
);
const image = computed(() => {
    if (props.seo.image) {
        return props.seo.image;
    }

    if (typeof window !== "undefined") {
        return `${window.location.origin}/favicon.ico`;
    }

    return "/favicon.ico";
});
const url = computed(() => {
    if (props.seo.url) {
        return props.seo.url;
    }

    if (typeof window !== "undefined") {
        return new URL(page.url, window.location.origin).toString();
    }

    return page.url;
});
const type = computed(() => props.seo.type || "website");
const robots = computed(() => props.seo.robots || "index,follow");
const structuredData = computed(() =>
    props.seo.structuredData
        ? JSON.stringify(props.seo.structuredData)
        : null,
);
</script>

<template>
    <Head>
        <title>{{ title }}</title>
        <meta head-key="description" name="description" :content="description" />
        <meta head-key="robots" name="robots" :content="robots" />
        <link head-key="canonical" rel="canonical" :href="url" />

        <meta head-key="og:site_name" property="og:site_name" :content="siteName" />
        <meta head-key="og:title" property="og:title" :content="title" />
        <meta head-key="og:description" property="og:description" :content="description" />
        <meta head-key="og:image" property="og:image" :content="image" />
        <meta head-key="og:url" property="og:url" :content="url" />
        <meta head-key="og:type" property="og:type" :content="type" />

        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="title" />
        <meta head-key="twitter:description" name="twitter:description" :content="description" />
        <meta head-key="twitter:image" name="twitter:image" :content="image" />

        <component
            :is="'script'"
            v-if="structuredData"
            head-key="structured-data"
            type="application/ld+json"
            v-text="structuredData"
        />
    </Head>
</template>
